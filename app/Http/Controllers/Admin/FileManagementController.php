<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FileSystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileManagementController extends Controller
{
    public function index(Request $request)
    {
        $fileTypes = File::query()
            ->whereNotNull('file_type')
            ->where('file_type', '!=', '')
            ->distinct()
            ->orderBy('file_type')
            ->pluck('file_type');

        $activeType = $request->query('type');

        if (! $fileTypes->contains($activeType)) {
            $activeType = null;
        }

        $filesQuery = File::query();

        if ($activeType) {
            $filesQuery->where('file_type', $activeType);
        }

        $files = $filesQuery
            ->orderByDesc('updated_at')
            ->get();

        $typeCounts = File::query()
            ->selectRaw('file_type, COUNT(*) as total')
            ->whereNotNull('file_type')
            ->where('file_type', '!=', '')
            ->groupBy('file_type')
            ->pluck('total', 'file_type');

        return view('admin.file.index', [
            'files' => $files,
            'fileTypes' => $fileTypes,
            'typeCounts' => $typeCounts,
            'totalFiles' => File::count(),
            'activeType' => $activeType,
        ]);
    }

    public function store(Request $request)
    {
        $activeType = $this->normalizeActiveType($request);

        try {
            $validated = Validator::make($request->all(), [
                'file_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'uploaded_file' => 'required|file|max:10240',
                'active_type' => 'nullable|string',
            ])->validate();
        } catch (ValidationException $e) {
            return redirect()
                ->route('file.index', array_filter(['type' => $activeType]))
                ->withErrors($e->validator)
                ->withInput()
                ->with('open_modal', 'create');
        }

        $storedFile = $this->storeUploadedFile($request->file('uploaded_file'));

        $file = File::create([
            'file_name' => $validated['file_name'],
            'description' => $validated['description'] ?? null,
            'file_path' => $storedFile['file_path'],
            'file_type' => $storedFile['file_type'],
        ]);

        return redirect()
            ->route('file.index', array_filter(['type' => $file->file_type]))
            ->with('success', 'File created successfully.');
    }

    public function update(Request $request, File $file)
    {
        $activeType = $this->normalizeActiveType($request);

        try {
            $validated = Validator::make($request->all(), [
                'file_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'replacement_file' => 'nullable|file|max:10240',
                'active_type' => 'nullable|string',
            ])->validate();
        } catch (ValidationException $e) {
            return redirect()
                ->route('file.index', array_filter(['type' => $activeType]))
                ->withErrors($e->validator)
                ->withInput()
                ->with('open_modal', 'edit')
                ->with('modal_file_id', $file->id);
        }

        $oldFilePath = $file->file_path;
        $oldWasManaged = $file->isManagedPublicFile();

        $file->fill([
            'file_name' => $validated['file_name'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->hasFile('replacement_file')) {
            $storedFile = $this->storeUploadedFile($request->file('replacement_file'));

            $file->file_path = $storedFile['file_path'];
            $file->file_type = $storedFile['file_type'] ?: $file->file_type;
        }

        $file->save();

        if ($request->hasFile('replacement_file') && $oldWasManaged && $oldFilePath && $oldFilePath !== $file->file_path) {
            $this->deleteManagedFile($oldFilePath);
        }

        return redirect()
            ->route('file.index', array_filter(['type' => $activeType]))
            ->with('success', 'File updated successfully.');
    }

    public function destroy(Request $request, File $file)
    {
        $activeType = $this->normalizeActiveType($request);
        $filePath = $file->file_path;
        $isManagedFile = $file->isManagedPublicFile();
        $fileName = $file->file_name;

        $file->delete();

        if ($isManagedFile && $filePath) {
            $this->deleteManagedFile($filePath);
        }

        return redirect()
            ->route('file.index', array_filter(['type' => $activeType]))
            ->with('success', sprintf('"%s" was deleted successfully.', $fileName));
    }

    private function normalizeActiveType(Request $request): ?string
    {
        $activeType = $request->input('active_type');

        return filled($activeType) ? $activeType : null;
    }

    private function storeUploadedFile(UploadedFile $uploadedFile): array
    {
        $targetDirectory = public_path('uploads/files');
        FileSystem::ensureDirectoryExists($targetDirectory);

        $storedFilename = $uploadedFile->hashName();
        $uploadedFile->move($targetDirectory, $storedFilename);
        $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: $uploadedFile->extension());

        return [
            'file_path' => 'uploads/files/' . $storedFilename,
            'file_type' => $extension ?: null,
        ];
    }

    private function deleteManagedFile(string $filePath): void
    {
        if (Str::startsWith($filePath, 'files/')) {
            Storage::disk('public')->delete($filePath);

            return;
        }

        if (Str::startsWith($filePath, 'uploads/files/')) {
            FileSystem::delete(public_path($filePath));
        }
    }
}

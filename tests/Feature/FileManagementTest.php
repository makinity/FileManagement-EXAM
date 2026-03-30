<?php

namespace Tests\Feature;

use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_files_and_all_tab(): void
    {
        $pdfFile = $this->createFile([
            'file_name' => 'Policies.pdf',
            'file_type' => 'pdf',
        ]);

        $docFile = $this->createFile([
            'file_name' => 'Guide.docx',
            'file_type' => 'docx',
        ]);

        $response = $this->get(route('file.index'));

        $response->assertOk();
        $response->assertSee('All', false);
        $response->assertSee($pdfFile->file_name, false);
        $response->assertSee($docFile->file_name, false);
        $response->assertViewHas('activeType', null);
    }

    public function test_index_filters_by_file_type(): void
    {
        $pdfFile = $this->createFile([
            'file_name' => 'Policies.pdf',
            'file_type' => 'pdf',
        ]);

        $this->createFile([
            'file_name' => 'Guide.docx',
            'file_type' => 'docx',
        ]);

        $response = $this->get(route('file.index', ['type' => 'pdf']));

        $response->assertOk();
        $response->assertSee($pdfFile->file_name, false);
        $response->assertDontSee('Guide.docx', false);
        $response->assertViewHas('activeType', 'pdf');
    }

    public function test_update_without_replacement_changes_metadata_only(): void
    {
        $file = $this->createFile([
            'file_name' => 'Policies.pdf',
            'description' => 'Original description',
            'file_path' => 'uploads/files/policies.pdf',
            'file_type' => 'pdf',
        ]);

        $response = $this->put(route('file.update', $file), [
            'file_name' => 'Updated Policies.pdf',
            'description' => 'Updated description',
            'active_type' => 'pdf',
        ]);

        $response->assertRedirect(route('file.index', ['type' => 'pdf']));

        $this->assertDatabaseHas('files', [
            'id' => $file->id,
            'file_name' => 'Updated Policies.pdf',
            'description' => 'Updated description',
            'file_path' => 'uploads/files/policies.pdf',
            'file_type' => 'pdf',
        ]);
    }

    public function test_update_with_replacement_stores_new_file_and_removes_old_managed_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('files/original.pdf', 'old-file');

        $file = $this->createFile([
            'file_name' => 'Policies.pdf',
            'description' => 'Original description',
            'file_path' => 'files/original.pdf',
            'file_type' => 'pdf',
        ]);

        $upload = UploadedFile::fake()->create(
            'updated-manual.docx',
            120,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );

        $response = $this->put(route('file.update', $file), [
            'file_name' => 'Updated Manual',
            'description' => 'Replaced file',
            'replacement_file' => $upload,
            'active_type' => 'pdf',
        ]);

        $response->assertRedirect(route('file.index', ['type' => 'pdf']));

        $file->refresh();

        $this->assertSame('Updated Manual', $file->file_name);
        $this->assertSame('Replaced file', $file->description);
        $this->assertSame('docx', $file->file_type);
        $this->assertNotSame('files/original.pdf', $file->file_path);
        Storage::disk('public')->assertMissing('files/original.pdf');
        Storage::disk('public')->assertExists($file->file_path);
    }

    public function test_destroy_removes_db_row_and_managed_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('files/removable.pdf', 'delete-me');

        $file = $this->createFile([
            'file_name' => 'Removable.pdf',
            'file_path' => 'files/removable.pdf',
            'file_type' => 'pdf',
        ]);

        $response = $this->delete(route('file.destroy', $file), [
            'active_type' => 'pdf',
        ]);

        $response->assertRedirect(route('file.index', ['type' => 'pdf']));

        $this->assertDatabaseMissing('files', [
            'id' => $file->id,
        ]);

        Storage::disk('public')->assertMissing('files/removable.pdf');
    }

    public function test_destroy_succeeds_when_legacy_file_is_missing(): void
    {
        $file = $this->createFile([
            'file_name' => 'Missing Legacy.pdf',
            'file_path' => 'uploads/files/missing-legacy.pdf',
            'file_type' => 'pdf',
        ]);

        $response = $this->delete(route('file.destroy', $file), [
            'active_type' => 'pdf',
        ]);

        $response->assertRedirect(route('file.index', ['type' => 'pdf']));
        $this->assertDatabaseMissing('files', ['id' => $file->id]);
    }

    public function test_invalid_edit_redirects_back_with_errors_and_modal_session_state(): void
    {
        $file = $this->createFile([
            'file_name' => 'Policies.pdf',
            'file_type' => 'pdf',
        ]);

        $response = $this->from(route('file.index', ['type' => 'pdf']))
            ->put(route('file.update', $file), [
                'file_name' => '',
                'description' => 'Still invalid',
                'active_type' => 'pdf',
            ]);

        $response->assertRedirect(route('file.index', ['type' => 'pdf']));
        $response->assertSessionHasErrors('file_name');
        $response->assertSessionHas('open_modal', 'edit');
        $response->assertSessionHas('modal_file_id', $file->id);
    }

    private function createFile(array $attributes = []): File
    {
        return File::create(array_merge([
            'file_name' => 'Sample File.pdf',
            'description' => 'Sample description',
            'file_path' => 'uploads/files/sample-file.pdf',
            'file_type' => 'pdf',
        ], $attributes));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Model
{
    use HasFactory;

    private const MANAGED_STORAGE_PREFIX = 'files/';
    private const MANAGED_PUBLIC_UPLOAD_PREFIX = 'uploads/files/';

    private const IMAGE_TYPES = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'bmp',
        'webp',
        'svg',
    ];

    protected $fillable = [
        'file_name',
        'description',
        'file_path',
        'file_type',
    ];

    public function isManagedPublicFile(): bool
    {
        return filled($this->file_path)
            && Str::startsWith($this->file_path, [
                self::MANAGED_STORAGE_PREFIX,
                self::MANAGED_PUBLIC_UPLOAD_PREFIX,
            ]);
    }

    public function isManagedStorageFile(): bool
    {
        return filled($this->file_path) && Str::startsWith($this->file_path, self::MANAGED_STORAGE_PREFIX);
    }

    public function isManagedUploadFile(): bool
    {
        return filled($this->file_path) && Str::startsWith($this->file_path, self::MANAGED_PUBLIC_UPLOAD_PREFIX);
    }

    public function hasOpenableFile(): bool
    {
        if (blank($this->file_path)) {
            return false;
        }

        if ($this->isManagedStorageFile()) {
            return Storage::disk('public')->exists($this->file_path);
        }

        return file_exists(public_path($this->file_path));
    }

    public function resolveOpenUrl(): ?string
    {
        if (! $this->hasOpenableFile()) {
            return null;
        }

        return $this->isManagedStorageFile()
            ? Storage::disk('public')->url($this->file_path)
            : asset($this->file_path);
    }

    public function isImageType(): bool
    {
        return in_array(Str::lower((string) $this->file_type), self::IMAGE_TYPES, true);
    }
}

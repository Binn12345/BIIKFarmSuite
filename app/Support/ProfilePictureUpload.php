<?php

namespace App\Support;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProfilePictureUpload
{
    private const MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * @return array{bytes: string, mime: string}
     */
    public static function inspect(TemporaryUploadedFile $upload): array
    {
        Validator::make(['picture' => $upload], [
            'picture' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ])->validate();

        $bytes = file_get_contents($upload->getRealPath());
        if ($bytes === false) {
            throw ValidationException::withMessages(['picture' => 'The uploaded image could not be read.']);
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        $image = @getimagesizefromstring($bytes);
        if (! in_array($mime, self::MIME_TYPES, true) || ! $image || $image['mime'] !== $mime) {
            throw ValidationException::withMessages(['picture' => 'Upload a valid JPEG, PNG, or WebP image.']);
        }

        return ['bytes' => $bytes, 'mime' => $mime];
    }
}

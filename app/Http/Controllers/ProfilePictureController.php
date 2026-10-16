<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProfilePictureController
{
    public function __invoke(User $user): Response
    {
        abort_unless(auth()->id() === $user->id || Gate::allows('users.view'), 403);

        $image = User::withoutGlobalScope('exclude_profile_picture')
            ->whereKey($user->id)
            ->first(['id', 'profile_picture', 'profile_picture_mime_type']);

        abort_if(! $image?->profile_picture || ! in_array($image->profile_picture_mime_type, [
            'image/jpeg', 'image/png', 'image/webp',
        ], true), 404);

        return response($image->profile_picture, 200, [
            'Content-Type' => $image->profile_picture_mime_type,
            'Content-Length' => (string) strlen($image->profile_picture),
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

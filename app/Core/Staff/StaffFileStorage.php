<?php

namespace App\Core\Staff;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class StaffFileStorage
{
    /**
     * @param  array<string, UploadedFile|null>  $files
     */
    public static function storeForUser(User $user, array $files): void
    {
        $directory = "staff/{$user->id}";
        $updates = [];

        if (! empty($files['profile_photo'])) {
            self::assertWebp($files['profile_photo'], 'profilePhoto', 'Profile photo');
            self::deleteIfExists($user->profile_photo_path);
            $updates['profile_photo_path'] = $files['profile_photo']->store($directory, 'public');
        }

        if (! empty($files['cnic_front'])) {
            self::assertWebp($files['cnic_front'], 'cnicFront', 'CNIC front');
            self::deleteIfExists($user->cnic_front_path);
            $updates['cnic_front_path'] = $files['cnic_front']->store($directory, 'public');
        }

        if (! empty($files['cnic_back'])) {
            self::assertWebp($files['cnic_back'], 'cnicBack', 'CNIC back');
            self::deleteIfExists($user->cnic_back_path);
            $updates['cnic_back_path'] = $files['cnic_back']->store($directory, 'public');
        }

        if ($updates !== []) {
            $user->update($updates);
        }
    }

    protected static function assertWebp(UploadedFile $file, string $field, string $label): void
    {
        if (strtolower($file->getClientOriginalExtension()) !== 'webp') {
            throw ValidationException::withMessages([
                $field => "{$label} must be a WebP image. JPG, PNG, GIF, BMP and other formats are not allowed.",
            ]);
        }
    }

    public static function deleteIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}

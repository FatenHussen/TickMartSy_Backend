<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait FileTrait
{
    // public function uploadFile($disk, $directory, $file)
    // {
    //     $allowedfileExtension = ['jpg', 'png', 'JPEG', 'PNG', 'jpeg','pdf'];
    //     $extension = $file->getClientOriginalExtension();
    //     $check = in_array($extension, $allowedfileExtension);
    //     if (!$check) {
    //         return "";
    //     }
    //     $path = Storage::disk($disk)->put($directory, $file);
    //     return $path;
    // }
    public function uploadFile($disk, $directory, $file)
    {
        $allowedfileExtension = ['jpg', 'png', 'jpeg', 'pdf'];

        if (is_object($file) && method_exists($file, 'getClientOriginalExtension')) {
            $extension = $file->getClientOriginalExtension();
            if (!in_array($extension, $allowedfileExtension)) {
                return "";
            }
            $path = Storage::disk($disk)->put($directory, $file);
            return $path;
        }

        if (is_string($file)) {
            return $file;
        }

        return "";
    }

    public function deleteFile($disk, $directory, $path)
    {
        try {
            if (str_contains($path, "default")) {
                return true;
            }
            $exploded = explode('/', $path);
            $filename = end($exploded);
            Storage::disk($disk)->delete("$directory/$filename");
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}

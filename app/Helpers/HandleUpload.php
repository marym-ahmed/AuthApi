<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class HandleUpload
{
    public static function uploadFile(UploadedFile $file, $directory, $disk = 'public')
    {
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        $storedFilePath = $file->storeAs($directory, $fileName, $disk);

        return $storedFilePath;
    }
}

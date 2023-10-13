<?php

namespace App\Services;


use App\Jobs\CsvUploadJob;
use App\Models\UserUpload;
use Illuminate\Support\Facades\Bus;

class CsvUploadService
{
    public static function upload($request): bool
    {
        try {
            $file = $request->file('upload');
            $fileName = $file->getClientOriginalName();
            $fileContents = file($file->getPathname());

//            $utf8Contents = mb_convert_encoding($fileContents, 'UTF-8', 'UTF-8');
//
//            $lines = explode("\n", $utf8Contents);
//
//            dd($utf8Contents);

            //TODO: removal of non-utf8 (chinese characters etc)

            $userUpload = self::userUpload($fileName);

            if (count($fileContents) > 1) {
                CsvUploadJob::dispatch($fileContents, $fileName, $userUpload);
            } else {
                return false;
            }

        } catch (\Exception $ex) {
            //TODO: Log Entries to log exceptions
            return false;
        }
        return true;
    }

    public static function userUpload($filename)
    {
        try {
            return UserUpload::create([
                'user_id' => auth()->user()->id,
                'filename' => $filename,
                'status' => UserUpload::UPLOAD_STATUS_PENDING,
            ]);
        }catch (\Exception $ex) {
            dd($ex);
        }

    }
}

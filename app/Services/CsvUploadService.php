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

            $csv = file($file);
            $chunks = array_chunk($csv,1000);
            $header = [];
            $batch  = Bus::batch([])->dispatch();

            $userUpload = self::userUpload($fileName);

            foreach ($chunks as $key => $chunk) {
                $data = array_map('str_getcsv', $chunk);

                foreach ($data as &$field) {
                    $field = mb_convert_encoding($field, 'UTF-8', 'UTF-8');
                    $field = preg_replace('/[^\x{0000}-\x{007F}]+/u', '', $field);
                }

                if($key == 0){
                    $header = $data[0];
                    unset($data[0]);
                }
                $batch->add(new CsvUploadJob($data, $header, $userUpload));
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

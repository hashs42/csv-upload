<?php

namespace App\Jobs;

use App\Events\CsvUploadEvent;
use App\Http\Controllers\CsvUploadController;
use App\Models\Product;
use App\Models\UserUpload;
use App\Services\CsvUploadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CsvUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private $fileContents, private $fileName, private UserUpload $userUpload)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $processedRowCount = 0;

        $this->userUpload->update([
            'status' => UserUpload::UPLOAD_STATUS_IN_PROCESS
        ]);

        event(new CsvUploadEvent($this->userUpload->user_id, UserUpload::UPLOAD_STATUS_IN_PROCESS));

        try {
            $totalRowCount = count($this->fileContents) - 1;

            foreach ($this->fileContents as $idx => $line) {

                if ($idx == 0) continue;

                $data = str_getcsv($line);

                foreach ($data as &$field) {
                    $field = mb_convert_encoding($field, 'UTF-8', 'UTF-8');
                    $field = preg_replace('/[^\x{0000}-\x{007F}]+/u', '', $field);
                }

                $product = Product::updateOrCreate([
                    'code' => $data[0],
                ], [
                    'code' => $data[0],
                    'title' => $data[1],
                    'description' => $data[2],
                    'style' => $data[3],
                    'mainframe_color' => $data[28],
                    'size' => $data[18],
                    'color' => $data[14],
                    'price' => $data[21],
                ]);

                if ($product) {
                    $processedRowCount++;
                }
            }


            if ($processedRowCount === $totalRowCount) {
                $this->userUpload->update([
                    'status' => UserUpload::UPLOAD_STATUS_COMPLETED
                ]);
                event(new CsvUploadEvent($this->userUpload->user_id, UserUpload::UPLOAD_STATUS_COMPLETED));
            } else {
                $this->userUpload->update([
                    'status' => UserUpload::UPLOAD_STATUS_FAILED
                ]);
                event(new CsvUploadEvent($this->userUpload->user_id, UserUpload::UPLOAD_STATUS_FAILED));
            }

        } catch (\Exception $ex) {
            //TODO: Log Entries to log exceptions
            $this->userUpload->update([
                'status' => UserUpload::UPLOAD_STATUS_FAILED
            ]);
            event(new CsvUploadEvent($this->userUpload->user_id, UserUpload::UPLOAD_STATUS_FAILED));
        }
    }
}

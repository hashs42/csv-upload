<?php

namespace App\Jobs;

use App\Events\CsvUploadEvent;
use App\Http\Controllers\CsvUploadController;
use App\Models\Product;
use App\Models\UserUpload;
use App\Services\CsvUploadService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CsvUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(public $data, public $header, public UserUpload $userUpload)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->userUpload->update([
            'status' => UserUpload::UPLOAD_STATUS_IN_PROCESS
        ]);

        event(new CsvUploadEvent($this->userUpload->user_id, UserUpload::UPLOAD_STATUS_IN_PROCESS));

        try {

            foreach ($this->data as $product) {
                $products = array_combine($this->header, $product);
                Product::updateOrCreate([
                    'code' => $products['UNIQUE_KEY'],
                ], [
                    'code' => $products['UNIQUE_KEY'],
                    'title' => $products['PRODUCT_TITLE'],
                    'description' => $products['PRODUCT_DESCRIPTION'],
                    'style' => $products['STYLE#'],
                    'mainframe_color' => $products['SANMAR_MAINFRAME_COLOR'],
                    'size' => $products['SIZE'],
                    'color' => $products['COLOR_NAME'],
                    'price' => $products['PIECE_PRICE'],
                ]);
            }

            $this->userUpload->update([
                'status' => UserUpload::UPLOAD_STATUS_COMPLETED
            ]);
            event(new CsvUploadEvent($this->userUpload->user_id, UserUpload::UPLOAD_STATUS_COMPLETED));

        } catch (\Exception $ex) {
            dd($ex);
            //TODO: Log Entries to log exceptions
            $this->userUpload->update([
                'status' => UserUpload::UPLOAD_STATUS_FAILED
            ]);
            event(new CsvUploadEvent($this->userUpload->user_id, UserUpload::UPLOAD_STATUS_FAILED));
        }
    }
}

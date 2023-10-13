<?php

namespace App\Http\Controllers;

use App\Http\Requests\CsvUploadRequest;
use App\Services\CsvUploadService;

class CsvUploadController extends Controller
{
    public function upload(CsvUploadRequest $request) {

        if ($request->hasFile('upload')) {

            $upload = CsvUploadService::upload($request);

            if (!$upload) return redirect()->route('dashboard')->withErrors('Please make sure that there is data apart from the header row');

            return redirect()->route('dashboard');
        }

        return redirect()->route('dashboard');
    }
}

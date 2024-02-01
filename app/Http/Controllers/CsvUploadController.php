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

//    public function test() {
//        for($x = 0; $x <= 100; $x++) {
//            if ($x % 3 == 0 && $x % 5 == 0) {
//                echo "HiTower" . "<br>";
//            } elseif ($x % 3 == 0) {
//                echo "Hi" . "<br>";
//            } elseif ($x % 5 == 0) {
//                echo "Tower" . "<br>";
//            } else {
//                echo $x . "<br>";
//            }
//        }
//    }
}

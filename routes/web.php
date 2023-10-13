<?php

use App\Models\UserUpload;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CsvUploadController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $uploads = UserUpload::where('user_id', auth()->user()->id)->get();
        $user = \App\Models\User::find(auth()->user()->id);
        return Inertia::render('Dashboard', compact('uploads', 'user'));
    })->name('dashboard');

    Route::post('csv-upload', [CsvUploadController::class, 'upload'])->name('csv.upload');
});

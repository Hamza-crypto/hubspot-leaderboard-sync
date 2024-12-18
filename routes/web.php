<?php

use App\Http\Controllers\GuruController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookController;
use App\Models\WebhookPayload;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('pages.dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::controller(WebhookController::class)->group(function () {
    Route::get('webhook', 'webhook');
    Route::post('webhook', 'webhook');
});

// Route::get('migrate_fresh', function () {
//     $res = Artisan::call('migrate:fresh');
//     dump('Database Reset Successfully');
// });


Route::get('migrate', function () {

    Artisan::call('migrate');
    dump('Migration Done');
});


Route::get('optimize', function () {
    Artisan::call('optimize:clear');
    dump('Optimization Done');
});



Route::controller(LeaderboardController::class)->group(function () {
    Route::get('leaderboard', 'index')->middleware('checkAppKey')->name('leaderboard');
    Route::get('leaderboard/spanish', 'leader_spanish')->middleware('checkAppKey');
});

require __DIR__.'/auth.php';


/**
 * Guru Controllers
 */

Route::get('get-token', [GuruController::class, 'getAccessToken']);
Route::get('refresh-token', [GuruController::class, 'getNewAccessTokenFromRefreshToken']);
Route::get('store-jobs', [GuruController::class, 'store_jobs']);



Route::get('import-contacts', function () {
    Artisan::call('import:hubspot-contacts');
});

Route::get('refresh-leaderboard', function () {
    Artisan::call('refresh-leaderboard');
});

Route::get('webhook/process', function () {
    Artisan::call('process_webhook');
});

Route::get('backup', function () {
    Artisan::call('backup:full');
});

Route::get('payloads', function () {
    dd("Total payloads: " . WebhookPayload::count());
});

Route::get('phpinfo', function () {
    echo phpinfo();
});



Route::get('check_path', function () {
    dump(env('APP_NAME'));
});

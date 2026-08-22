<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\SongController;
use App\Controllers\ArtistController;
use App\Controllers\AlbumController;
use App\Controllers\PlaylistController;
use App\Controllers\FavoriteController;
use App\Controllers\HistoryController;
use App\Controllers\SearchController;
use App\Controllers\ProfileController;
use App\Controllers\SettingsController;
use App\Controllers\HealthController;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

$router->post('auth/register', [AuthController::class, 'register']);
$router->post('auth/login', [AuthController::class, 'login']);
$router->post('auth/logout', [AuthController::class, 'logout']);

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

$router->get('home', [HomeController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Songs
|--------------------------------------------------------------------------
*/

$router->get('songs', [SongController::class, 'index']);

$router->get('songs/trending', [SongController::class, 'trending']);
$router->get('songs/popular', [SongController::class, 'popular']);
$router->get('songs/latest', [SongController::class, 'latest']);
$router->get('songs/new', [SongController::class, 'latest']);
$router->get('songs/recommended', [SongController::class, 'recommended']);

$router->get('songs/{id}/play', [SongController::class, 'play']);
$router->post('songs/{id}/play', [SongController::class, 'play']);

$router->post('/songs/{id}/progress', [SongController::class, 'progress']);

$router->get('songs/{id}', [SongController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Artists
|--------------------------------------------------------------------------
*/

$router->get('artists', [ArtistController::class, 'index']);
$router->get('artists/{id}', [ArtistController::class, 'show']);
$router->get('artists/{id}/tracks', [ArtistController::class, 'tracks']);
$router->get('artists/{id}/albums', [ArtistController::class, 'albums']);

/*
|--------------------------------------------------------------------------
| Albums
|--------------------------------------------------------------------------
*/

$router->get('albums', [AlbumController::class, 'index']);
$router->get('albums/search', [AlbumController::class, 'search']);
$router->get('albums/{id}', [AlbumController::class, 'show']);
$router->get('albums/{id}/tracks', [AlbumController::class, 'tracks']);

/*
|--------------------------------------------------------------------------
| Genres
|--------------------------------------------------------------------------
*/

$router->get('tracks', [SongController::class, 'filter']);

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

$router->get('search', [SearchController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Favorites
|--------------------------------------------------------------------------
*/

$router->get('favorites', [FavoriteController::class, 'index']);
$router->post('favorites', [FavoriteController::class, 'store']);
$router->delete('favorites/{id}', [FavoriteController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| History
|--------------------------------------------------------------------------
*/

$router->get('history', [HistoryController::class, 'index']);
$router->post('history', [HistoryController::class, 'store']);
$router->delete('history/{id}', [HistoryController::class, 'destroy']);
$router->delete('history', [HistoryController::class, 'clear']);

/*
|--------------------------------------------------------------------------
| Playlists
|--------------------------------------------------------------------------
*/

$router->get('playlists', [PlaylistController::class, 'index']);
$router->get('playlists/{id}/tracks', [PlaylistController::class, 'tracks']);
$router->post('playlists', [PlaylistController::class, 'store']);
$router->put('playlists/{id}', [PlaylistController::class, 'update']);
$router->delete('playlists/{id}', [PlaylistController::class, 'destroy']);

$router->post('playlists/{id}/songs', [PlaylistController::class, 'addSong']);
$router->delete('playlists/{id}/songs/{songId}', [PlaylistController::class, 'removeSong']);

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

$router->get('profile', [ProfileController::class, 'show']);
$router->put('profile', [ProfileController::class, 'update']);

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/

$router->get('settings', [SettingsController::class, 'show']);
$router->put('settings', [SettingsController::class, 'update']);

$router->get('', function () {
    echo json_encode([
        'success' => true,
        'api' => API_NAME,
        'version' => API_VERSION,
        'status' => 'online'
    ]);
});

/*
|--------------------------------------------------------------------------
| db-test
|--------------------------------------------------------------------------
*/

$router->get(
    'db-test',
    [HealthController::class, 'dbTest']
);
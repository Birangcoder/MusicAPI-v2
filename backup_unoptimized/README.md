# MusicAPI V2 — Current API Documentation

PHP + MySQL REST API for the Flutter Music App.

> **This document describes the current implementation of MusicAPI V2**, based on the current router/model structure.  
> It intentionally does **not** list planned endpoints such as refresh tokens, lyrics, queue, artist follow, downloads, or streaming unless they are currently implemented.

---

## 1. Base URL

### Local development

```text
http://localhost/MusicAPI-v2/public
```

Examples:

```text
GET http://localhost/MusicAPI-v2/public/
GET http://localhost/MusicAPI-v2/public/songs
GET http://localhost/MusicAPI-v2/public/artists
```

### Smartphone testing

`localhost` means the device itself, so a phone cannot normally use:

```text
http://localhost/MusicAPI-v2/public
```

Use your computer's local IPv4 address instead, for example:

```text
http://192.168.1.10/MusicAPI-v2/public
```

Both the computer and phone must be connected to the same network.

---

# 2. Response Format

The API normally returns:

```json
{
    "success": true,
    "data": {}
}
```

For paginated endpoints:

```json
{
    "success": true,
    "data": [],
    "pagination": {
        "page": 1,
        "limit": 20,
        "total": 28,
        "total_pages": 2,
        "has_next": true,
        "has_previous": false
    }
}
```

Errors can look like:

```json
{
    "success": false,
    "message": "Route not found"
}
```

Validation errors may look like:

```json
{
    "success": false,
    "message": "Validation Failed",
    "errors": {
        "password_confirmation": [
            "The field is required."
        ]
    }
}
```

---

# 3. Authentication

JWT authentication is used for protected endpoints.

## JWT expiration

Current configuration:

```php
define('JWT_EXPIRE', 60 * 60 * 24 * 7);
```

The token expires after:

```text
7 days
```

After expiration, the user must log in again.

> Refresh-token functionality is not currently part of the active router.

---

# 4. Register

### Endpoint

```http
POST /auth/register
```

Full local URL:

```text
http://localhost/MusicAPI-v2/public/auth/register
```

### Body

Use:

```json
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
}
```

### Important

`password_confirmation` is required.

The following will fail:

```json
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123"
}
```

because the confirmation field is missing.

---

# 5. Login

### Endpoint

```http
POST /auth/login
```

### Body

```json
{
    "email": "test@example.com",
    "password": "Password123"
}
```

Save the returned JWT token.

For protected endpoints, send:

```http
Authorization: Bearer YOUR_TOKEN
```

In Thunder Client:

```text
Auth → Bearer Token → YOUR_TOKEN
```

---

# 6. Logout

### Endpoint

```http
POST /auth/logout
```

### Authentication

Required.

```http
Authorization: Bearer YOUR_TOKEN
```

---

# 7. Home

### Endpoint

```http
GET /home
```

Authentication is optional.

The home response can contain:

```text
trending
popular
new_release
recommended
top_artists
top_albums
continue_listening
```

For a guest user, `continue_listening` is empty.

Example:

```json
{
    "success": true,
    "data": {
        "trending": [],
        "popular": [],
        "new_release": [],
        "recommended": [],
        "top_artists": [],
        "top_albums": [],
        "continue_listening": []
    }
}
```

---

# 8. Songs

## 8.1 Get songs

```http
GET /songs
```

Example:

```text
GET /songs?page=1
```

Pagination:

```text
/songs?page=1
/songs?page=2
/songs?page=3
```

The API handles SQL `LIMIT` internally.

You do **not** write SQL `LIMIT` in the URL.

---

## 8.2 Trending songs

```http
GET /songs/trending
```

Example:

```text
GET /songs/trending
```

Trending is calculated from song statistics such as play activity.

---

## 8.3 Popular songs

```http
GET /songs/popular
```

---

## 8.4 New releases

```http
GET /songs/new
```

Returns songs ordered as latest releases.

---

## 8.5 Recommended songs

```http
GET /songs/recommended
```

This endpoint uses the current user when authentication information is available.

---

# 9. Single Song Details

### Endpoint

```http
GET /songs/{id}
```

Example:

```text
GET /songs/28
```

A detailed song response is designed to contain:

```json
{
    "success": true,
    "data": {
        "id": 28,
        "title": "Millionaire",
        "slug": "millionaire",

        "media": {
            "audio_url": "https://res.cloudinary.com/...",
            "cover_url": "https://res.cloudinary.com/...",
            "duration_seconds": 195,
            "duration": "03:15"
        },

        "metadata": {
            "language": "Hindi",
            "release_date": "2024-08-26",
            "is_active": true
        },

        "statistics": {
            "play_count": 0,
            "like_count": 0,
            "download_count": 0
        },

        "artists": [
            {
                "id": 1,
                "name": "Arijit Singh",
                "slug": "arijit-singh",
                "image_url": "https://res.cloudinary.com/...",
                "verified": true
            }
        ],

        "album": {
            "id": 5,
            "title": "Millionaire",
            "slug": "millionaire",
            "cover_url": "https://res.cloudinary.com/...",
            "release_date": "2024-08-26",
            "album_type": "Single",
            "label": "..."
        },

        "genres": [
            {
                "id": 16,
                "name": "Hindi",
                "slug": "hindi"
            }
        ],

        "links": {
            "self": "/songs/28",
            "artist": "/artists/1",
            "album": "/albums/5"
        }
    }
}
```

The relationships come from:

```text
songs
   |
   +---- song_artists ---- artists
   |
   +---- song_albums ----- albums
   |
   +---- song_genres ----- genres
```

---

# 10. Artists

## 10.1 Artist list

```http
GET /artists
```

Pagination:

```text
/artists?page=1
/artists?page=2
```

Example artist:

```json
{
    "id": 1,
    "name": "Arijit Singh",
    "slug": "arijit-singh",
    "image_url": "https://res.cloudinary.com/xrwvu9pm/image/upload/...",
    "verified": true
}
```

---

## 10.2 Artist tracks

```http
GET /artists/{id}/tracks
```

Example:

```text
GET /artists/1/tracks
```

Pagination:

```text
GET /artists/1/tracks?page=1
GET /artists/1/tracks?page=2
```

The response should use the same detailed song structure as `/songs/{id}` rather than returning only raw song columns.

---

## 10.3 Artist albums

```http
GET /artists/{id}/albums
```

Example:

```text
GET /artists/1/albums
```

Pagination:

```text
GET /artists/1/albums?page=1
```

---

## 10.4 Artist details

If the route is implemented:

```http
GET /artists/{id}
```

Example:

```text
GET /artists/1
```

---

# 11. Albums

## 11.1 Album list

```http
GET /albums
```

Pagination:

```text
GET /albums?page=1
GET /albums?page=2
```

---

## 11.2 Album details / tracks

```http
GET /albums/{id}/tracks
```

Example:

```text
GET /albums/5/tracks
```

The response contains album information and its tracks.

---

## 11.3 Album details

If the normal album detail endpoint is enabled:

```http
GET /albums/{id}
```

Example:

```text
GET /albums/5
```

---

# 12. Genres / Tags

### Endpoint

```http
GET /tracks?tags={genre}
```

Examples:

```text
GET /tracks?tags=Hindi
```

```text
GET /tracks?tags=Gujarati
```

```text
GET /tracks?tags=rock
```

Multiple tags can be supplied as comma-separated values if supported:

```text
GET /tracks?tags=hindi,pop
```

Pagination:

```text
GET /tracks?tags=Hindi&page=1
GET /tracks?tags=Hindi&page=2
```

The result should contain the same detailed song information used elsewhere.

---

# 13. Search

Search supports songs and artists.

### Endpoint

```http
GET /search?q={keyword}
```

Examples:

```text
GET /search?q=agar
```

```text
GET /search?q=mereya
```

```text
GET /search?q=arijit
```

The search should be able to find:

```text
Songs
Artists
```

---

## Search by type

If the `type` parameter is enabled:

```text
GET /search?q=agar&type=song
```

```text
GET /search?q=arijit&type=artist
```

This allows Flutter to display separate search sections.

Example conceptual response:

```json
{
    "success": true,
    "data": {
        "songs": [],
        "artists": []
    }
}
```

---

# 14. Playlists

## 14.1 Get user's playlists

```http
GET /playlists
```

Authentication required.

---

## 14.2 Get playlist with songs

```http
GET /playlists/{id}/tracks
```

Example:

```text
GET /playlists/1/tracks
```

Authentication is required if the current implementation protects the playlist.

The database relationship is:

```text
playlists
    |
    +---- playlist_songs ---- songs
```

`playlist_songs` contains the relationship between a playlist and a song.

Typical columns:

```text
playlist_id
song_id
position
added_at
```

### Important database note

The playlist table uses:

```text
title
```

not:

```text
name
```

Therefore SQL such as:

```sql
SELECT name FROM playlists
```

is incorrect if your table has `title`.

Use:

```sql
SELECT title FROM playlists
```

---

## 14.3 Create playlist

```http
POST /playlists
```

Example body:

```json
{
    "title": "My Favorites",
    "description": "My personal playlist"
}
```

---

## 14.4 Update playlist

```http
PUT /playlists/{id}
```

Example:

```text
PUT /playlists/1
```

---

## 14.5 Delete playlist

```http
DELETE /playlists/{id}
```

---

## 14.6 Add song to playlist

```http
POST /playlists/{id}/songs
```

Example:

```text
POST /playlists/1/songs
```

Body:

```json
{
    "song_id": 28
}
```

---

## 14.7 Remove song from playlist

```http
DELETE /playlists/{id}/songs/{songId}
```

Example:

```text
DELETE /playlists/1/songs/28
```

---

# 15. Favorites

Authentication is required.

## Get favorites

```http
GET /favorites
```

The endpoint should return the favorite songs with their song information.

Example:

```json
{
    "success": true,
    "data": [
        {
            "id": 7,
            "song_id": 26,
            "created_at": "2026-08-08 11:58:27",

            "song": {
                "id": 26,
                "title": "Gir Gajavti Aavi Sinh",
                "slug": "gir-gajavti-aavi-sinh",
                "media": {
                    "cover_url": "https://res.cloudinary.com/...",
                    "audio_url": "https://res.cloudinary.com/...",
                    "duration_seconds": 186
                }
            }
        }
    ]
}
```

---

## Add favorite

```http
POST /favorites
```

Body:

```json
{
    "song_id": 26
}
```

---

## Remove favorite

```http
DELETE /favorites/{songId}
```

Example:

```text
DELETE /favorites/26
```

---

# 16. History

Authentication is required.

## Get history

```http
GET /history
```

Example:

```json
{
    "success": true,
    "data": [
        {
            "id": 7,
            "song_id": 11,
            "played_at": "2026-08-05 11:58:44",

            "song": {
                "id": 11,
                "title": "Vishvambhari Stuti",
                "slug": "vishvambhari-stuti",
                "media": {
                    "cover_url": "https://res.cloudinary.com/...",
                    "audio_url": "https://res.cloudinary.com/...",
                    "duration_seconds": 377
                }
            }
        }
    ]
}
```

---

## Add history

```http
POST /history
```

Example:

```json
{
    "song_id": 11
}
```

The server associates the record with the authenticated user.

---

# 17. Profile

Authentication required.

## Get profile

```http
GET /profile
```

---

## Update profile

```http
PUT /profile
```

The current profile update fields are:

```text
name
avatar_url
country
birth_date
gender
bio
```

Example:

```json
{
    "name": "Test User",
    "avatar_url": "https://res.cloudinary.com/...",
    "country": "India",
    "birth_date": "2003-11-28",
    "gender": "other",
    "bio": "Music lover"
}
```

The server updates the profile for the authenticated user's ID.

---

# 18. Settings

Authentication required.

## Get settings

```http
GET /settings
```

## Update settings

```http
PUT /settings
```

---

# 19. Pagination

The API uses page-based pagination.

Do this:

```text
GET /songs?page=1
GET /songs?page=2
GET /artists?page=1
GET /albums?page=1
```

Do not expose SQL syntax such as:

```text
/songs?limit=20
```

as the normal client interface if the model/controller already controls the default limit.

Internally the PHP code calculates:

```php
$offset = ($page - 1) * $limit;
```

Example with 28 songs and a limit of 20:

```text
Page 1 → songs 1–20
Page 2 → songs 21–28
```

---

# 20. Cloudinary Media

Songs and images can be stored outside the PHP server.

Example artist:

```text
name:
Arijit Singh

image_url:
https://res.cloudinary.com/xrwvu9pm/image/upload/v1786071384/arijit_singh_yutjxh.jpg
```

Example song:

```text
audio_url:
https://res.cloudinary.com/xrwvu9pm/video/upload/v1786069169/millonre_jbfmrv.mp3

cover_url:
https://res.cloudinary.com/xrwvu9pm/image/upload/v1786123560/millionaire_v7ufuf.jpg
```

The database stores the URLs.

The PHP server does not need to store the audio/image files locally for normal playback.

---

# 21. Database Relationships

Important relationships:

```text
songs
 |
 +--- song_artists --- artists
 |
 +--- song_albums ---- albums
 |
 +--- song_genres ---- genres
 |
 +--- favorites
 |
 +--- history
 |
 +--- playlist_songs --- playlists
```

## song_artists

Connects songs and artists.

Example:

```text
song_id = 1
artist_id = 1
```

means artist 1 is associated with song 1.

---

# 22. Thunder Client Testing

Recommended order:

## Step 1 — API health

```http
GET /
```

Expected:

```json
{
    "success": true,
    "api": "MusicAPI",
    "version": "v2",
    "status": "online"
}
```

## Step 2 — Register

```http
POST /auth/register
```

## Step 3 — Login

```http
POST /auth/login
```

Copy the JWT token.

## Step 4 — Test public endpoints

```text
GET /home
GET /songs
GET /songs/1
GET /songs/trending
GET /songs/popular
GET /songs/new
GET /artists
GET /artists/1/tracks
GET /artists/1/albums
GET /albums
GET /albums/1/tracks
GET /tracks?tags=Hindi
GET /search?q=agar
```

## Step 5 — Add Bearer Token

In Thunder Client:

```text
Auth
→ Bearer
→ Token
→ paste JWT
```

## Step 6 — Test protected endpoints

```text
GET /songs/recommended
GET /favorites
POST /favorites
GET /history
POST /history
GET /playlists
POST /playlists
GET /profile
PUT /profile
GET /settings
PUT /settings
```

---

# 23. Current Endpoint Checklist

## Authentication

```text
☐ POST /auth/register
☐ POST /auth/login
☐ POST /auth/logout
```

## Home

```text
☐ GET /home
```

## Songs

```text
☐ GET /songs
☐ GET /songs/trending
☐ GET /songs/popular
☐ GET /songs/new
☐ GET /songs/recommended
☐ GET /songs/{id}
```

## Artists

```text
☐ GET /artists
☐ GET /artists/{id}
☐ GET /artists/{id}/tracks
☐ GET /artists/{id}/albums
```

## Albums

```text
☐ GET /albums
☐ GET /albums/{id}
☐ GET /albums/{id}/tracks
```

## Genres

```text
☐ GET /tracks?tags={genre}
```

## Search

```text
☐ GET /search?q={keyword}
☐ GET /search?q={keyword}&type=song
☐ GET /search?q={keyword}&type=artist
```

## Playlists

```text
☐ GET /playlists
☐ GET /playlists/{id}/tracks
☐ POST /playlists
☐ PUT /playlists/{id}
☐ DELETE /playlists/{id}
☐ POST /playlists/{id}/songs
☐ DELETE /playlists/{id}/songs/{songId}
```

## Favorites

```text
☐ GET /favorites
☐ POST /favorites
☐ DELETE /favorites/{songId}
```

## History

```text
☐ GET /history
☐ POST /history
```

## Profile

```text
☐ GET /profile
☐ PUT /profile
```

## Settings

```text
☐ GET /settings
☐ PUT /settings
```

---

# 24. Endpoints NOT Yet Assumed

The following are not included as current endpoints unless they are added to `routes/api.php` and implemented:

```text
/auth/refresh
/auth/forgot-password
/auth/reset-password
/auth/verify-email/{token}

/songs/{id}/lyrics
/songs/{id}/stream
/songs/{id}/download
/songs/{id}/similar

/artists/{id}/follow
/artists/{id}/unfollow
/artists/following

/search/suggestions

/playlists/public/{id}
/playlists/{id}/reorder

/history DELETE
/queue
/now-playing

/profile/avatar
```

Do not add these to Flutter until the corresponding PHP route/controller/model actually exists.

---

# 25. Important Router Rule

Specific routes must appear before parameter routes.

Correct:

```php
$router->get('songs/trending', [SongController::class, 'trending']);
$router->get('songs/popular', [SongController::class, 'popular']);
$router->get('songs/new', [SongController::class, 'latest']);

$router->get('songs/{id}', [SongController::class, 'show']);
```

Do not put:

```php
$router->get('songs/{id}', [SongController::class, 'show']);
```

before:

```php
$router->get('songs/trending', ...);
```

Otherwise the router can interpret:

```text
trending
```

as the `{id}` value.

---

# 26. Flutter API Configuration

For Windows/localhost:

```dart
const baseUrl = 'http://localhost/MusicAPI-v2/public';
```

For smartphone testing, replace `localhost` with the computer's IPv4 address:

```dart
const baseUrl = 'http://192.168.1.10/MusicAPI-v2/public';
```

Example:

```dart
final url = Uri.parse('$baseUrl/songs?page=1');
```

Protected request:

```dart
headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
}
```

---

# 27. Recommended Testing Order

Use this order when testing the complete API:

```text
1. GET /
2. POST /auth/register
3. POST /auth/login
4. GET /home
5. GET /songs
6. GET /songs/1
7. GET /songs/trending
8. GET /songs/popular
9. GET /songs/new
10. GET /artists
11. GET /artists/1/tracks
12. GET /artists/1/albums
13. GET /albums
14. GET /albums/1/tracks
15. GET /tracks?tags=Hindi
16. GET /search?q=agar
17. GET /search?q=arijit
18. GET /playlists
19. GET /playlists/1/tracks
20. GET /favorites
21. POST /favorites
22. GET /history
23. POST /history
24. GET /profile
25. PUT /profile
26. GET /settings
27. PUT /settings
28. POST /auth/logout
```

For protected endpoints, use the JWT obtained from login.

---

# 28. Final API Structure

```text
MusicAPI V2
│
├── Authentication
│   ├── Register
│   ├── Login
│   └── Logout
│
├── Home
│
├── Songs
│   ├── All
│   ├── Trending
│   ├── Popular
│   ├── New
│   ├── Recommended
│   └── Details
│
├── Artists
│   ├── All
│   ├── Details
│   ├── Tracks
│   └── Albums
│
├── Albums
│   ├── All
│   ├── Details
│   └── Tracks
│
├── Genres
│   └── Filter Tracks
│
├── Search
│   ├── Songs
│   └── Artists
│
├── Playlists
│   ├── List
│   ├── Details + Tracks
│   ├── Create
│   ├── Update
│   ├── Delete
│   ├── Add Song
│   └── Remove Song
│
├── Favorites
│   ├── List
│   ├── Add
│   └── Remove
│
├── History
│   ├── List
│   └── Add
│
├── Profile
│   ├── View
│   └── Update
│
└── Settings
    ├── View
    └── Update
```

---

## Important

This document is a **current implementation guide**, not a list of future features. If a route is not present in `routes/api.php`, it should not be called from Flutter yet.

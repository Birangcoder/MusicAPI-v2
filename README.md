# MusicAPI V2 Documentation

# MusicAPI V2

A PHP 8.3 + MySQL REST API for a Flutter music streaming application.

MusicAPI V2 provides authentication, songs, artists, albums, playlists, favorites, listening history, recommendations, search, playback tracking, and an optimized home feed.

---

## Features

- JWT-based authentication
- User registration and login
- User profile and settings
- Song browsing and optimized song cards
- Trending, popular, latest, and recommended songs
- Artist and album browsing
- Song and artist search
- Favorites
- Listening history
- Continue Listening
- Playlists and playlist tracks
- Artist following
- Playback progress tracking
- Pagination
- MySQL database with prepared statements
- Shared database connection through a singleton `Database` class
- Cloud-hosted media URLs such as Cloudinary
- Flutter-friendly JSON responses

---

# API Base URL

For local WAMP development, the API can be accessed through the project's public entry point.

Example:

```text
http://localhost/MusicAPI-v2/public
```

Example request:

```text
GET http://localhost/MusicAPI-v2/public/home
```

For production, configure the API behind a domain or virtual host so the Flutter application only needs one configurable base URL.

---

# Response Format

The API uses a consistent response envelope:

```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
```

Validation or other errors use the same general structure:

```json
{
  "success": false,
  "message": "Validation Failed",
  "errors": {}
}
```

---

# Authentication

Protected endpoints use a JWT access token.

```http
Authorization: Bearer ACCESS_TOKEN
```

Example:

```http
GET /home
Authorization: Bearer eyJ...
```

The API determines the authenticated user from the token. Flutter should not send a user ID as a substitute for authentication.

For anonymous users, endpoints that support optional authentication can return public data without a token.

---

# Authentication Endpoints

## Register

```http
POST /register
```

Example:

```json
{
  "name": "Birang",
  "email": "birang@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

The password confirmation must match the password.

---

## Login

```http
POST /login
```

Example:

```json
{
  "email": "birang@example.com",
  "password": "password123"
}
```

The login response contains the authenticated user/session information used by Flutter for subsequent protected requests.

---

# Home

## Get Home

```http
GET /home
```

Authenticated users receive personalized sections such as recommendations and continue listening.

The response is intentionally optimized for the Flutter home screen.

```json
{
  "success": true,
  "message": "Success",
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

# Optimized Song Format

Song collections use the same compact song-card structure.

This structure is used by:

- Home
- Trending
- Popular
- New Release
- Recommended
- Favorites
- History
- Continue Listening
- Song search/listing where applicable

Example:

```json
{
  "id": 26,
  "title": "Gir Gajavti Aavi Sinh",
  "slug": "gir-gajavti-aavi-sinh",
  "media": {
    "cover_url": "https://...",
    "audio_url": "https://...",
    "duration_seconds": 186
  },
  "metadata": {
    "language": "Gujarati",
    "release_date": "2020-01-01"
  },
  "artists": [
    {
      "id": 25,
      "name": "Aditya Gadhvi",
      "slug": "aditya-gadhvi"
    }
  ]
}
```

The Home endpoint should not send unnecessary full song information when the UI only needs a song card.

---

# Songs

## List Songs

```http
GET /songs
```

Supports pagination and available filters defined by the active route implementation.

---

## Get Song

```http
GET /songs/{id}
```

Example:

```text
GET /songs/20
```

Returns the complete song details required by the song/player screen.

---

## Trending Songs

```http
GET /songs/trending
```

Example:

```text
GET /songs/trending?page=1&limit=10
```

---

## Popular Songs

```http
GET /songs/popular
```

Example:

```text
GET /songs/popular?page=1&limit=10
```

Popular songs are ordered using listening/engagement information such as play count, likes, and downloads.

---

## Latest Songs

```http
GET /songs/latest
```

Alias:

```http
GET /songs/new
```

Latest songs are ordered by release date.

---

## Recommended Songs

```http
GET /songs/recommended
```

Recommendations are user-specific and require authentication.

The recommendation system can use user activity such as:

- Listening history
- Favorites
- Followed artists

New users can receive an empty recommendation section and Flutter can decide whether to hide that section.

---

# Playback

## Start Playback

```http
POST /songs/{id}/play
```

Example:

```text
POST /songs/20/play
Authorization: Bearer ACCESS_TOKEN
```

This endpoint is intended to record the beginning of playback and update playback-related statistics.

---

## Playback Progress

```http
POST /songs/{id}/progress
```

Example:

```json
{
  "play_duration": 125,
  "completed": false
}
```

When the song finishes:

```json
{
  "play_duration": 186,
  "completed": true
}
```

### Meaning

`play_duration`:

```text
Number of seconds actually listened.
```

`completed`:

```text
true  = playback reached the end
false = user stopped/skipped/left before completion
```

Flutter should not call this endpoint every second. A periodic update or an update when playback stops/finishes is sufficient.

---

# Search

Search can return songs and artists according to the active search implementation.

Examples:

```text
GET /search?q=agar
GET /search?q=mere
```

Search should support matching song titles and artist names where enabled by the active API implementation.

---

# Artists

## List Artists

```http
GET /artists
```

Supports pagination.

Optimized artist objects can be returned as:

```json
{
  "id": 25,
  "name": "Aditya Gadhvi",
  "slug": "aditya-gadhvi"
}
```

Full artist endpoints can include additional artist information such as image and verification status.

---

## Artist Details

```http
GET /artists/{id}
```

---

## Artist Tracks

```http
GET /artists/{id}/tracks
```

Supports pagination.

---

## Artist Albums

```http
GET /artists/{id}/albums
```

Supports pagination.

---

# Albums

## List Albums

```http
GET /albums
```

Example album object:

```json
{
  "id": 8,
  "title": "Aaj Ki Raat - Single",
  "slug": "aaj-ki-raat-single",
  "description": "Single release of Aaj Ki Raat",
  "cover_url": "https://...",
  "release_date": "2024-07-24",
  "album_type": "Single",
  "copyright": null,
  "label": "Independent",
  "total_tracks": 1
}
```

Album cover URLs are returned directly as:

```json
"cover_url": "https://..."
```

They are not wrapped inside a `media` object.

---

## Album Details

```http
GET /albums/{id}
```

The album detail response can include its tracks.

Track and disc numbers are stored in the `song_albums` relationship table rather than directly in `songs`.

---

## Album Search

```http
GET /albums/search?q=...
```

---

# Favorites

Favorites are user-specific and require authentication.

## Get Favorites

```http
GET /favorites
```

Example:

```text
GET /favorites?page=1&limit=20
Authorization: Bearer ACCESS_TOKEN
```

Response:

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "tracks": [
      {
        "id": 26,
        "title": "Gir Gajavti Aavi Sinh",
        "slug": "gir-gajavti-aavi-sinh",
        "media": {
          "cover_url": "https://...",
          "audio_url": "https://...",
          "duration_seconds": 186
        },
        "metadata": {
          "language": "Gujarati",
          "release_date": "2020-01-01"
        },
        "artists": []
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 1,
      "total_pages": 1,
      "has_next": false,
      "has_previous": false
    }
  }
}
```

Favorites intentionally use the same song-card structure as the other song collections.

---

## Add Favorite

```http
POST /songs/{id}/favorite
```

Requires authentication.

The favorite should be unique per user/song.

---

## Remove Favorite

```http
DELETE /songs/{id}/favorite
```

Requires authentication.

---

## Check Favorite

```http
GET /songs/{id}/favorite
```

Example response:

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "is_favorite": true
  }
}
```

---

# History

History is user-specific.

## Get History

```http
GET /history
```

Example:

```text
GET /history?page=1&limit=20
Authorization: Bearer ACCESS_TOKEN
```

History uses the same optimized song-card structure as Favorites.

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "tracks": [
      {
        "id": 11,
        "title": "Vishvambhari Stuti",
        "slug": "vishvambhari-stuti",
        "media": {
          "cover_url": "https://...",
          "audio_url": "https://...",
          "duration_seconds": 377
        },
        "metadata": {
          "language": "Gujarati",
          "release_date": "2020-01-01"
        },
        "artists": []
      }
    ],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 1,
      "total_pages": 1,
      "has_next": false,
      "has_previous": false
    }
  }
}
```

The history-specific database fields such as `played_at`, `play_duration`, and `completed` are used internally for listening behavior and recommendations.

---

## Add History

```http
POST /history
```

Example:

```json
{
  "song_id": 20,
  "play_duration": 125,
  "completed": false,
  "device": "Android"
}
```

---

## Delete History Item

```http
DELETE /history/{id}
```

---

## Clear History

```http
DELETE /history
```

---

# Continue Listening

Continue Listening is included in:

```text
GET /home
```

The response uses the exact same optimized song-card format:

```json
"continue_listening": [
  {
    "id": 20,
    "title": "Song Title",
    "slug": "song-title",
    "media": {
      "cover_url": "...",
      "audio_url": "...",
      "duration_seconds": 186
    },
    "metadata": {
      "language": "Gujarati",
      "release_date": "2020-01-01"
    },
    "artists": []
  }
]
```

The API selects the user's recently played unique songs.

---

# Playlists

Playlists are user-owned.

The current database uses:

```text
playlists.title
```

not:

```text
playlists.name
```

Playlist songs are stored in:

```text
playlist_songs
```

with ordering controlled by:

```text
playlist_songs.position
```

Typical operations include:

```text
GET    /playlists
POST   /playlists
GET    /playlists/{id}
PUT    /playlists/{id}
DELETE /playlists/{id}

GET    /playlists/{id}/tracks
POST   /playlists/{id}/tracks
DELETE /playlists/{id}/tracks/{songId}
```

Playlist access must respect the playlist owner's user ID and the playlist's public/private state.

---

# Artist Following

Users can follow artists.

The relationship is stored in:

```text
artist_follows
```

with:

```text
user_id
artist_id
```

This information can be used by the recommendation system.

---

# Profile

The user's profile information is stored in the `users` table.

Profile fields include information such as:

```text
name
avatar_url
country
birth_date
gender
bio
```

Profile updates require authentication.

---

# Database Structure

Main tables:

```text
users
user_settings

songs
artists
albums
genres

song_artists
song_albums
song_genres

favorites
history

playlists
playlist_songs

artist_follows
search_history

song_views
```

Important relationships:

```text
songs
 ├── song_artists ──> artists
 ├── song_albums  ──> albums
 └── song_genres  ──> genres

users
 ├── favorites
 ├── history
 ├── playlists
 ├── artist_follows
 └── search_history
```

---

# Important Database Details

## Song artists

Artists are connected to songs through:

```text
song_artists
```

This table also contains the artist role.

---

## Song albums

Album relationships are stored in:

```text
song_albums
```

Track ordering uses:

```text
track_number
disc_number
```

These fields should not be read from `songs`.

Correct ordering:

```sql
ORDER BY
    sa.disc_number ASC,
    sa.track_number ASC,
    s.id ASC
```

---

## Playlist ordering

Playlist track order is stored in:

```text
playlist_songs.position
```

---

## Favorites

The favorite relationship is:

```text
user_id + song_id
```

A user should not be able to create duplicate favorites for the same song.

---

## History

History stores:

```text
user_id
song_id
played_at
play_duration
completed
device
```

`play_duration` is the number of seconds listened.

`completed` indicates whether playback reached the end.

---

# Database Connection

The API uses a singleton `Database` class.

```php
Database::getInstance()->connection();
```

The singleton creates one `mysqli` connection for the current PHP request.

Different models such as:

```text
Song
Album
Artist
Playlist
History
```

reuse that same connection during the request.

It does not create a new MySQL connection every time a model requests the database.

A new HTTP request normally has its own PHP request lifecycle and therefore its own connection lifecycle.

---

# Flutter Integration

The Flutter application should keep the API base URL in one place.

Example:

```dart
class ApiConfig {
  static const String baseUrl =
      'http://localhost/MusicAPI-v2/public';

  static String get home => '$baseUrl/home';
  static String get login => '$baseUrl/login';
  static String get register => '$baseUrl/register';
}
```

For protected requests:

```dart
final session = await storage.readTokens();

final response = await http.get(
  Uri.parse(ApiConfig.home),
  headers: {
    'Accept': 'application/json',
    if (session != null)
      'Authorization': 'Bearer ${session.accessToken}',
  },
);
```

The exact token property should match the application's `LoginResponse` model.

---

# Recommended Flutter Home Model

Because the API uses the same song-card structure across Home, Favorites, History, and Continue Listening, Flutter can reuse one `SongModel`.

For example:

```dart
class HomeModel {
  final List<SongModel> trending;
  final List<SongModel> popular;
  final List<SongModel> newRelease;
  final List<SongModel> recommended;
  final List<ArtistModel> topArtists;
  final List<AlbumModel> topAlbums;
  final List<SongModel> continueListening;

  const HomeModel({
    required this.trending,
    required this.popular,
    required this.newRelease,
    required this.recommended,
    required this.topArtists,
    required this.topAlbums,
    required this.continueListening,
  });
}
```

---

# Pagination

Paginated collections return:

```json
{
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 100,
    "total_pages": 5,
    "has_next": true,
    "has_previous": false
  }
}
```

The API clamps requested limits to the configured maximum.

A `limit` controls how many records are returned on the current page.

It does **not** change `total`.

For example:

```text
limit = 5
total = 7
total_pages = 2
```

means there are seven total matching records, five on page one, and two on page two.

---

# Home Recommendation Logic

Recommendations are personalized rather than simply returning the popular list.

Relevant user signals include:

```text
Listening history
Favorites
Followed artists
```

The goal is to avoid making:

```text
popular == recommended
```

for every user.

For a new user without enough activity, Flutter can hide the recommendation section until the API has enough user-specific information.

---

# API Design Principles

The API follows these principles:

1. Use prepared SQL statements.
2. Keep authentication server-side through JWT.
3. Keep user-specific resources protected.
4. Reuse compact song-card responses for list screens.
5. Avoid sending unnecessary full objects from `/home`.
6. Keep album and artist responses separate from song media data.
7. Use pagination for large collections.
8. Use database relationships rather than duplicating relationship data.
9. Keep GET requests read-oriented.
10. Use POST/DELETE for state-changing operations.

---

# Development Notes

The API uses:

```text
PHP 8.3
MySQL
mysqli
JWT authentication
Apache/WAMP for local development
Cloudinary for media hosting
Flutter as the primary client
```

Media files are not required to be stored in the API project. The API stores and returns their external URLs.

---

# Project Structure

Typical backend structure:

```text
MusicAPI-v2/
├── app/
│   ├── Controllers/
│   ├── Core/
│   ├── Helpers/
│   ├── Middleware/
│   └── Models/
│
├── config/
├── public/
│   └── index.php
│
└── ...
```

The `public` directory is the web entry point.

---

# Error Handling

Typical HTTP statuses:

```text
200 OK
201 Created
400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
409 Conflict
422 Unprocessable Entity
500 Internal Server Error
```

Validation errors provide field-level information where applicable.

Example:

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

# Security Notes

- Never hard-code database credentials in source control.
- Never expose JWT secrets.
- Use HTTPS in production.
- Validate and sanitize request input.
- Use prepared statements for database queries.
- Protect user-specific endpoints with authentication.
- Do not trust a user ID supplied by Flutter when a JWT can identify the user.
- Keep production database credentials outside public web directories.

---

# Production Checklist

Before deployment:

- [ ]  Configure production database credentials.
- [ ]  Configure JWT secret securely.
- [ ]  Enable HTTPS.
- [ ]  Point the web server to the `public` directory.
- [ ]  Configure CORS if the client requires it.
- [ ]  Disable PHP/Xdebug error output in production.
- [ ]  Verify authentication.
- [ ]  Verify Home for authenticated and anonymous users.
- [ ]  Verify Favorites.
- [ ]  Verify History.
- [ ]  Verify playback and progress.
- [ ]  Verify playlists.
- [ ]  Verify search.
- [ ]  Verify pagination.
- [ ]  Verify Cloudinary URLs.
- [ ]  Test the API from the Flutter application.

---

# Documentation Policy

This README describes the current API design and database structure.

When the backend changes, update:

- routes
- request bodies
- response examples
- authentication requirements
- database relationships
- Flutter integration examples

Do not document planned functionality as an active endpoint.

Static developer documentation website for MusicAPI V2 — a PHP 8.3 + MySQL REST API built as the backend for a Flutter music application.

## Run

Open `index.html` directly, or serve the folder with any static HTTP server.

## What's included

- Full route reference (42 active endpoints) with request/response examples, path/query parameters, and error codes
- Database schema — every table with its columns, plus an ER diagram
- Flutter integration snippets (GET, POST, JWT, pagination)
- A live "API Explorer" panel to send real requests against Production or Local
- Searchable, filterable route table and sidebar navigation
- Dark/light theme toggle, copy-to-clipboard on every code block
- "Implementation Notes" section that clearly flags tables/controllers that exist but have no active route — nothing unimplemented is presented as a live endpoint

## Customize

- Replace the GitHub `href` in `index.html` with your repository.
- Update routes/details in `script.js` (`routes`, `endpoints`, `dbTables`).
- Production/local base URLs are selectable in the header and drive the API Explorer.

The content is based on the current implementation only and does not document planned/future functionality.

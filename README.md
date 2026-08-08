# MusicAPI V2.1 — Improved REST API Documentation

PHP + MySQL backend reference for Thunder Client and Flutter integration

**Base URL (dev):**`http://localhost/MusicAPI-v2/public/api/v2`**Base URL (prod):**`https://api.yourdomain.com/v2`**Environment:** development → staging → production

> **Why the URL changed:** Your original base URL had no `/api` or version prefix in the path. Once you deploy, `localhost` breaks for every mobile client. Standardize now on `/api/v2/...` so you can run `v1` and `v2` side by side later without breaking old app builds still in users' hands.

---

## What Changed From V2 → V2.1 (Summary)


| Area             | Old                               | New                                                                                   |
| ---------------- | --------------------------------- | ------------------------------------------------------------------------------------- |
| Response format  | Inconsistent per-endpoint         | Single envelope for every response                                                    |
| Errors           | Not specified                     | Standard error codes + HTTP status mapping                                            |
| Auth             | JWT, no refresh                   | JWT access token (15 min) + refresh token (30 days)                                   |
| Streaming        | Not covered                       | Range-request streaming endpoint + signed URLs                                        |
| Pagination       | "if implemented"                  | Mandatory`meta`block on every list endpoint                                           |
| Search           | Single`q`param                    | Add`type`filter + per-type endpoints                                                  |
| Missing features | —                                | Queue/now-playing, follow artist, lyrics, password reset, avatar upload, admin upload |
| Rate limiting    | Not covered                       | Documented limits + headers                                                           |
| Security         | Plaintext base URL, no HTTPS note | HTTPS required, refresh token rotation, rate limits                                   |

---

## 1. Standard Response Envelope

**Every** response — success or error — uses this shape. This is the single biggest gap in the original doc; without it, your Flutter app needs custom parsing logic per endpoint.

**Success:**

```json
{
  "success": true,
  "data": { },
  "meta": null
}
```

**Success with pagination:**

```json
{
  "success": true,
  "data": [ ],
  "meta": {
    "page": 1,
    "limit": 20,
    "total_items": 143,
    "total_pages": 8,
    "has_next": true,
    "has_prev": false
  }
}
```

**Error:**

```json
{
  "success": false,
  "error": {
    "code": "INVALID_CREDENTIALS",
    "message": "Email or password is incorrect.",
    "field": null
  }
}
```

### Standard Error Codes


| HTTP | code                  | Meaning                                                           |
| ---- | --------------------- | ----------------------------------------------------------------- |
| 400  | `VALIDATION_ERROR`    | Bad input;`field`tells you which one                              |
| 401  | `UNAUTHORIZED`        | Missing/expired token                                             |
| 401  | `INVALID_CREDENTIALS` | Bad login                                                         |
| 403  | `FORBIDDEN`           | Valid token, no permission (e.g. editing someone else's playlist) |
| 404  | `NOT_FOUND`           | Resource doesn't exist                                            |
| 409  | `ALREADY_EXISTS`      | e.g. duplicate email on register                                  |
| 422  | `SONG_NOT_PLAYABLE`   | Media file missing/unpublished                                    |
| 429  | `RATE_LIMITED`        | Too many requests — see §14                                     |
| 500  | `SERVER_ERROR`        | Unhandled exception                                               |

---

## 2. Authentication


| Method | Endpoint                     | Purpose                                     | Auth                       |
| ------ | ---------------------------- | ------------------------------------------- | -------------------------- |
| POST   | `/auth/register`             | Create account                              | No                         |
| POST   | `/auth/login`                | Login, returns access + refresh token       | No                         |
| POST   | `/auth/refresh`              | Exchange refresh token for new access token | No (refresh token in body) |
| POST   | `/auth/logout`               | Revoke refresh token                        | Yes                        |
| POST   | `/auth/forgot-password`      | Send reset email/OTP                        | No                         |
| POST   | `/auth/reset-password`       | Set new password with reset token           | No                         |
| GET    | `/auth/verify-email/{token}` | Confirm email                               | No                         |

**Why refresh tokens matter:** your original spec issues one JWT with no expiry story. If it's long-lived, a stolen token is a permanent breach. If it's short-lived with no refresh flow, users get logged out constantly. Standard fix: short-lived access token (15 min) + long-lived refresh token (30 days, rotated on use, revocable on logout).

**Login response:**

```json
{
  "success": true,
  "data": {
    "user": { "id": 12, "name": "Test User", "email": "test2@test.com" },
    "access_token": "eyJ...",
    "refresh_token": "8f3a...",
    "expires_in": 900
  }
}
```

**Refresh:**

```json
POST /auth/refresh
{ "refresh_token": "8f3a..." }
```

---

## 3. Home


| Method | Endpoint | Purpose                                                         | Auth       |
| ------ | -------- | --------------------------------------------------------------- | ---------- |
| GET    | `/home`  | Aggregated home feed (banners, trending, new releases, for-you) | Optional\* |

\*Optional auth: if a token is present, personalize `for_you`; if not, return generic trending content. Don't hard-require login just to browse — that kills first-run conversion.

---

## 4. Songs


| Method | Endpoint              | Purpose                                  | Auth    |
| ------ | --------------------- | ---------------------------------------- | ------- |
| GET    | `/songs`              | Paginated song list                      | No      |
| GET    | `/songs/trending`     | Trending songs                           | No      |
| GET    | `/songs/popular`      | Popular songs                            | No      |
| GET    | `/songs/new`          | Latest releases                          | No      |
| GET    | `/songs/recommended`  | Personalized recommendations             | Yes     |
| GET    | `/songs/{id}`         | Single song details                      | No      |
| GET    | `/songs/{id}/lyrics`  | **New:**Time-synced or plain lyrics      | No      |
| GET    | `/songs/{id}/stream`  | **New:**Actual playable audio (see §12) | Depends |
| GET    | `/songs/{id}/similar` | **New:**"More like this"                 | No      |

Route order note carried over correctly from your original doc — keep `/songs/trending`, `/songs/popular`, `/songs/new` registered **before**`/songs/{id}` or the router will try to treat "trending" as an id.

**Query params for `/songs`:**`page`, `limit`, `sort` (`newest`, `popular`, `alphabetical`), `genre`

---

## 5. Artists


| Method | Endpoint               | Purpose                                             | Auth |
| ------ | ---------------------- | --------------------------------------------------- | ---- |
| GET    | `/artists`             | Paginated artist list                               | No   |
| GET    | `/artists/{id}`        | **New:**Artist profile (bio, image, follower count) | No   |
| GET    | `/artists/{id}/tracks` | Songs by artist                                     | No   |
| GET    | `/artists/{id}/albums` | Albums by artist                                    | No   |
| POST   | `/artists/{id}/follow` | **New:**Follow artist                               | Yes  |
| DELETE | `/artists/{id}/follow` | **New:**Unfollow                                    | Yes  |
| GET    | `/artists/following`   | **New:**Artists the current user follows            | Yes  |

Your original doc jumps straight to `/artists/{id}/tracks` with no `/artists/{id}` endpoint — meaning the app can't show an artist's own bio/photo page without a workaround.

---

## 6. Albums


| Method | Endpoint              | Purpose                                                    | Auth |
| ------ | --------------------- | ---------------------------------------------------------- | ---- |
| GET    | `/albums`             | Paginated album list                                       | No   |
| GET    | `/albums/{id}`        | **New:**Album details (title, cover, release date, artist) | No   |
| GET    | `/albums/{id}/tracks` | Track list for album                                       | No   |

Same gap as artists — you had `/albums/{id}/tracks` but nothing returning the album's own metadata separately.

---

## 7. Genres / Tags


| Method | Endpoint             | Purpose                                  | Auth |
| ------ | -------------------- | ---------------------------------------- | ---- |
| GET    | `/genres`            | **New:**List all genres/tags with counts | No   |
| GET    | `/tracks?tags=Hindi` | Filter tracks by tag                     | No   |

`GET /genres` lets your Flutter UI build a genre picker dynamically instead of hardcoding tag names in the app.

---

## 8. Search


| Method | Endpoint                                     | Purpose                          | Auth |
| ------ | -------------------------------------------- | -------------------------------- | ---- |
| GET    | `/search?q=`                                 | Search across everything         | No   |
| GET    | `/search?q=&type=song|artist|album|playlist` | **New:**Scoped search            | No   |
| GET    | `/search/suggestions?q=`                     | **New:**Autocomplete-as-you-type | No   |

A single unscoped search endpoint forces the client to guess result types from response shape. Add `type` so the UI can show separate tabs (Songs / Artists / Albums) without client-side filtering.

---

## 9. Playlists


| Method | Endpoint                         | Purpose                                          | Auth |
| ------ | -------------------------------- | ------------------------------------------------ | ---- |
| GET    | `/playlists`                     | Current user's playlists                         | Yes  |
| GET    | `/playlists/public/{id}`         | **New:**View a public playlist without owning it | No   |
| GET    | `/playlists/{id}/tracks`         | Playlist details + tracks                        | Yes  |
| POST   | `/playlists`                     | Create playlist                                  | Yes  |
| PUT    | `/playlists/{id}`                | Update playlist                                  | Yes  |
| DELETE | `/playlists/{id}`                | Delete playlist                                  | Yes  |
| POST   | `/playlists/{id}/songs`          | Add song                                         | Yes  |
| DELETE | `/playlists/{id}/songs/{songId}` | Remove song                                      | Yes  |
| PUT    | `/playlists/{id}/reorder`        | **New:**Reorder tracks (drag-and-drop)           | Yes  |

DB note carried over: `playlists.title` (not `name`); `playlist_songs` has `playlist_id, song_id, position, added_at`.

Reorder endpoint matters because without it, moving one song requires deleting and re-adding every song after it just to fix `position`.

```json
PUT /playlists/1/reorder
{ "song_id": 28, "new_position": 3 }
```

---

## 10. Favorites


| Method | Endpoint              | Purpose         | Auth |
| ------ | --------------------- | --------------- | ---- |
| GET    | `/favorites`          | List favorites  | Yes  |
| POST   | `/favorites`          | Add favorite    | Yes  |
| DELETE | `/favorites/{songId}` | Remove favorite | Yes  |

**Fix:** your original had `DELETE /favorites/{id}` — ambiguous whether `{id}` is the favorite row's id or the song's id. Use `{songId}` explicitly so the Flutter client doesn't need a separate lookup call just to unfavorite a song it already has loaded.

---

## 11. History & Now Playing


| Method | Endpoint       | Purpose                                          | Auth |
| ------ | -------------- | ------------------------------------------------ | ---- |
| GET    | `/history`     | Listening history                                | Yes  |
| POST   | `/history`     | Record playback event                            | Yes  |
| DELETE | `/history`     | **New:**Clear history                            | Yes  |
| GET    | `/queue`       | **New:**Current play queue (cross-device sync)   | Yes  |
| PUT    | `/queue`       | **New:**Replace/update queue                     | Yes  |
| GET    | `/now-playing` | **New:**What's currently playing on this account | Yes  |

Queue/now-playing matter if you ever want "continue on another device" — Spotify-style. Without server-side queue state, playback state lives only in the Flutter app's memory and is lost on app switch.

---

## 12. Streaming & Media

This section is entirely missing from your original doc, and it's the most important gap for an actual working app.


| Method | Endpoint               | Purpose                                                  | Auth                       |
| ------ | ---------------------- | -------------------------------------------------------- | -------------------------- |
| GET    | `/songs/{id}/stream`   | Stream audio with HTTP Range support                     | Depends on song visibility |
| GET    | `/songs/{id}/download` | **New:**Signed, time-limited download URL (offline mode) | Yes                        |

* `/stream` must support `Range` request headers so Flutter's audio player can seek without downloading the whole file.
* Don't put raw Cloudinary URLs directly in list responses if you want play-count/analytics accuracy — proxy or sign them instead, so every play goes through your backend.
* Response for `/stream` should be a `307` redirect to a signed, expiring Cloudinary/CDN URL rather than piping bytes through PHP — cheaper and faster.

```
GET /songs/28/stream
→ 307 Redirect → https://res.cloudinary.com/.../song.mp3?token=...&expires=1723...
```

---

## 13. Profile & Settings


| Method | Endpoint          | Purpose                        | Auth |
| ------ | ----------------- | ------------------------------ | ---- |
| GET    | `/profile`        | Get profile                    | Yes  |
| PUT    | `/profile`        | Update profile                 | Yes  |
| POST   | `/profile/avatar` | **New:**Upload profile picture | Yes  |
| GET    | `/settings`       | Get settings                   | Yes  |
| PUT    | `/settings`       | Update settings                | Yes  |

---

## 14. Rate Limiting (New Section)

Not covered at all in the original doc — needed before you ship publicly.


| Scope                            | Limit                                     |
| -------------------------------- | ----------------------------------------- |
| Unauthenticated (search, browse) | 60 req/min per IP                         |
| Authenticated                    | 300 req/min per user                      |
| `/auth/login`,`/auth/register`   | 5 req/min per IP (brute-force protection) |

Return these headers on every response:

```
X-RateLimit-Limit: 300
X-RateLimit-Remaining: 287
X-RateLimit-Reset: 1723123200
```

---

## 15. Security Notes

* **HTTPS only in production.** The original base URL was `http://localhost` — fine for dev, but ship with TLS or the JWT is sniffable on any shared network.
* **Refresh token rotation:** issue a new refresh token every time one is used; invalidate the old one. Prevents replay if a token leaks.
* **Password reset tokens** expire in 15 minutes and are single-use.
* **Rate-limit auth endpoints** separately (see §14) — this is the #1 thing missing that matters for a real launch.

---

## 16. Complete Endpoint Checklist (V2.1)

```
Auth
☐ POST   /auth/register
☐ POST   /auth/login
☐ POST   /auth/refresh
☐ POST   /auth/logout
☐ POST   /auth/forgot-password
☐ POST   /auth/reset-password
☐ GET    /auth/verify-email/{token}

Home
☐ GET    /home

Songs
☐ GET    /songs
☐ GET    /songs/trending
☐ GET    /songs/popular
☐ GET    /songs/new
☐ GET    /songs/recommended
☐ GET    /songs/{id}
☐ GET    /songs/{id}/lyrics
☐ GET    /songs/{id}/stream
☐ GET    /songs/{id}/similar
☐ GET    /songs/{id}/download

Artists
☐ GET    /artists
☐ GET    /artists/{id}
☐ GET    /artists/{id}/tracks
☐ GET    /artists/{id}/albums
☐ POST   /artists/{id}/follow
☐ DELETE /artists/{id}/follow
☐ GET    /artists/following

Albums
☐ GET    /albums
☐ GET    /albums/{id}
☐ GET    /albums/{id}/tracks

Genres
☐ GET    /genres
☐ GET    /tracks?tags=

Search
☐ GET    /search?q=
☐ GET    /search?q=&type=
☐ GET    /search/suggestions?q=

Playlists
☐ GET    /playlists
☐ GET    /playlists/public/{id}
☐ GET    /playlists/{id}/tracks
☐ POST   /playlists
☐ PUT    /playlists/{id}
☐ DELETE /playlists/{id}
☐ POST   /playlists/{id}/songs
☐ DELETE /playlists/{id}/songs/{songId}
☐ PUT    /playlists/{id}/reorder

Favorites
☐ GET    /favorites
☐ POST   /favorites
☐ DELETE /favorites/{songId}

History / Queue
☐ GET    /history
☐ POST   /history
☐ DELETE /history
☐ GET    /queue
☐ PUT    /queue
☐ GET    /now-playing

Profile / Settings
☐ GET    /profile
☐ PUT    /profile
☐ POST   /profile/avatar
☐ GET    /settings
☐ PUT    /settings
```

---

## 17. Suggested Next Steps

1. Implement the response envelope (§1) first — it's a small change that fixes every other inconsistency downstream.
2. Add refresh tokens before you have real users, not after (painful to migrate later).
3. Build `/songs/{id}/stream` with Range support before wiring up the Flutter audio player — trying to add seeking after the fact means reworking both sides.
4. Everything marked **New** beyond that can be shipped incrementally; none of it blocks a v1 launch except streaming and the envelope.

# MusicAPI v2 Optimization

This version keeps `/songs/{id}` as the full song-details endpoint and uses a lightweight **track card** for list/home endpoints.

## Full song details

`GET /songs/{id}` remains the detailed response and includes media, metadata, statistics, artists, album, genres and links.

## Lightweight track card

Home and list responses now use only:

```json
{
  "id": 26,
  "title": "Gir Gajavti Aavi Sinh",
  "slug": "gir-gajavti-aavi-sinh",
  "media": {
    "cover_url": "...",
    "audio_url": "...",
    "duration_seconds": 186,
    "duration": "03:06"
  },
  "metadata": {
    "language": "Gujarati",
    "release_date": "2020-01-01"
  },
  "artists": [
    {
      "id": 1,
      "name": "Artist",
      "slug": "artist"
    }
  ]
}
```

The card intentionally does not include lyrics, description, album, genres, statistics, timestamps or `is_active`.

## Home

`GET /home` now returns the track arrays directly instead of repeating pagination metadata for each fixed five-item section.

Home sections:

- `trending`
- `popular`
- `new_release`
- `recommended`
- `top_artists`
- `top_albums`
- `continue_listening`

`top_artists` and `top_albums` use lightweight cards.

## N+1 query reduction

The previous pattern:

```text
SELECT song IDs
find(1)
find(2)
find(3)
...
```

has been replaced for song lists by one batched `cardsByIds()` query. This is used by song lists, recommendations, artist tracks, favorites, history and Home sections.

## Database indexes

Run `database/optimization_indexes.sql` once. It adds composite indexes for history, artist/album/genre relations, latest songs and popular songs.

## Pagination

List endpoints use:

```text
?page=1&limit=20
```

Home uses fixed small limits internally and does not expose pagination metadata for its fixed sections.

## Important endpoint distinction

Use `/songs/{id}` when the Flutter song-details/player screen needs complete metadata.

Use list/home endpoints when building cards and feeds.

-- MusicAPI v2 performance indexes
-- Run once on the music_app_v2 database.

ALTER TABLE history
    ADD INDEX idx_history_user_played (user_id, played_at),
    ADD INDEX idx_history_played_song (played_at, song_id, completed);

ALTER TABLE song_artists
    ADD INDEX idx_song_artists_artist_song (artist_id, song_id);

ALTER TABLE song_albums
    ADD INDEX idx_song_albums_album_song (album_id, song_id);

ALTER TABLE song_genres
    ADD INDEX idx_song_genres_genre_song (genre_id, song_id);

ALTER TABLE songs
    ADD INDEX idx_songs_active_release (is_active, deleted_at, release_date, id),
    ADD INDEX idx_songs_active_popular (is_active, deleted_at, play_count, like_count, download_count, id);

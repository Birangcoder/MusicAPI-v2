CREATE DATABASE IF NOT EXISTS music_app_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE music_app_v2;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_url TEXT NULL,
    country VARCHAR(100) NULL,
    birth_date DATE NULL,
    gender ENUM('male', 'female', 'other') DEFAULT 'other',
    bio TEXT NULL,
    is_premium TINYINT(1) DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE artists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE,
    bio TEXT NULL,
    country VARCHAR(100),
    image_url TEXT,
    cover_url TEXT,
    verified TINYINT(1) DEFAULT 0,
    monthly_listeners BIGINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE albums (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(191) UNIQUE,
    description TEXT,
    cover_url TEXT,
    release_date DATE,
    album_type ENUM('Album', 'Single', 'EP', 'Compilation') DEFAULT 'Album',
    copyright VARCHAR(255),
    label VARCHAR(255),
    total_tracks INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE genres (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE,
    image_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE songs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(191) UNIQUE,
    description TEXT,
    lyrics LONGTEXT,
    audio_url TEXT NOT NULL,
    cover_url TEXT,
    duration_seconds INT UNSIGNED NOT NULL,
    language VARCHAR(50),
    release_date DATE,
    track_number INT UNSIGNED DEFAULT 1,
    disc_number INT UNSIGNED DEFAULT 1,
    play_count BIGINT UNSIGNED DEFAULT 0,
    like_count BIGINT UNSIGNED DEFAULT 0,
    download_count BIGINT UNSIGNED DEFAULT 0,
    is_explicit TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE song_artists (
    song_id INT UNSIGNED NOT NULL,
    artist_id INT UNSIGNED NOT NULL,
    role ENUM('Main', 'Featured', 'Composer', 'Producer') DEFAULT 'Main',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(song_id, artist_id, role),
    CONSTRAINT fk_song_artist_song FOREIGN KEY(song_id) REFERENCES songs(id) ON DELETE CASCADE,
    CONSTRAINT fk_song_artist_artist FOREIGN KEY(artist_id) REFERENCES artists(id) ON DELETE CASCADE
);

CREATE TABLE song_albums (
    song_id INT UNSIGNED NOT NULL,
    album_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(song_id, album_id),
    CONSTRAINT fk_song_album_song FOREIGN KEY(song_id) REFERENCES songs(id) ON DELETE CASCADE,
    CONSTRAINT fk_song_album_album FOREIGN KEY(album_id) REFERENCES albums(id) ON DELETE CASCADE
);

CREATE TABLE song_genres (
    song_id INT UNSIGNED NOT NULL,
    genre_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(song_id, genre_id),
    CONSTRAINT fk_song_genre_song FOREIGN KEY(song_id) REFERENCES songs(id) ON DELETE CASCADE,
    CONSTRAINT fk_song_genre_genre FOREIGN KEY(genre_id) REFERENCES genres(id) ON DELETE CASCADE
);

INSERT INTO
    genres(name, slug)
VALUES
    ('Pop', 'pop'),
    ('Rock', 'rock'),
    ('Hip Hop', 'hip-hop'),
    ('Rap', 'rap'),
    ('R&B', 'r-and-b'),
    ('Jazz', 'jazz'),
    ('Blues', 'blues'),
    ('Classical', 'classical'),
    ('Country', 'country'),
    ('Electronic', 'electronic'),
    ('Dance', 'dance'),
    ('EDM', 'edm'),
    ('Lo-fi', 'lo-fi'),
    ('Instrumental', 'instrumental'),
    ('Folk', 'folk'),
    ('Romantic', 'romantic'),
    ('Devotional', 'devotional'),
    ('Punjabi', 'punjabi'),
    ('Hindi', 'hindi'),
    ('Tamil', 'tamil'),
    ('Telugu', 'telugu'),
    ('Malayalam', 'malayalam');

CREATE TABLE favorites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    song_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_favorite(user_id, song_id),
    CONSTRAINT fk_favorite_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorite_song FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
);

CREATE TABLE history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    song_id INT UNSIGNED NOT NULL,
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    play_duration INT UNSIGNED DEFAULT 0,
    completed TINYINT(1) DEFAULT 0,
    device VARCHAR(100) NULL,
    CONSTRAINT fk_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_history_song FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
    INDEX idx_history_user(user_id),
    INDEX idx_history_song(song_id),
    INDEX idx_history_played(played_at)
);

CREATE TABLE playlists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    cover_url TEXT NULL,
    is_public TINYINT(1) DEFAULT 1,
    total_songs INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_playlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_playlist_user(user_id)
);

CREATE TABLE playlist_songs (
    playlist_id INT UNSIGNED NOT NULL,
    song_id INT UNSIGNED NOT NULL,
    position INT UNSIGNED NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (playlist_id, song_id),
    UNIQUE KEY uk_playlist_position (playlist_id, position),
    CONSTRAINT fk_playlist_song_playlist FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
    CONSTRAINT fk_playlist_song_song FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
);

CREATE TABLE song_views (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    song_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    device VARCHAR(100) NULL,
    platform VARCHAR(50) NULL,
    CONSTRAINT fk_song_view_song FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
    CONSTRAINT fk_song_view_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE
    SET
        NULL,
        INDEX idx_song(song_id),
        INDEX idx_user(user_id),
        INDEX idx_viewed(viewed_at)
);

CREATE TABLE search_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    keyword VARCHAR(255) NOT NULL,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_search_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_search_user(user_id),
    INDEX idx_keyword(keyword),
    INDEX idx_searched(searched_at)
);

CREATE TABLE user_settings (
    user_id INT UNSIGNED PRIMARY KEY,
    theme ENUM('light', 'dark', 'system') DEFAULT 'system',
    language VARCHAR(10) DEFAULT 'en',
    stream_quality ENUM('low', 'medium', 'high', 'lossless') DEFAULT 'high',
    download_quality ENUM('low', 'medium', 'high', 'lossless') DEFAULT 'high',
    autoplay TINYINT(1) DEFAULT 1,
    crossfade_seconds TINYINT UNSIGNED DEFAULT 0,
    normalize_volume TINYINT(1) DEFAULT 1,
    explicit_content TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- INSERT INTO
--     banners (
--         title,
--         subtitle,
--         image_url,
--         redirect_type,
--         sort_order
--     )
-- VALUES
--     (
--         'Top Hits',
--         'Trending this week',
--         'https://example.com/banner1.jpg',
--         'playlist',
--         1
--     ),
--     (
--         'New Releases',
--         'Fresh music every Friday',
--         'https://example.com/banner2.jpg',
--         'album',
--         2
--     ),
--     (
--         'Editors Choice',
--         'Hand-picked songs',
--         'https://example.com/banner3.jpg',
--         'playlist',
--         3
--     );

CREATE TABLE artist_follows (
    user_id INT UNSIGNED NOT NULL,
    artist_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(user_id, artist_id),
    CONSTRAINT fk_follow_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_follow_artist FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM(
        'general',
        'artist',
        'playlist',
        'album',
        'system'
    ) DEFAULT 'general',
    reference_id INT UNSIGNED NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notification_user(user_id),
    INDEX idx_notification_read(is_read)
);

-- INSERT INTO
--     banners (
--         title,
--         subtitle,
--         image_url,
--         redirect_type,
--         sort_order
--     )
-- VALUES
--     (
--         'Top Hits',
--         'Trending this week',
--         'https://example.com/banner1.jpg',
--         'playlist',
--         1
--     ),
--     (
--         'New Releases',
--         'Fresh music every Friday',
--         'https://example.com/banner2.jpg',
--         'album',
--         2
--     ),
--     (
--         'Editor' s Choice ',
-- ' Hand - picked songs ',
-- ' https: / / example.com / banner3.jpg ',
-- ' playlist ',
-- 3
-- );

-- ===========================================
-- INDEXES
-- ===========================================

CREATE INDEX idx_song_title ON songs(title);
CREATE INDEX idx_song_slug ON songs(slug);
CREATE INDEX idx_song_release ON songs(release_date);
CREATE INDEX idx_song_play_count ON songs(play_count);
CREATE INDEX idx_song_like_count ON songs(like_count);
CREATE INDEX idx_song_download_count ON songs(download_count);
CREATE INDEX idx_song_active ON songs(is_active);

CREATE INDEX idx_artist_name ON artists(name);
CREATE INDEX idx_artist_slug ON artists(slug);
CREATE INDEX idx_artist_verified ON artists(verified);

CREATE INDEX idx_album_title ON albums(title);
CREATE INDEX idx_album_slug ON albums(slug);
CREATE INDEX idx_album_release ON albums(release_date);

CREATE INDEX idx_genre_name ON genres(name);

CREATE INDEX idx_song_artist_artist ON song_artists(artist_id);
CREATE INDEX idx_song_album_album ON song_albums(album_id);
CREATE INDEX idx_song_genre_genre ON song_genres(genre_id);



-- ===========================================
-- DEFAULT USER SETTINGS
-- ===========================================

DELIMITER $$

CREATE TRIGGER tr_create_user_settings
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO user_settings(user_id)
    VALUES(NEW.id);
END$$

DELIMITER ;



-- ===========================================
-- UPDATE PLAYLIST TOTAL SONGS
-- ===========================================

DELIMITER $$

CREATE TRIGGER tr_playlist_song_insert
AFTER INSERT ON playlist_songs
FOR EACH ROW
BEGIN
    UPDATE playlists
    SET total_songs = total_songs + 1
    WHERE id = NEW.playlist_id;
END$$

CREATE TRIGGER tr_playlist_song_delete
AFTER DELETE ON playlist_songs
FOR EACH ROW
BEGIN
    UPDATE playlists
    SET total_songs = GREATEST(total_songs - 1,0)
    WHERE id = OLD.playlist_id;
END$$

DELIMITER ;



-- ===========================================
-- DEFAULT GENRES
-- ===========================================

INSERT IGNORE INTO genres(name,slug) VALUES
(' Pop ',' pop '),
(' Rock ',' rock '),
(' Hip Hop ',' hip - hop '),
(' Rap ',' rap '),
(' R & B ',' r -
        and - b '),
(' Jazz ',' jazz '),
(' Blues ',' blues '),
(' Classical ',' classical '),
(' Country ',' country '),
(' Electronic ',' electronic '),
(' Dance ',' dance '),
(' EDM ',' edm '),
(' Lo - fi ',' lo - fi '),
(' Instrumental ',' instrumental '),
(' Folk ',' folk '),
(' Romantic ',' romantic '),
(' Devotional ',' devotional '),
(' Punjabi ',' punjabi '),
(' Hindi ',' hindi '),
(' Tamil ',' tamil '),
(' Telugu ',' telugu '),
(' Malayalam ',' malayalam ');



-- ===========================================
-- DEFAULT BANNERS
-- ===========================================

-- INSERT INTO banners
-- (title,subtitle,image_url,redirect_type,sort_order)
-- VALUES
-- (
-- ' Top Hits ',
-- ' Trending Songs ',
-- ' https: / / example.com / banner1.jpg ',
-- ' playlist ',
-- 1
-- ),
-- (
-- ' New Albums ',
-- ' Listen Now ',
-- ' https: / / example.com / banner2.jpg ',
-- ' album ',
-- 2
-- ),
-- (
-- ' Top Artists ',
-- ' Popular Artists ',
-- ' https: / / example.com / banner3.jpg ',
-- ' artist ',
-- 3
-- );
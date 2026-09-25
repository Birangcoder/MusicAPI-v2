# MusicAPI v1 + v2

One Render web service exposes two database-backed API versions.

- `/v1/*` = existing MySQL API. Existing `DB_*` Render variables remain for v1.
- `/v2/*` = PostgreSQL/Supabase API. Add `V2_DB_*` Render variables for v2.

## Render variables

Existing v1 variables: `APP_ENV`, `APP_URL`, `DB_HOST`, `DB_NAME`, `DB_PASS`, `DB_PORT`, `DB_USER`, `JWT_SECRET`.

Add for v2:

```text
V2_DB_HOST=...
V2_DB_PORT=5432
V2_DB_NAME=postgres
V2_DB_USER=...
V2_DB_PASS=...
V2_APP_URL=https://your-service.onrender.com/v2
```

## PostgreSQL import

1. In Supabase SQL Editor run `v2/database/schema.sql`.
2. Then run `v2/database/data.sql`.

`data.sql` is generated from the current `muzik.sql` dump and preserves current IDs/rows.

## URLs

```text
https://your-service.onrender.com/v1/songs
https://your-service.onrender.com/v2/songs
https://your-service.onrender.com/v1/health
https://your-service.onrender.com/v2/health
```

# MusicAPI V2 Documentation

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

# AM-zadanie

To setup run `php setup.php`

All messages are in `/messages` directory.

## Endpoints
- `/api/message/ GET` - list messages (accepts `sort-by` query parameter with values `date` and `uuid`), returns keys `id`, `created_at`, `content`.
- `/api/message/ POST` - create aa message (accepts JSON with `content` key)
- `/api/message/{id} GET` - get specific message data (`id`, `created_at`, `content`)

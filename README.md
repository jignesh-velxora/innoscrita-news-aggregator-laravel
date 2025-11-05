# Innoscripta News Aggregator API

A Laravel-based API that aggregates articles from multiple providers (NewsAPI, The Guardian, NYTimes), stores them in a MySQL database (SQLite supported for tests), and exposes endpoints to query and refresh articles. An hourly scheduler automatically refreshes articles.


## 1. Tech Stack
- PHP 8.2+
- Laravel 11
- MySQL 8 (or compatible) for local/dev
- SQLite for tests (configured in database/database.sqlite)
- HTTP Client: Laravel HTTP (Guzzle)


## 2. Local Setup
1) Install dependencies
- composer install

2) Configure environment
- Copy .env.example to .env (if not present)
- Update DB connection (MySQL recommended for local):
  - DB_CONNECTION=mysql
  - DB_HOST=127.0.0.1
  - DB_PORT=3306
  - DB_DATABASE=innoscripta
  - DB_USERNAME=root
  - DB_PASSWORD=your_password
- Add API keys
  - NEWSAPI_KEY=64d194fd0e0f4aa7a2c2ea47532309f8
  - GUARDIAN_KEY=e350554b-6d98-4b49-8ceb-6ad3ea07325d
  - NYT_KEY=HxjNsV6TRW7B9b98GNWOkexVO2JALQjG

3) App key and caches
- php artisan key:generate
- php artisan optimize:clear

4) Migrate database
- php artisan migrate

5) Serve the app
- php artisan serve
- Note the URL (e.g., http://127.0.0.1:8000)


## 3. Configuration Details
- API keys are read via config/services.php
  - services.newsapi.key -> env('NEWSAPI_KEY')
  - services.guardian.key -> env('GUARDIAN_KEY')
  - services.nyt.key -> env('NYT_KEY')
- Providers read keys from the config; constructor injection is supported for testing/mocking.


## 4. Data Model
- Article fields: id, source, external_id, title, author, description, url, image_url, category, published_at, content, timestamps
- Migration: database/migrations/2025_11_04_000000_create_articles_table.php


## 5. Business Workflow
1) Fetch articles
- NewsAggregatorService orchestrates multiple providers (NewsApiProvider, GuardianProvider, NytProvider) to fetch batches of articles using the configured API keys.
- Deduplication performed using (external_id + source); existing rows are updated when needed.

2) Store articles
- Persisted to the articles table via the Article model and repository.

3) Expose via API
- GET /api/articles for listing
- GET /api/articles/{id} for details
- POST /api/articles/refresh to fetch from providers on demand
- GET /api/articles/refresh is also available for convenience in this codebase

4) Automatic refresh via scheduler
- Console command news:refresh triggers aggregation.
- Scheduled hourly in app/Console/Kernel.php.


## 6. API Contract
Base URL: use the running server URL (for php artisan serve usually http://127.0.0.1:8000). All endpoints are prefixed with /api.

- List Articles
  - Method: GET
  - URL: {BASE_URL}/api/articles
  - Query params:
    - page: integer (default 1)
    - per_page: integer (default 10, max 100)
    - search: string (optional; matches on title/description/author)
    - sort_by: string (optional; e.g., published_at)
    - sort_dir: string (optional; asc|desc, default desc)
    - Optional filters (if provided): source, category, author, from, to
  - Response: 200 OK
    - JSON structure:
      {
        "data": [ArticleResource...],
        "meta": { "total": int, "per_page": int, "current_page": int, "last_page": int }
      }

- Show Article
  - Method: GET
  - URL: {BASE_URL}/api/articles/{id}
  - Response: 200 OK, JSON ArticleResource
  - Errors: 404 if not found

- Refresh Articles (On-demand)
  - Method: POST (preferred) or GET
  - URL: {BASE_URL}/api/articles/refresh
  - Body (JSON or form) or query string:
    - q: string (optional)
    - from: ISO date (optional)
    - to: ISO date (optional)
    - category: string (optional)
  - Response: 200 OK, JSON { "updated": <int> }
  - Side effects: Fetches from external providers and stores/updates articles.

Notes:
- Authentication is not enforced by default. If added later, include Authorization headers accordingly.
- Rate limits depend on provider API quotas; errors will surface as 4xx/5xx from providers.


## 7. Scheduler (Cron) Setup
The application schedules a refresh every hour.

- Defined in app/Console/Kernel.php:
  - $schedule->command('news:refresh')->hourly();

To activate on a server/machine with cron:
- Add a crontab entry that runs Laravel’s scheduler every minute:
 - `* * * * * cd /Users/jigneshpatel/Herd/innoscripta && php artisan schedule:run >> /dev/null 2>&1`

Manual trigger:
- php artisan news:refresh [--q=...] [--from=YYYY-MM-DD] [--to=YYYY-MM-DD] [--category=...]


## 8. Environment Variables
- APP_URL=http://localhost (or the host used by your web server)
- Database settings (DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- Provider API keys
  - NEWSAPI_KEY
  - GUARDIAN_KEY
  - NYT_KEY

After changing .env, run:
- php artisan optimize:clear


## 9. Troubleshooting
- 404 Not Found on API
  - Ensure routes/api.php contains the three endpoints under the /api prefix (Laravel handles prefixing automatically in RouteServiceProvider).
  - Clear routes: php artisan route:clear
  - Verify server URL/port from php artisan serve output

- Missing/invalid API keys
  - Ensure keys are present in .env and config/services.php
  - php artisan config:clear

- Database errors
  - Ensure MySQL is running and credentials are correct
  - Run php artisan migrate
  - For SQLite tests ensure database/database.sqlite exists and DB_CONNECTION=sqlite for testing


## 10. Testing
- Feature tests located in tests/Feature/ArticleApiTest.php
- Run all tests: php artisan test


## 11. Deliverables
- Source code with this README
- API summary above; you can generate a Postman/Insomnia collection with the endpoints:
  - GET {BASE_URL}/api/articles
  - GET {BASE_URL}/api/articles/{id}
  - POST {BASE_URL}/api/articles/refresh


## 12. Security & Notes
- Do not commit .env with real keys/passwords.
- Network calls rely on provider uptime and quotas; consider retries/backoff and error handling in production.
- Consider caching and pagination tuning for large datasets.

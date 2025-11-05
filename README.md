# Innoscripta News Aggregator API

A Laravel-based API that aggregates articles from multiple providers (NewsAPI, The Guardian, NYTimes), stores them in a MySQL database, and exposes endpoints to query and refresh articles. An hourly scheduler automatically refreshes articles.


## 1. Tech Stack
- PHP 8.2+
- Laravel 11
- MySQL 8 (or compatible)
- HTTP Client: Laravel Http (Guzzle)


## 2. Local Setup
1) Clone and install dependencies
- composer install

2) Configure environment
- Copy .env.example to .env (if not present)
- Update DB connection to MySQL
  - DB_CONNECTION=mysql
  - DB_HOST=127.0.0.1
  - DB_PORT=3306
  - DB_DATABASE=innoscripta
  - DB_USERNAME=root
  - DB_PASSWORD=your_password
- Add API keys
  - NEWSAPI_KEY=your_newsapi_key
  - GUARDIAN_KEY=your_guardian_key
  - NYT_KEY=your_nyt_key

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
- Providers read keys by default from config; constructor injection is supported for testing.


## 4. Data Model
- Article fields: id, source, external_id, title, author, description, url, image_url, category, published_at, content, timestamps
- Migration: database/migrations/2025_11_04_000000_create_articles_table.php


## 5. Business Workflow
1) Fetch articles
- NewsAggregatorService orchestrates multiple providers (NewsApiProvider, GuardianProvider, NytProvider) to fetch batches of articles using the configured API keys.
- Deduplication performed using external_id + source, updating existing articles when needed.

2) Store articles
- Persisted to the articles table via the Article model/repository.

3) Expose via API
- GET /api/articles for listing
- GET /api/articles/{id} for details
- POST /api/articles/refresh to fetch from providers on demand

4) Automatic refresh via scheduler
- Console command news:refresh triggers aggregation.
- Scheduled hourly in app/Console/Kernel.php.


## 6. API Contract (Collection)
Base URL: use the running server URL (for php artisan serve usually http://127.0.0.1:8000). All endpoints are prefixed with /api.

- List Articles
  - Method: GET
  - URL: {BASE_URL}/api/articles
  - Query params:
    - q: string (optional) — search phrase sent to providers for future refreshes, not required for local list
    - from: ISO date (optional)
    - to: ISO date (optional)
    - page: integer (pagination if implemented)
  - Response: 200 OK, JSON array/paginated resource of articles

- Show Article
  - Method: GET
  - URL: {BASE_URL}/api/articles/{id}
  - Response: 200 OK, JSON
  - Errors: 404 if not found

- Refresh Articles (On-demand)
  - Method: POST
  - URL: {BASE_URL}/api/articles/refresh
  - Body (JSON or form):
    - q: string (optional)
    - from: ISO date (optional)
    - to: ISO date (optional)
    - category: string (optional)
  - Response: 200 OK, JSON { stored: <int> }
  - Side effects: Fetches from external providers and stores/updates articles.

Notes:
- Authentication is not enforced by default. If added, include Authorization header accordingly.
- Rate limits depend on provider API quotas.


## 7. Scheduler (Cron) Setup
The application schedules a refresh every hour.

- Defined in app/Console/Kernel.php:
  - $schedule->command('news:refresh')->hourly();

To activate:
- Install a cron that runs Laravel’s scheduler every minute:
  - * * * * * cd /Users/jigneshpatel/Herd/innoscripta && php artisan schedule:run >> /dev/null 2>&1

Manual trigger:
- php artisan news:refresh


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
  - Ensure bootstrap/app.php includes api: __DIR__.'/../routes/api.php' in withRouting()
  - Clear routes: php artisan route:clear
  - Verify server URL/port from php artisan serve output

- Missing/invalid API keys
  - Ensure keys are present in .env and config/services.php
  - php artisan config:clear

- Database errors
  - Ensure MySQL is running and credentials are correct
  - Run php artisan migrate


## 10. Testing
- Feature tests located in tests/Feature/ArticleApiTest.php
- Run tests: php artisan test


## 11. Deliverables for Client
- Source code with README (this document)
- API collection summary above; optional Postman/Insomnia export can be created based on the three endpoints:
  - GET {BASE_URL}/api/articles
  - GET {BASE_URL}/api/articles/{id}
  - POST {BASE_URL}/api/articles/refresh


## 12. Security & Notes
- Do not commit .env with real keys/passwords.
- Network calls rely on provider uptime and quotas; implement retries/backoff if needed.
- Consider caching and pagination for large datasets.

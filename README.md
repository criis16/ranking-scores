# Ranking Scores

[![Tests](https://github.com/criis16/ranking-scores/actions/workflows/tests.yml/badge.svg)](https://github.com/criis16/ranking-scores/actions/workflows/tests.yml)

REST API for accumulating user scores and querying a ranking, built with a **hexagonal / DDD** architecture on **Laravel 13** and **PostgreSQL**, with no ORM (direct database access through PDO).

## Stack

| | |
|---|---|
| Framework | Laravel 13 · PHP 8.5 in Docker |
| Database | PostgreSQL 18 |
| Tests | PHPUnit 12 |
| Style | Laravel Pint |
| API contract | OpenAPI 3.1 → [`docs/openapi.yaml`](docs/openapi.yaml) |

## How it works

### Endpoints

| Method | Route | Description |
|---|---|---|
| `POST` | `/user/{userId}/score` | Adds a score to the user (creates it if it does not exist) |
| `GET` | `/ranking?type=top{N}` | The top `N` users by score descending |
| `GET` | `/ranking?type=at{P}/{M}` | Window of positions around `P` with range `M` |

**`POST /user/{userId}/score`** — the body must contain **exactly one** of the fields:

- `{"total": 150}` → **absolute** score: added to whatever the user already has.
- `{"score": "+10"}` → **relative** score: the sign indicates the operation (`+10` adds, `-30` subtracts).

Scores accumulate per user and **never go below `0`** (the result is clamped when accumulating).

**`GET /ranking`** — the `type` parameter is case-insensitive (`Top30`, `At2/1` also work):

- `top{N}` — e.g. `top30`: the first 30 users ordered by score descending.
- `at{P}/{M}` — e.g. `at2/1`: position 2 ± 1 → positions 1 to 3 (1-based positions, the window is clamped at the edges).

The full contract (schemas, status codes and examples) is in [`docs/openapi.yaml`](docs/openapi.yaml).

### Architecture

The code lives in `src/app/`, organized into three modules with layer separation:

- **`Shared/`** — generic reusable pieces: value objects (`IntValueObject`, `StringValueObject`, `UuidValueObject`), the **Criteria** query abstraction (`Criteria`, `Filters`, `Order`…) and its SQL translator (`PdoCriteriaConverter`).
- **`Score/`** — the score domain: `Score`, `ScorePoints`, `AbsoluteScore`, `RelativeScore` (with `RelativeScoreOperation`) and `ScoreType`.
- **`User/`** — the user context:
  - `Domain/` — `User`, `UserId` and the `UserRepository` **interface**: the domain does not know PostgreSQL exists.
  - `Application/` — the use cases: `NewUserUseCase` (save a score) and `Ranking/GetRankingUseCase` (compute the ranking), with their DTOs. The ranking lives here because it is a query/projection over users, not its own subdomain.
  - `Controllers/` — HTTP adapters: `NewUserController`, `RankingController` and their validating `FormRequest`s.
  - `Infrastructure/Persistence/` — `PgsqlUserRepository`, the implementation with **plain PDO**.

Request flow for each endpoint:

```
POST /user/{id}/score                          GET /ranking?type=...
        │                                              │
        ▼                                              ▼
AddScoreRequest (validates)                  RankingRequest (validates the pattern)
        │                                              │
        ▼                                              ▼
NewUserUseCase                                 GetRankingUseCase
  ├─ finds or creates the user                 ├─ translates top/at into offset + limit
  └─ applies AbsoluteScore | RelativeScore     └─ builds a Criteria (ORDER BY score DESC)
        │                                              │
        ▼                                              ▼
UserRepository (interface in Domain)  ◄──── PgsqlUserRepository (PDO)
                                                   │
                                                   ▼
                                    PdoCriteriaConverter → parametrized SQL
                                     (WHERE · ORDER BY · LIMIT · OFFSET)
```

Persistence uses the project's own **`app_users`** table (`id` uuid, `score` integer), not Laravel's `users`. No repository writes SQL by hand: queries are expressed as `Criteria` and the converter translates them into parametrized SQL.

## Getting started

**Requirements:** Docker with Compose (and PHP + Composer if you want to run the tests locally).

```sh
git clone <repo-url> ranking-scores
cd ranking-scores

# 1. Environment
cp src/.env.example src/.env

# 2. Dependencies and application key
cd src
composer install
php artisan key:generate
cd ..

# 3. Boot: builds the image (PHP 8.5 + pdo_pgsql), starts PostgreSQL 18,
#    runs the migrations and serves the API
docker compose up --build -d
```

The API is available at **http://localhost:8000**. The `db` service (PostgreSQL 18) exposes port `5432` with the `laravel`/`secret` user/password and the `laravel` database.

If you do not have PHP locally, step 2 can also be done inside the container:

```sh
docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
```

Check that everything works:

```sh
curl 'http://localhost:8000/ranking?type=top30'
```

Stop:

```sh
docker compose down        # add -v to also delete the data
docker compose logs -f app # tail the API logs
```

## Tests

Tests run on the **host** against PostgreSQL (through port `5432`, which `compose.yaml` exposes), so you need PHP ≥ 8.3 with the `pdo_pgsql` extension.

**Once**, create the test database (it is the one configured in `phpunit.xml`; it is created empty and the tests set up the schema themselves):

```sh
docker compose up -d db
docker compose exec db psql -U laravel -d postgres \
  -c "CREATE DATABASE ranking_scores_test;"
```

Run the full suite (exactly the same command CI uses):

```sh
cd src
vendor/bin/phpunit --testsuite Shared,Score,User
```

| Suite | Contents |
|---|---|
| `Shared` | Value Objects, Criteria and `PdoCriteriaConverter` |
| `Score` | Score domain (absolute and relative) |
| `User` | Use cases, HTTP controllers, repository on real PostgreSQL and an e2e test of the ranking endpoint |

Notes:

- Domain tests use **in-memory doubles** (no mocks); infrastructure tests use real PostgreSQL inside a transaction that is rolled back after every test.
- The Laravel skeleton's `Feature` and `Unit` suites are **not** part of CI.
- On every PR, GitHub Actions (`.github/workflows/tests.yml`) spins up PostgreSQL 18 as a *service* and runs the same command.

Code style:

```sh
cd src
vendor/bin/pint            # format
vendor/bin/pint --test     # check only
```

## Repository structure

```
ranking-scores/
├── compose.yaml              # Services: app (PHP 8.5) + db (PostgreSQL 18)
├── docker/Dockerfile         # App image with pdo_pgsql
├── docs/openapi.yaml         # OpenAPI 3.1 contract of the API
├── .github/workflows/        # CI: Shared,Score,User suite
└── src/                      # Laravel application
    ├── app/
    │   ├── Shared/           # Value Objects + Criteria + PdoCriteriaConverter
    │   ├── Score/            # Score domain
    │   ├── User/
    │   │   ├── Domain/           # User, UserId, UserRepository (interface)
    │   │   ├── Application/      # NewUserUseCase, Ranking/GetRankingUseCase
    │   │   ├── Controllers/      # NewUserController, RankingController + requests
    │   │   └── Infrastructure/   # PgsqlUserRepository (PDO)
    │   ├── Models/           # Laravel skeleton (unused by the domain)
    │   └── Providers/        # Binding registration (PDO and repositories)
    ├── routes/web.php        # POST /user/{userId}/score · GET /ranking
    ├── database/migrations/  # + create_app_users_table
    └── tests/
        ├── Shared/ Score/ User/   # CI suites
        └── Feature/ Unit/         # Laravel skeleton (not in CI)
```

# API Formularza Kontaktowego

API do obsługi formularza kontaktowego - Symfony 7 + PostgreSQL.

## Jak postawić

### Z Dockerem

```bash
# Klonowanie repozytorium
git clone https://github.com/iud/h2h.git
cd h2h

# Uruchomienie kontenerów
docker compose up -d

# Instalacja zależności
docker compose run --rm php composer install

# Utworzenie bazy i wykonanie migracji
docker compose run --rm php php bin/console doctrine:database:create --if-not-exists
docker compose run --rm php php bin/console doctrine:migrations:migrate --no-interaction

# Uruchomienie serwera
docker compose run --rm -p 8000:8000 php php -S 0.0.0.0:8000 -t public
```

Gotowe, API działa na `http://localhost:8000`

### Bez Dockera (lokalnie)

Wymagania:
- PHP 8.2+ z pdo_pgsql
- Composer
- PostgreSQL 16+

```bash
# Klonowanie repozytorium
git clone https://github.com/iud/h2h.git
cd h2h

# Instalacja zależności
composer install

# Konfiguracja bazy w .env.local
DATABASE_URL="postgresql://user:pass@127.0.0.1:5432/contact_form_db?serverVersion=16&charset=utf8"

# Utworzenie bazy i wykonanie migracji
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction

# Uruchomienie serwera
php -S localhost:8000 -t public
```

## API

Dwa endpointy - zapis wiadomości i lista wiadomości. Wszystko w JSON.

### POST /api/contact

Zapis wiadomości. Wymagane pola:
- `fullName` - imię i nazwisko (min 2, max 255 znaków)
- `email` - adres email (poprawny format)
- `message` - treść wiadomości (min 10 znaków)
- `consent` - zgoda na RODO (musi być `true`)

Przykład requestu:
```json
{
  "fullName": "Jan Kowalski",
  "email": "jan@example.com",
  "message": "Treść wiadomości",
  "consent": true
}
```

Sukces (201):
```json
{
  "id": 1,
  "fullName": "Jan Kowalski",
  "email": "jan@example.com",
  "message": "Treść wiadomości",
  "createdAt": "2025-12-17T10:30:00+00:00"
}
```

Błąd walidacji (422) - zwraca listę błędów w formacie Symfony.

### GET /api/contact

Pobiera listę wiadomości z paginacją (najnowsze pierwsze).

Query params:
- `page` - numer strony (domyślnie 1, min 1)
- `limit` - ile na stronę (domyślnie 20, max 100)

Odpowiedź:
```json
{
  "items": [],
  "meta": {
    "page": 1,
    "limit": 20,
    "total": 42,
    "pages": 3
  }
}
```

## Dokumentacja Swagger

Pełna dokumentacja API dostępna pod `http://localhost:8000/api/doc`.

## Testy

Dwa typy testów:
- **PHPUnit** - testy jednostkowe (warstwa aplikacji, encje)
- **Codeception** - testy integracyjne (API endpoints)

### Z Dockerem

```bash
# Przygotowanie bazy testowej
docker compose run --rm php php bin/console doctrine:database:create --env=test --if-not-exists
docker compose run --rm php php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Uruchomienie testów PHPUnit
docker compose run --rm php php bin/phpunit

# Uruchomienie testów Codeception
docker compose run --rm php vendor/bin/codecept run
```

### Lokalnie

```bash
php bin/phpunit
vendor/bin/codecept run
```

Testy pokrywają: zapis wiadomości, wszystkie przypadki walidacji (brak pól, niepoprawne wartości), listę wiadomości, paginację.

## Struktura

Standardowa struktura Symfony:
- `src/Controller/` - kontrolery API
- `src/Entity/` - encje Doctrine
- `src/DTO/` - obiekty transferu danych z walidacją
- `src/Application/` - logika biznesowa
- `src/Repository/` - repozytoria
- `tests/` - testy (PHPUnit + Codeception)
- `migrations/` - migracje bazy danych

## Co zostało zrobione

- Kod przeleciany przez **phpcs** (PSR-12) i **phpstan**
- Testy jednostkowe (PHPUnit) + testy integracyjne (Codeception)
- Warstwa aplikacji (`ContactMessageCreator`) - oddzielenie logiki od kontrolera, ale to też taka atrapa bardziej niż real use
- DTO z walidacją Symfony
- Docker setup - ekstremalnie lekki, tylko do testowania
- Dokumentacja Swagger/OpenAPI

## Co można poprawić

### 1. Refaktoryzacja dokumentacji OpenAPI
Atrybuty OpenAPI w kontrolerze mogą go zaśmiecać. Można przenieść do osobnych klas lub użyć YAML.

### 2. Paginacja - KnpPaginatorBundle
Zamiast ręcznej paginacji można użyć [KnpPaginatorBundle](https://github.com/KnpLabs/KnpPaginatorBundle) - mniej kodu, więcej funkcji (sortowanie, filtry).

### 3. API Platform
Dla prostego CRUD można było użyć API Platform. Ale to też mógł być overkill.

### 4. Messenger / Command Pattern
Zamiast bezpośredniego wywołania serwisu, kontroler mógłby rzucać komendę przez Symfony Messenger. 

### 5. Testy setterów
Testy setterów są zbędne, ale to zadanie rekrutacyjne, to pokazuję, że umiem ;)

### 6. Env.dev zacommitowany umyslnie wyłącznie na potrzeby zadania rekru


# Booker - System Rezerwacji

Kompleksowy system rezerwacji oparty na Symfony, umożliwiający firmom zarządzanie usługami, pracownikami i rezerwacjami klientów.

## Technologie

- **PHP 8.4+** (ext-ctype, ext-iconv, pdo_sqlite)
- **Symfony 7.x**
- **SQLite 3** (baza danych w pliku `var/data/booker.db`)
- **Doctrine ORM**
- **Twig** (szablony)

## Instalacja

### 1. Sklonuj repozytorium
```bash
git clone https://github.com/zuukov/projekt-symfony-booker.git
cd projekt-symfony-booker
```

### 2. Zainstaluj zależności
```bash
composer install
```

### 3. Uruchom serwer deweloperski
```bash
symfony server:start
```


Aplikacja będzie dostępna pod adresem: `http://localhost:8000`

## Funkcjonalności

- **Zarządzanie firmami** - tworzenie profili biznesowych z godzinami pracy
- **Usługi** - definiowanie usług z cenami, czasem trwania i zdjęciami
- **Personel** - zarządzanie pracownikami, ich umiejętnościami i dostępnością
- **Rezerwacje** - system bookingu z wyborem usługi, pracownika i terminu
- **Opinie** - recenzje klientów z ocenami gwiazdkowymi
- **Wyszukiwanie** - wyszukiwanie firm po kategorii, lokalizacji i nazwie
- **Geokodowanie** - automatyczna mapa z lokalizacją firmy (OpenStreetMap)
- **Zasady bezpieczeństwa i udogodnienia** - 40 predefiniowanych opcji dla firm
- **Role użytkowników** - admin, właściciel firmy, klient


## Baza danych

Projekt używa **SQLite** - baza jest przechowywana w pliku `var/data/booker.db`

### Główne tabele:
- `user` - użytkownicy systemu
- `business` - firmy
- `service` - usługi oferowane przez firmy
- `staff` - pracownicy
- `booking` - rezerwacje
- `review` - opinie klientów
- `service_category` - kategorie usług
- `business_working_hours` - godziny pracy firm
- `staff_service` - powiązania pracownik-usługa
- `staff_working_hours` - godziny pracy pracowników
- `staff_time_off` - urlopy pracowników

## Komendy pomocnicze

```bash
# Wyczyść cache
php bin/console cache:clear

# Lista wszystkich tras
php bin/console debug:router

# Status migracji
php bin/console doctrine:migrations:status

# Wykonaj migracje
php bin/console doctrine:migrations:migrate

# Załaduj fixtures ponownie (usuwa dane!)
php bin/console doctrine:fixtures:load

# Sprawdź schemat bazy
php bin/console doctrine:schema:validate
```

## Rozwiązywanie problemów

### Baza danych nie istnieje
```bash
php bin/console doctrine:schema:create
```

### Błędy uprawnień do pliku SQLite
```bash
chmod -R 777 var/
```

### Cache Symfony
```bash
php bin/console cache:clear
chmod -R 777 var/cache var/log
```

### Port 8000 zajęty
```bash
# Użyj innego portu
php -S localhost:8080 -t public/
```

## Środowiska

- **Deweloperskie**: `.env.local` (nie wrzucane do repozytorium)
- **Produkcyjne**: ustaw `APP_ENV=prod` w `.env`

Baza danych SQLite jest wersjonowana w repozytorium (`var/data/booker.db`), więc przykładowe dane są dostępne od razu po klonowaniu.

## Licencja

Projekt edukacyjny - użyj zgodnie z własnymi potrzebami.



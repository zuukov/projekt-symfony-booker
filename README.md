# Symfony Booking System

A comprehensive booking system built with Symfony that allows businesses to manage services, staff, and customer bookings.

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.4 or higher** with required extensions:
  - `ext-ctype`
  - `ext-iconv`
  - `pdo_mysql` (for MySQL support)
- **Composer** (PHP dependency manager)
- **MySQL 8.0 or higher**
- **Git**

## Database Setup

### 1. Install MySQL

#### Ubuntu/Debian:
```bash
sudo apt update
sudo apt install mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql
```

#### macOS (with Homebrew):
```bash
brew install mysql
brew services start mysql
```

#### Windows:
Download and install MySQL from the [official website](https://dev.mysql.com/downloads/mysql/).

### 2. Create Database and User

Connect to MySQL as root:
```bash
mysql -u root -p
```

Create the database and user:
```sql
-- Create database
CREATE DATABASE booker_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'booker_user'@'localhost' IDENTIFIED BY 'secret123';

-- Grant privileges
GRANT ALL PRIVILEGES ON booker_db.* TO 'booker_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Exit MySQL
EXIT;
```

Verify the setup:
```bash
mysql -u booker_user -p'secret123' booker_db -e "SELECT 'Database connection successful' as status;"
```

## Project Setup

### 1. Clone the Repository
```bash
git clone https://github.com/zuukov/projekt-symfony-booker.git
cd projekt-symfony-booker
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Environment Configuration

Copy the environment file:
```bash
cp .env .env.local
```

The `.env` file is already configured for MySQL with:
- **Database**: `booker_db`
- **User**: `booker_user`
- **Password**: `secret123`
- **Host**: `127.0.0.1:3306`

### 4. Create Database Schema

Run the Doctrine migrations to create all tables:
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

You should see output similar to:
```
[notice] Migrating up to DoctrineMigrations\Version20251214184200
[notice] finished in 123ms, used 12M memory, 1 migrations executed, 11 sql queries
```

## Database Structure

The migration creates the following tables:

### Core Tables:
- **`user`** - System users with roles (admin, business_owner, user)
- **`business`** - Business information and settings
- **`service_category`** - Service categories for organization
- **`service`** - Individual services offered by businesses
- **`staff`** - Staff members working for businesses

### Relationship Tables:
- **`staff_service`** - Many-to-many relationship between staff and services
- **`booking`** - Customer bookings with status tracking
- **`review`** - Customer reviews for businesses

### Schedule Tables:
- **`business_working_hours`** - Business operating hours (7 days)
- **`staff_working_hours`** - Individual staff working hours
- **`staff_time_off`** - Staff vacation and time off records

## Verification Steps

### 1. Check Migration Status
```bash
php bin/console doctrine:migrations:status
```

Should show:
```
+----------------------+--------+---------------------------------------+
| Migration            | Status | Description                           |
+----------------------+--------+---------------------------------------+
| 20251214184200       | up     | Create all database tables...         |
+----------------------+--------+---------------------------------------+
```

### 2. Verify Tables Exist
```bash
php bin/console doctrine:query:sql "SHOW TABLES;"
```

Or directly in MySQL:
```bash
mysql -u booker_user -p'secret123' booker_db -e "SHOW TABLES;"
```

Expected output:
```
+-------------------+
| Tables_in_booker_db |
+-------------------+
| business          |
| booking           |
| business_working_hours |
| review            |
| service           |
| service_category  |
| staff             |
| staff_service     |
| staff_time_off    |
| staff_working_hours |
| user              |
+-------------------+
```

### 3. Check Table Structure
Example - check user table:
```bash
php bin/console doctrine:query:sql "DESCRIBE user;"
```

## Development Server

Start the Symfony development server:
```bash
php bin/console cache:clear
php bin/console debug:router
symfony server:start
```

Or if you have the Symfony CLI:
```bash
symfony serve
```

The application will be available at `http://localhost:8000`

## Troubleshooting

### Common Issues:

#### 1. PHP Not Found
Ensure PHP is installed and in your PATH:
```bash
php --version
```

#### 2. MySQL Connection Issues
- Verify MySQL is running: `sudo systemctl status mysql`
- Check credentials in `.env` file
- Test connection: `mysql -u booker_user -p'secret123' booker_db`

#### 3. Migration Errors
If migrations fail, you might need to:
```bash
# Drop and recreate database
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

#### 4. Permission Issues
Ensure your user has proper permissions:
```sql
GRANT ALL PRIVILEGES ON booker_db.* TO 'booker_user'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
```

## Project Structure

```
src/
├── Controller/          # HTTP controllers
├── Entity/             # Doctrine entities
├── Repository/         # Doctrine repositories
├── Kernel.php          # Symfony kernel
config/                 # Configuration files
migrations/             # Database migrations
templates/              # Twig templates
tests/                  # Test files
```

## API Endpoints

The system provides RESTful API endpoints for:
- User management
- Business operations
- Service management
- Booking system
- Staff scheduling
- Reviews and ratings

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `php bin/phpunit`
5. Submit a pull request

## License

This project is proprietary software.

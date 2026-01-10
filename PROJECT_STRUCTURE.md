# Booker - Booking System Project Structure

## Overview
Symfony-based booking system (similar to Booksy) for managing business appointments, services, staff, and customer bookings.

## Technology Stack
- **Framework**: Symfony 8.0
- **PHP**: 8.4+
- **Database**: MySQL 8.0.32 (booker_db)
- **CSS**: Tailwind CSS (migrated from Bootstrap)
- **JavaScript**: FullCalendar.js 6.1.10 for calendar views
- **Time Convention**: 24-hour format, Polish weekday (0=Monday, 6=Sunday)

---

## Database Schema

### Core Entities

**User**
- Authentication & roles (USER, BUSINESS_OWNER, ADMIN)
- firstName, lastName, email, password

**Business**
- Owned by User (BUSINESS_OWNER role)
- businessName, formalBusinessName, description
- address, city, postalCode
- phone, secondaryPhone, email
- instagramUrl, facebookUrl, websiteUrl, logoUrl
- **specialNote** (200 chars) - for holidays/special closures

**Service**
- Belongs to Business
- name, description, category (ServiceCategory)
- durationMinutes (1-480), price, isActive
- featuredImage URL

**Staff**
- Belongs to Business
- name, surname, aboutMe, experience, school
- avatarImage URL

**StaffService** (Junction Table)
- Links Staff ↔ Service (many-to-many)
- Determines which staff can provide which services

**StaffWorkingHours**
- Staff availability per weekday
- weekday (0=Mon, 6=Sun), startsAt (time), endsAt (time)
- Multiple entries per staff (e.g., Mon 9-12, Mon 14-17)

**StaffTimeOff**
- Staff holidays/absences
- startsAt (datetime), endsAt (datetime), reason

**BusinessWorkingHours**
- Business opening hours per weekday
- weekday, opensAt, closesAt
- **Note**: Currently has entity but no UI (future enhancement)

**Booking**
- Links Business, Service, Staff, User
- startsAt, endsAt (datetime)
- priceAtBooking (captured at booking time)
- status: PENDING → CONFIRMED → COMPLETED or CANCELLED

**ServiceCategory**
- categoryFullName, categoryFriendlyName
- featuredImage

**Review**
- Links Booking (one-to-one)
- rating, comment, createdAt
- **Note**: Entity exists, no UI yet

---

## Application Flow

### 1. User Registration & Login
- Route: `/rejestracja`, `/logowanie`
- Users can register as USER or BUSINESS_OWNER
- LoginSuccessHandler redirects based on role:
  - BUSINESS_OWNER → `/owner` (owner dashboard)
  - USER → `/` (homepage)

### 2. Business Owner Workflow

#### A. Business Management
- **Dashboard**: `/owner` - List all owned businesses
- **Create Business**: `/owner/business/create`
  - Basic info, contact details, social media
  - Special note for holidays
- **Edit Business**: `/owner/business/{id}/edit`

#### B. Service Management
- **List Services**: `/wlasciciel/biznes/{id}/uslugi`
- **Create Service**: `/wlasciciel/biznes/{id}/uslugi/nowa`
  - Name, description, category, duration, price, image
- **Edit/Delete Service**: Protection against deletion if bookings exist

#### C. Staff Management
- **List Staff**: `/owner/business/{id}/staff`
- **Add Staff**: `/owner/business/{id}/staff/create`
- **Edit Staff**: `/owner/business/{businessId}/staff/{staffId}/edit`
- **Assign Services**: `/wlasciciel/biznes/{id}/pracownik/{staffId}/uslugi`
  - Checkboxes for all business services
- **Working Hours**: `/wlasciciel/biznes/{id}/pracownik/{staffId}/grafik`
  - Add multiple time blocks per weekday
- **Time Off**: `/wlasciciel/biznes/{id}/pracownik/{staffId}/urlopy`
  - Add absences with date range

#### D. Booking Management
- **View Bookings**: `/wlasciciel/biznes/{id}/rezerwacje`
- **Actions**: Confirm, Complete, Cancel bookings
- **Calendar View**: `/wlasciciel/biznes/{id}/kalendarz`
  - FullCalendar.js with month/week/day views
  - Color-coded by status
  - JSON data: `/wlasciciel/biznes/{id}/kalendarz/dane`

### 3. User (Customer) Workflow

#### A. Search & Discovery
- **Search**: `/szukaj?q={query}` → redirects to `/szukaj/{term}`
- Searches across:
  - Businesses (name, description, city)
  - Services (name, description, category)
  - Staff (name, surname, aboutMe)
- Results grouped by type with links to booking

#### B. Booking Process
1. **Select Service**: `/rezerwacja/biznes/{businessId}/usluga/{serviceId}`
   - Shows staff who can provide this service
2. **Select Date/Time**: `/rezerwacja/biznes/{businessId}/usluga/{serviceId}/pracownik/{staffId}`
   - Date picker → AJAX loads available slots
   - API: `/api/rezerwacja/sloty?staffId=X&serviceId=Y&date=YYYY-MM-DD`
3. **Create Booking**: POST `/rezerwacja/utworz`
   - Validates availability, creates booking as PENDING
4. **Confirmation**: `/rezerwacja/{id}/potwierdzenie`
   - Shows booking details and success message

#### C. Booking Management
- **My Bookings**: `/uzytkownik/rezerwacje`
  - Tabs: Upcoming / Past
  - **Cancel Rule**: Only if >2 hours before start time
  - Otherwise: Shows business phone for contact
- **Calendar View**: `/uzytkownik/kalendarz`
  - Personal booking calendar
  - JSON data: `/uzytkownik/kalendarz/dane`

---

## Service Layer

### AvailabilityService
**Purpose**: Calculate available time slots for bookings

**Key Method**: `getAvailableSlots(Staff $staff, Service $service, \DateTime $date): array`

**Logic**:
1. Get weekday (0=Monday via `date('N') - 1`)
2. Query StaffWorkingHours for that weekday
3. Check if staff has time-off on that date
4. Generate 15-minute slots within working hours
5. Filter out slots that conflict with existing bookings
6. Return available \DateTime objects

### BookingService
**Purpose**: Booking CRUD with business logic

**Methods**:
- `createBooking()` - Validates staff offers service, slot available, time is future
- `confirmBooking()` - PENDING → CONFIRMED
- `completeBooking()` - CONFIRMED → COMPLETED
- `cancelBooking()` - Any → CANCELLED

### SearchService
**Purpose**: Real-time search across entities

**Method**: `search(string $query): array`

**Returns**:
```php
[
    'businesses' => [...],  // Array of Business entities
    'services' => [...],    // Array of Service entities
    'staff' => [...],       // Array of Staff entities
    'total' => int
]
```

**Search Strategy**: Case-insensitive LIKE queries on multiple fields, max 20 results per type

---

## Repository Extensions

### BookingRepository
- `findByStaffAndDateRange()` - Bookings in date range
- `findOverlappingBookings()` - Conflict detection
- `findUpcomingByUser()` - User's future bookings
- `findPastByUser()` - User's past bookings
- `findByBusiness()` - All business bookings

### StaffWorkingHoursRepository
- `findByStaffAndWeekday()` - Get staff hours for specific day

### StaffTimeOffRepository
- `findByStaffAndDate()` - Check time-off in date range
- `findUpcomingByStaff()` - Future absences

### StaffServiceRepository
- `findServicesByStaff()` - Services assigned to staff

---

## Form Types

### BusinessFormType
- All business fields with validation
- Special note field (200 chars max)
- BusinessName disabled when editing (read-only)

### ServiceFormType
- Service fields with validation
- Duration: 1-480 minutes
- Price: >= 0
- isActive checkbox

### StaffFormType
- Staff personal info
- About me, experience, school, avatar

### StaffWorkingHoursFormType
- Weekday dropdown (0=Poniedziałek, 6=Niedziela)
- Time fields (24h format)
- Validation: endsAt > startsAt

### StaffTimeOffFormType
- DateTime range
- Optional reason
- Validation: endsAt > startsAt, dates >= today

---

## Controllers

### SecurityController
- Login, logout, registration
- Role-based redirects via LoginSuccessHandler

### HomeController
- Homepage, service pages

### SearchController
- Search query processing
- Results display (uses SearchService)

### OwnerController
- Business CRUD
- Staff CRUD
- Service CRUD
- StaffService assignment
- Working hours & time-off management
- Booking management (view, confirm, complete, cancel)
- Calendar view & data endpoint

### BookingController
- User booking flow (select staff → select datetime → create)
- Available slots API (AJAX)
- User bookings list
- Booking cancellation (2-hour rule)
- User calendar view & data endpoint

---

## Key Features

### 1. Service CRUD
- Full management for business owners
- Cannot delete services with existing bookings
- Featured image support
- Active/inactive toggle

### 2. Business Special Note
- 200-character field
- For holidays/special closures
- Character counter in UI
- Example: "Zamknięte 24-26 grudnia"

### 3. Real Search
- Multi-entity search (businesses, services, staff)
- Case-insensitive, partial matching
- Grouped results with direct booking links

### 4. 2-Hour Cancellation Rule
- Users can cancel bookings only if >2 hours before start
- Backend validation + frontend UI check
- Shows business phone if too late to cancel

### 5. Calendar Views (FullCalendar.js)
- **Owner Calendar**: All business bookings
- **User Calendar**: Personal bookings
- Color-coded by status:
  - 🟡 Yellow: PENDING
  - 🟢 Green: CONFIRMED
  - 🔵 Blue: COMPLETED
  - 🔴 Red: CANCELLED
- Month/Week/Day views
- Polish locale, starts Monday

### 6. Availability Algorithm
- Respects staff working hours (multiple blocks per day)
- Excludes staff time-off
- Prevents double-booking
- 15-minute slot intervals
- No buffer time (back-to-back bookings allowed)

---

## Authorization

### Roles
- `ROLE_USER` - Can book services, view own bookings
- `ROLE_BUSINESS_OWNER` - Can manage businesses, staff, services, bookings
- `ROLE_ADMIN` - Not yet implemented

### Security Rules
- Business owners can only manage their own businesses
- Users can only view/cancel their own bookings
- Staff/Service assignment: Verify business ownership
- Booking actions: Verify ownership (owner or customer)

---

## API Endpoints

### AJAX/JSON Endpoints
- `GET /api/rezerwacja/sloty` - Available booking slots
  - Params: staffId, serviceId, date
  - Returns: `{success: bool, slots: [{time, datetime}]}`

- `GET /wlasciciel/biznes/{id}/kalendarz/dane` - Owner calendar events
  - Returns: Array of FullCalendar event objects

- `GET /uzytkownik/kalendarz/dane` - User calendar events
  - Returns: Array of FullCalendar event objects

---

## File Structure

```
src/
├── Controller/
│   ├── BookingController.php      # User booking flow + calendar
│   ├── HomeController.php         # Homepage
│   ├── OwnerController.php        # Business owner panel
│   ├── SearchController.php       # Search functionality
│   └── SecurityController.php     # Auth
├── Entity/
│   ├── Booking.php
│   ├── BookingStatus.php (enum)
│   ├── Business.php
│   ├── BusinessWorkingHours.php
│   ├── Review.php
│   ├── Service.php
│   ├── ServiceCategory.php
│   ├── Staff.php
│   ├── StaffService.php
│   ├── StaffTimeOff.php
│   ├── StaffWorkingHours.php
│   ├── User.php
│   └── UserRole.php (enum)
├── Form/
│   ├── BusinessFormType.php
│   ├── ServiceFormType.php
│   ├── StaffFormType.php
│   ├── StaffTimeOffFormType.php
│   └── StaffWorkingHoursFormType.php
├── Repository/
│   ├── BookingRepository.php
│   ├── BusinessRepository.php
│   ├── ServiceRepository.php
│   ├── StaffRepository.php
│   ├── StaffServiceRepository.php
│   ├── StaffTimeOffRepository.php
│   └── StaffWorkingHoursRepository.php
├── Service/
│   ├── AvailabilityService.php    # Slot calculation
│   ├── BookingService.php         # Booking logic
│   └── SearchService.php          # Multi-entity search
└── Security/
    └── LoginSuccessHandler.php    # Role-based redirects

templates/
├── base.html.twig                 # Base layout
├── booking/                       # User booking flow
│   ├── confirmation.html.twig
│   ├── select_datetime.html.twig
│   └── select_staff.html.twig
├── home/
│   ├── index.html.twig
│   └── service_pages.html.twig
├── owner/                         # Business owner panel
│   ├── bookings.html.twig
│   ├── business_form.html.twig
│   ├── calendar.html.twig         # FullCalendar view
│   ├── dashboard.html.twig
│   ├── service_form.html.twig
│   ├── service_list.html.twig
│   ├── staff_form.html.twig
│   ├── staff_list.html.twig
│   ├── staff_schedule.html.twig
│   ├── staff_services.html.twig
│   └── staff_timeoff.html.twig
├── partials/
│   ├── header.html.twig
│   └── nav-simple.html.twig
├── search/
│   └── results.html.twig          # Multi-entity search results
├── security/
│   ├── login.html.twig
│   └── register.html.twig
└── user/                          # Customer views
    ├── bookings.html.twig
    └── calendar.html.twig         # User's booking calendar
```

---

## Configuration

### Database Connection
```yaml
# .env
DATABASE_URL="mysql://booker_user:secret123@127.0.0.1:3306/booker_db?serverVersion=8.0.32&charset=utf8mb4"
```

### Services (config/services.yaml)
```yaml
App\Repository\:
    resource: '../src/Repository'
    tags: ['doctrine.repository_service']

App\Controller\:
    resource: '../src/Controller'
    autowire: true
    autoconfigure: true

App\Service\:
    resource: '../src/Service'
    autowire: true
    autoconfigure: true
```

---

## Development Workflow

### Starting the Project
```bash
# 1. Install dependencies
composer install

# 2. Run migrations
php bin/console doctrine:migrations:migrate

# 3. Start server
symfony server:start
# OR
php -S localhost:8000 -t public
```

### Creating Migrations
```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### Clearing Cache
```bash
php bin/console cache:clear
```

---

## Future Enhancements

### Planned Features (Not Yet Implemented)
- Business working hours UI (entity exists)
- Service CRUD for categories
- Review system UI (entity exists)
- Email notifications
- SMS reminders
- Payment integration
- Recurring bookings
- Multi-service bookings
- Waitlist feature
- User profile management
- Booking rescheduling (skipped - cancel + rebook instead)
- Dashboard analytics

### Known Limitations
- No buffer time between bookings
- No business-wide working hours enforcement (only staff hours)
- Search has no relevance scoring/sorting
- Calendar events cannot be dragged/resized
- No email confirmations yet

---

## Polish Text Convention
All user-facing text is hardcoded in Polish (not using Symfony translations):
- Buttons: "Dodaj", "Edytuj", "Usuń", "Anuluj"
- Status: "Oczekująca", "Potwierdzona", "Zakończona", "Anulowana"
- Days: "Poniedziałek" through "Niedziela"
- Time format: "09:00 - 17:00" (24h)

---

## Testing URLs

### Business Owner
- Dashboard: http://localhost:8000/owner
- Create business: http://localhost:8000/owner/business/create
- Manage staff: http://localhost:8000/owner/business/{id}/staff
- Services: http://localhost:8000/wlasciciel/biznes/{id}/uslugi
- Calendar: http://localhost:8000/wlasciciel/biznes/{id}/kalendarz
- Bookings: http://localhost:8000/wlasciciel/biznes/{id}/rezerwacje

### User (Customer)
- Homepage: http://localhost:8000/
- Search: http://localhost:8000/szukaj?q=fryzjer
- My bookings: http://localhost:8000/uzytkownik/rezerwacje
- My calendar: http://localhost:8000/uzytkownik/kalendarz
- Booking flow starts from search or service pages

### Auth
- Register: http://localhost:8000/rejestracja
- Login: http://localhost:8000/logowanie

---

## Contact & Support
Project repository: /home/mehow/Developer/booker/projekt-symfony-booker

# Employee Collaboration Tracker

A PHP 8.3 tool designed to identify pairs of employees who have worked together on common projects for the longest total period.

## Prerequisites
- Docker & Docker Compose
- Composer

## Installation & Setup

1. Clone or extract the project files into your directory.

2. Install dependencies via Docker:
```bash
docker-compose run --rm app composer install
```

3. Start the containers:
```bash
docker-compose up -d
```

4. Access the application:
   `http://localhost:8080`

## Unit Testing
To run the PHPUnit test suite:
```bash
docker-compose exec app ./vendor/bin/phpunit tests
```

## Quality Control & Static Analysis

### 1. PHP CodeSniffer (PSR-12 Compliance)
Check for style violations:
```bash
docker-compose exec app ./vendor/bin/phpcs src --standard=PSR12
```

Fix fixable style violations automatically:
```bash
docker-compose exec app ./vendor/bin/phpcbf src --standard=PSR12
```

### 2. PHPStan (Static Analysis)
```bash
docker-compose exec app ./vendor/bin/phpstan analyse src --level=5
```

### 3. Psalm (Static Analysis)
```bash
docker-compose exec app ./vendor/bin/psalm
```

### 4. PHP Mess Detector (Code Quality)
```bash
docker-compose exec app ./vendor/bin/phpmd src text codesize,unusedcode,naming
```

## DateFacade Library Fallback Testing

The application is designed to work with or without the Carbon library.

### To Install Carbon (Active Mode):
```bash
docker-compose exec app composer require nesbot/carbon
```
*The UI will display "Engine: Carbon".*

### To Uninstall Carbon (Fallback Mode):
```bash
docker-compose exec app composer remove nesbot/carbon
```
*The UI will display "Engine: PHP Native". DateFacade will switch to internal DateTimeImmutable logic.*

## CSV File Format
The application expects a CSV file without a header:
`EmployeeID, ProjectID, DateFrom, DateTo`

- DateFrom is mandatory.
- If DateTo is empty or "NULL", the current date is used.
- Supports EU (DD-MM) and US (MM-DD) formats via UI selection.

## Architecture Highlights
- MVC Pattern (Controller, Model, Service layers).
- Dependency Injection for service management.
- PRG (Post-Redirect-Get) pattern for clean form handling.
- Readonly classes for data immutability.

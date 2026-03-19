# Testing TransmitMail

This document describes how to run tests for TransmitMail.

## Overview

TransmitMail includes automated tests to ensure compatibility across multiple PHP versions.

The tests are **Functional/End-to-End (E2E) tests** using **Symfony Panther**, which run in a real browser environment to verify the entire form submission flow.

## Supported PHP Versions

Tests are executed against the following PHP versions:

- PHP 7.2
- PHP 7.3
- PHP 7.4
- PHP 8.0
- PHP 8.1
- PHP 8.2
- PHP 8.3
- PHP 8.4
- PHP 8.5

# Running Tests Locally

TransmitMail uses Docker Compose to manage the multi-version PHP test environment.

## 1. Start the test environment

Example for PHP 8.5:

```bash
docker compose up -d php85
```

This starts the PHP container for the specified version.

## 2. Install dependencies

Dependencies are managed per PHP version within the `environments/` directory.

```bash
docker compose exec php85 composer install
```

*Note: You only need to run this once or when `composer.json` changes.*

## 3. Run tests

Execute the tests using PHPUnit from within the container. Since the vendor directory is environment-specific, use the following command:

```bash
docker compose exec php85 environments/php85/vendor/bin/phpunit
```

Alternatively, you can use `composer exec`:

```bash
docker compose exec php85 composer exec phpunit
```

### Running specific tests

To run a specific test file or filter by test name:

```bash
docker compose exec php85 environments/php85/vendor/bin/phpunit tests/FunctionalTest/BasicTest.php
docker compose exec php85 environments/php85/vendor/bin/phpunit --filter testTitle
```

## What is being tested?

The E2E tests verify the following:

- Form display and default values
- Validation rules (required fields, email format, character types, etc.)
- Confirmation page display and "back" functionality
- Error handling and error messages
- CSRF protection
- Template syntax processing

## Test Directory Structure

```text
tests/
  FunctionalTest/
    BasicTest.php
    CsrfTest.php
    GetParameterTest.php
    InputOptionsTest.php
    TransmitMailPantherTestCase.php (Base class)
```

# Continuous Integration

Tests are automatically executed on every push using **CircleCI**.

CI verifies:
- All supported PHP versions (7.2 - 8.5)
- Successful execution of all E2E tests

If CI fails, the release should not be created.

# Adding New Tests

When adding a feature or fixing a bug:

1. Add a test case in `tests/FunctionalTest/` that reproduces the issue.
2. Confirm the test fails.
3. Fix the implementation.
4. Confirm the test passes across relevant PHP versions.

# Tips & Troubleshooting

## Cleanup

If tests fail due to stale cache or temporary files, clean the test artifacts:

```bash
rm -rf tmp/tests/*
```

## Debugging

- Use the `--debug` flag for verbose output:
  ```bash
  vendor/bin/phpunit --debug
  ```
- **Screenshots on Failure**: When a Panther test fails, a screenshot is automatically saved to `tmp/tests/screenshot/` to help diagnose UI issues.

## Browser Environment

The Docker environment is pre-configured with Google Chrome and ChromeDriver. Tests run in **headless mode** by default.

# Test Philosophy

TransmitMail aims to:
- Maintain compatibility with a wide range of PHP versions.
- Prevent regressions.
- Ensure reliable form processing and email delivery.

Contributions that improve test coverage are welcome.

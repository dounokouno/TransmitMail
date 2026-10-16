# Testing

This document describes how to run the automated tests for TransmitMail.

## Overview

TransmitMail uses automated tests to verify the following:

- Form submission
- Input validation
- Confirmation page
- Email delivery
- Error handling
- Compatibility across supported PHP versions

For information about setting up the development environment, see [DEVELOPMENT.md](DEVELOPMENT.md).

---

## Supported PHP Versions

The test suite is regularly executed against the following PHP versions:

- PHP 7.2
- PHP 7.3
- PHP 7.4
- PHP 8.0
- PHP 8.1
- PHP 8.2
- PHP 8.3
- PHP 8.4
- PHP 8.5

---

## Running Tests

### Install Dependencies

Choose the directory corresponding to the PHP version you want to test and install its dependencies.

Example for PHP 8.5:

```bash
cd php85
composer install
```

Replace `php85` with the directory corresponding to your target PHP version (for example, `php72` or `php84`).

### Start the Test Environment

Example for PHP 8.5:

```bash
docker compose up php85
```

Replace `php85` with the service corresponding to your target PHP version.

### Run PHPUnit

Inside the container:

```bash
vendor/bin/phpunit
```

Or from the host:

```bash
docker compose exec php85 vendor/bin/phpunit
```

Replace `php85` with the service corresponding to your target PHP version.

### Run End-to-End Tests

TransmitMail uses Symfony Panther for browser-based end-to-end (E2E) testing.

Example:

```bash
vendor/bin/phpunit tests/E2E
```

---

## Continuous Integration

The automated test suite is executed by CircleCI.

CircleCI verifies:

- Compatibility across supported PHP versions
- PHPUnit tests
- Symfony Panther end-to-end tests

GitHub Actions is responsible for packaging and publishing releases.

---

## Writing Tests

When fixing a bug:

1. Add a test that reproduces the issue.
2. Confirm that the test fails.
3. Fix the implementation.
4. Confirm that the test passes.

When implementing a new feature:

1. Add or update tests for the new functionality.
2. Ensure that all existing tests continue to pass.

---

## Debugging

Run PHPUnit with additional output:

```bash
vendor/bin/phpunit --debug
```

Run a specific test:

```bash
vendor/bin/phpunit --filter TestName
```

If temporary files cause problems, check the test environment and clean up any unnecessary temporary files before running the tests again.

---

## Testing Goals

The goals of the test suite are to:

- Prevent regressions
- Maintain compatibility across supported PHP versions
- Ensure reliable form processing
- Maintain release quality

# PHP Bucket Testing

[![Tests](https://github.com/Jord-JD/php-bucket-testing/actions/workflows/tests.yml/badge.svg)](https://github.com/Jord-JD/php-bucket-testing/actions/workflows/tests.yml)
[![Packagist](https://img.shields.io/packagist/v/jord-jd/php-bucket-testing.svg)](https://packagist.org/packages/jord-jd/php-bucket-testing)

This library enables developers to easily redirect users to different URLs, for the purpose 
of bucket testing. Bucket testing is also known as A/B testing or split testing.

This type of testing is used to test two or more versions of a webpage to determine which one
performs better based on specified key metrics, such as clicks, downloads, purchases or any other
form of conversion.

## Features

* Random selection of buckets, with optional weights
* Stable weighted assignment for a user, account, or session identifier
* Automatic handling of temporary redirects
* Ability to retrieve bucket and manually handle URL redirection
* Injectable random-number generation for reproducible tests
* Easy to use fluent interface syntax

## Installation
To install, just run the following composer command.

`composer require jord-jd/php-bucket-testing`

Remember to include the `vendor/autoload.php` file if your framework does not already do so.

## Usage

```php

use \JordJD\BucketTesting\BucketManager;
use \JordJD\BucketTesting\Bucket;

// Create a new bucket manager
$bucketManager = new BucketManager;

// Add buckets, with URLs and optional weights
$bucketManager->add(new Bucket('https://google.co.uk/'))->withWeight(25);
$bucketManager->add(new Bucket('https://php.net/'))->withWeight(75);

// Redirect to a randomly selected URL
$bucketManager->redirect();

// Or replace the call above with another valid 3xx status code:
// $bucketManager->redirect(307);

// Or, if you wish, get a random bucket and manually handle the redirection
$bucket = $bucketManager->getRandomBucket();
header('location: '.$bucket->url);

```

## Stable experiment assignments

For a meaningful A/B test, the same subject should normally stay in the same
cohort. Pass a stable, non-empty identifier such as a user ID, account ID, or
session ID to `getBucketForSubject()`:

```php
$bucket = $bucketManager->getBucketForSubject($userId);
```

The assignment is deterministic and respects the configured weights. It does
not require server-side storage, but changing the bucket list, its order, or its
weights can change existing assignments.

## Deterministic tests

You may inject a callable that returns an integer within the requested range.
Production code uses `random_int()` when available and falls back to `mt_rand()`
on older PHP versions.

```php
$bucketManager = new BucketManager(function ($minimum, $maximum) {
    return $minimum;
});
```

Bucket URLs may be absolute or relative, but must be non-empty strings and may
not contain carriage-return or newline characters. `redirect()` accepts only
integer HTTP status codes from 300 through 399.

## Compatibility

PHP 5.4 through the current PHP 8.x releases are supported. PHP 5.4 is the
minimum because the package source has always used short-array syntax.

### Upgrading to 4.0

Composer now enforces the real PHP 5.4 syntax minimum instead of incorrectly
advertising PHP 5.3 support. `Bucket` also rejects empty, non-string, or
header-unsafe URL values when constructed. Existing valid bucket URLs and the
original random-selection API continue to work unchanged.

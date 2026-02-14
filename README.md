# PHP Bucket Testing

[![Build Status](https://travis-ci.org/Jord-JD/php-bucket-testing.svg?branch=master)](https://travis-ci.org/Jord-JD/php-bucket-testing)
[![Coverage Status](https://coveralls.io/repos/github/Jord-JD/php-bucket-testing/badge.svg?branch=master)](https://coveralls.io/github/Jord-JD/php-bucket-testing?branch=master)

This library enables developers to easily redirect users to different URLs, for the purpose 
of bucket testing. Bucket testing is also known as A/B testing or split testing.

This type of testing is used to test two or more versions of a webpage to determine which one
performs better based on specfied key metrics, such as clicks, downloads, purchases or any other
form of conversion.

## Features

* Random selection of buckets, with optional weights
* Automatic handling of temporary redirects
* Ability to retrieve bucket and manually handle URL redirection
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

// Or, if you wish, get a random bucket and manually handle the redirection
$bucket = $bucketManager->getRandomBucket();
header('location: '.$bucket->url);

```

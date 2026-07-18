<?php

namespace JordJD\BucketTesting;

use InvalidArgumentException;

class Bucket
{
    public $url;

    public function __construct($url)
    {
        if (!is_string($url) || trim($url) === '') {
            throw new InvalidArgumentException('Bucket URL must be specified as a non-empty string.');
        }

        if (preg_match('/[\r\n]/', $url)) {
            throw new InvalidArgumentException('Bucket URL must not contain header control characters.');
        }

        $this->url = $url;
    }
}

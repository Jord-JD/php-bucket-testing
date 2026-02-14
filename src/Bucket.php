<?php

namespace JordJD\BucketTesting;

class Bucket
{
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }
}

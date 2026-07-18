<?php

namespace JordJD\BucketTesting;

use Exception;
use InvalidArgumentException;

class BucketManager
{
    private $weightedBuckets = [];
    private $randomIntegerGenerator;

    public function __construct($randomIntegerGenerator = null)
    {
        if ($randomIntegerGenerator !== null && !is_callable($randomIntegerGenerator)) {
            throw new InvalidArgumentException('Random integer generator must be callable.');
        }

        $this->randomIntegerGenerator = $randomIntegerGenerator;
    }

    public function add(Bucket $bucket)
    {
        $this->weightedBuckets[] = new WeightedBucket($bucket);

        return $this;
    }

    public function withWeight($weight)
    {
        $weightedBucket = $this->getMostRecentlyAddedWeightedBucket();

        $weightedBucket->setWeight($weight);

        return $this;
    }

    private function getMostRecentlyAddedWeightedBucket()
    {
        $weightedBucket = end($this->weightedBuckets);

        if (!$weightedBucket) {
            throw new Exception('Unable to retrieve most recently added weight bucket. You must add a bucket first!');
        }

        return $weightedBucket;
    }

    private function getRandomWeightedBucket()
    {
        $weightedBucketSelector = new WeightedBucketSelector($this->weightedBuckets, $this->randomIntegerGenerator);

        return $weightedBucketSelector->getRandomBucket();
    }

    public function getRandomBucket()
    {
        return $this->getRandomWeightedBucket()->bucket;
    }

    /**
     * Select the same weighted bucket for the same stable subject identifier.
     *
     * This is useful for keeping a user, account, or session in one experiment
     * cohort without storing the assignment separately.
     */
    public function getBucketForSubject($subject)
    {
        $weightedBucketSelector = new WeightedBucketSelector($this->weightedBuckets);

        return $weightedBucketSelector->getBucketForSubject($subject)->bucket;
    }

    public function redirect($statusCode = 302)
    {
        if (!is_int($statusCode) || $statusCode < 300 || $statusCode > 399) {
            throw new InvalidArgumentException('Redirect status code must be an integer between 300 and 399.');
        }

        $bucket = $this->getRandomBucket();

        header('Location: '.$bucket->url, true, $statusCode);
        die;
    }
}

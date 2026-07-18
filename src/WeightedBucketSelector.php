<?php

namespace JordJD\BucketTesting;

use Exception;
use InvalidArgumentException;
use OverflowException;
use UnexpectedValueException;

class WeightedBucketSelector
{
    private $weightedBuckets;
    private $randomIntegerGenerator;

    public function __construct(array $weightedBuckets, $randomIntegerGenerator = null)
    {
        if ($randomIntegerGenerator !== null && !is_callable($randomIntegerGenerator)) {
            throw new InvalidArgumentException('Random integer generator must be callable.');
        }

        $this->weightedBuckets = $weightedBuckets;
        $this->randomIntegerGenerator = $randomIntegerGenerator;
    }

    public function getRandomBucket()
    {
        if (!$this->weightedBuckets) {
            throw new Exception('There are no weighted buckets available. You must add a bucket first!');
        }

        $index = $this->getRandomWeightedIndex();

        return $this->weightedBuckets[$index];
    }

    public function getBucketForSubject($subject)
    {
        if (!$this->weightedBuckets) {
            throw new Exception('There are no weighted buckets available. You must add a bucket first!');
        }

        if (!is_scalar($subject) && !(is_object($subject) && method_exists($subject, '__toString'))) {
            throw new InvalidArgumentException('Bucket subject must be a scalar value or stringable object.');
        }

        $subject = (string) $subject;

        if ($subject === '') {
            throw new InvalidArgumentException('Bucket subject must not be empty.');
        }

        $totalWeight = $this->getTotalWeight();

        if ($totalWeight > (int) floor(PHP_INT_MAX / 16)) {
            throw new OverflowException('Total bucket weight is too large for deterministic subject selection on this platform.');
        }

        $remainder = 0;
        $hash = hash('sha256', $subject);

        for ($position = 0, $length = strlen($hash); $position < $length; $position++) {
            $remainder = (($remainder * 16) + hexdec($hash[$position])) % $totalWeight;
        }

        return $this->getWeightedBucketAtPosition($remainder + 1);
    }

    private function getRandomWeightedIndex()
    {
        $totalWeight = $this->getTotalWeight();

        if ($this->randomIntegerGenerator !== null) {
            $rand = call_user_func($this->randomIntegerGenerator, 1, $totalWeight);

            if (!is_int($rand) || $rand < 1 || $rand > $totalWeight) {
                throw new UnexpectedValueException('Random integer generator must return an integer within the requested range.');
            }
        } elseif (function_exists('random_int')) {
            $rand = random_int(1, $totalWeight);
        } else {
            if ($totalWeight > mt_getrandmax()) {
                throw new OverflowException('Total bucket weight exceeds the random number range on this PHP version. Supply a custom generator.');
            }

            $rand = mt_rand(1, $totalWeight);
        }

        foreach ($this->weightedBuckets as $index => $weightedBucket) {
            $rand -= $weightedBucket->weight;

            if ($rand <= 0) {
                return $index;
            }
        }

        throw new Exception('Error retrieving random weighted index during bucket selection process.');
    }

    private function getTotalWeight()
    {
        $totalWeight = 0;

        foreach ($this->weightedBuckets as $weightedBucket) {
            if (!$weightedBucket instanceof WeightedBucket) {
                throw new InvalidArgumentException('Weighted bucket selector expects WeightedBucket instances.');
            }

            if ($totalWeight > PHP_INT_MAX - $weightedBucket->weight) {
                throw new OverflowException('Total bucket weight exceeds the integer range on this platform.');
            }

            $totalWeight += $weightedBucket->weight;
        }

        return $totalWeight;
    }

    private function getWeightedBucketAtPosition($position)
    {
        foreach ($this->weightedBuckets as $weightedBucket) {
            $position -= $weightedBucket->weight;

            if ($position <= 0) {
                return $weightedBucket;
            }
        }

        throw new Exception('Error retrieving bucket at weighted position.');
    }
}

<?php

use PHPUnit\Framework\TestCase;
use JordJD\BucketTesting\Bucket;
use JordJD\BucketTesting\BucketManager;
use JordJD\BucketTesting\WeightedBucket;

final class ExceptionsTest extends TestCase
{
    public function testGetRandomBucketWhenNoBucketsAreAdded()
    {
        $this->expectException(Exception::class);
        (new BucketManager())->getRandomBucket();
    }

    public function testGetMostRecentlyAddedWeightedBucketWhenNoBucketsAreAdded()
    {
        $method = new ReflectionMethod(BucketManager::class, 'getMostRecentlyAddedWeightedBucket');
        $method->setAccessible(true);

        $this->expectException(Exception::class);
        $method->invoke(new BucketManager());
    }

    public function testRedirectWhenNoBucketsAreAdded()
    {
        $this->expectException(Exception::class);
        (new BucketManager())->redirect();
    }

    public function testCreatingWeightedBucketWithStringWeight()
    {
        $this->expectException(Exception::class);
        (new WeightedBucket(new Bucket('https://php.net/')))->setWeight('not a number!');
    }

    public function testCreatingWeightedBucketWithFloatWeight()
    {
        $this->expectException(Exception::class);
        (new WeightedBucket(new Bucket('https://php.net/')))->setWeight(2.75);
    }

    public function testCreatingWeightedBucketWithNegativeWeight()
    {
        $this->expectException(Exception::class);
        (new WeightedBucket(new Bucket('https://php.net/')))->setWeight(-1);
    }

    public function testCreatingWeightedBucketWithZeroWeight()
    {
        $this->expectException(Exception::class);
        (new WeightedBucket(new Bucket('https://php.net/')))->setWeight(0);
    }
}

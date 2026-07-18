<?php

use PHPUnit\Framework\TestCase;
use JordJD\BucketTesting\Bucket;
use JordJD\BucketTesting\BucketManager;
use JordJD\BucketTesting\WeightedBucket;

final class ExceptionsTest extends TestCase
{
    private function expectExceptionCompatible($class)
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException($class);

            return;
        }

        $this->setExpectedException($class);
    }

    public function testGetRandomBucketWhenNoBucketsAreAdded()
    {
        $this->expectExceptionCompatible('Exception');
        (new BucketManager())->getRandomBucket();
    }

    public function testGetMostRecentlyAddedWeightedBucketWhenNoBucketsAreAdded()
    {
        $method = new ReflectionMethod('JordJD\\BucketTesting\\BucketManager', 'getMostRecentlyAddedWeightedBucket');
        $method->setAccessible(true);

        $this->expectExceptionCompatible('Exception');
        $method->invoke(new BucketManager());
    }

    public function testRedirectWhenNoBucketsAreAdded()
    {
        $this->expectExceptionCompatible('Exception');
        (new BucketManager())->redirect();
    }

    public function testCreatingWeightedBucketWithStringWeight()
    {
        $this->expectExceptionCompatible('Exception');
        (new WeightedBucket(new Bucket('https://php.net/')))->setWeight('not a number!');
    }

    public function testCreatingWeightedBucketWithFloatWeight()
    {
        $this->expectExceptionCompatible('Exception');
        (new WeightedBucket(new Bucket('https://php.net/')))->setWeight(2.75);
    }

    public function testCreatingWeightedBucketWithNegativeWeight()
    {
        $this->expectExceptionCompatible('Exception');
        (new WeightedBucket(new Bucket('https://php.net/')))->setWeight(-1);
    }

    public function testCreatingWeightedBucketWithZeroWeight()
    {
        $this->expectExceptionCompatible('Exception');
        (new WeightedBucket(new Bucket('https://php.net/')))->setWeight(0);
    }

    public function testBucketRejectsHeaderInjection()
    {
        $this->expectExceptionCompatible('InvalidArgumentException');
        new Bucket("https://example.com/\r\nX-Test: injected");
    }

    public function testBucketRejectsEmptyUrl()
    {
        $this->expectExceptionCompatible('InvalidArgumentException');
        new Bucket('');
    }

    public function testSubjectSelectionRejectsEmptySubject()
    {
        $manager = new BucketManager();
        $manager->add(new Bucket('https://example.com/'));

        $this->expectExceptionCompatible('InvalidArgumentException');
        $manager->getBucketForSubject('');
    }

    public function testRandomGeneratorMustReturnRequestedIntegerRange()
    {
        $manager = new BucketManager(function ($minimum, $maximum) {
            return $maximum + 1;
        });
        $manager->add(new Bucket('https://example.com/'));

        $this->expectExceptionCompatible('UnexpectedValueException');
        $manager->getRandomBucket();
    }

    public function testRedirectStatusMustBeThreeHundredRangeInteger()
    {
        $this->expectExceptionCompatible('InvalidArgumentException');
        (new BucketManager())->redirect(200);
    }
}

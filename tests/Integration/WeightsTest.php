<?php

use PHPUnit\Framework\TestCase;
use JordJD\BucketTesting\Bucket;
use JordJD\BucketTesting\BucketManager;

final class WeightsTest extends TestCase
{
    private function createManager($randomIntegerGenerator = null)
    {
        $bucketManager = new BucketManager($randomIntegerGenerator);
        $bucketManager->add(new Bucket('https://google.co.uk/'))->withWeight(25);
        $bucketManager->add(new Bucket('https://php.net/'))->withWeight(75);

        return $bucketManager;
    }

    public function testCustomRandomGeneratorSelectsWeightedRanges()
    {
        $firstBucketManager = $this->createManager(function ($minimum, $maximum) {
            return $minimum;
        });
        $lastBucketManager = $this->createManager(function ($minimum, $maximum) {
            return $maximum;
        });

        $this->assertSame('https://google.co.uk/', $firstBucketManager->getRandomBucket()->url);
        $this->assertSame('https://php.net/', $lastBucketManager->getRandomBucket()->url);
    }

    public function testSubjectAssignmentIsStableAndRespectsWeights()
    {
        $bucketManager = $this->createManager();
        $firstAssignment = $bucketManager->getBucketForSubject('account-123')->url;

        for ($i = 0; $i < 20; $i++) {
            $this->assertSame($firstAssignment, $bucketManager->getBucketForSubject('account-123')->url);
        }

        $urlCount = [
            'https://google.co.uk/' => 0,
            'https://php.net/' => 0,
        ];

        for ($i = 0; $i < 1000; $i++) {
            $urlCount[$bucketManager->getBucketForSubject('account-'.$i)->url]++;
        }

        $this->assertGreaterThan(200, $urlCount['https://google.co.uk/']);
        $this->assertLessThan(300, $urlCount['https://google.co.uk/']);
        $this->assertGreaterThan(700, $urlCount['https://php.net/']);
        $this->assertLessThan(800, $urlCount['https://php.net/']);
    }
}

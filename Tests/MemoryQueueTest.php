<?php

namespace Itr\DelayedJobBundle\Tests;

use Itr\DelayedJobBundle\DelayedJob\MemoryQueue;
use Itr\DelayedJobBundle\Tests\Fixture\Job\EchoJob;
use Itr\DelayedJobBundle\Tests\Fixture\Job\BeforeAfterFailureJob;
use Itr\DelayedJobBundle\Tests\Fixture\Job\FatalErrorJob;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;

class MemoryQueueTest extends \PHPUnit_Framework_TestCase
{

    public function testSingleJob()
    {
        $faker = \Faker\Factory::create();

        $queue = new MemoryQueue();
        $result = $queue->execute(new EchoJob($faker->name));
        $this->assertInstanceOf('Itr\DelayedJobBundle\DelayedJob\Result\JobResult', $result);
        $this->assertInternalType('bool', $result->isFailed());
        $this->assertFalse($result->isFailed());
        $this->assertTrue($result->isSuccess());
    }

    public function testBeforeAfterFailure()
    {
        $faker = \Faker\Factory::create();

        $queue = new MemoryQueue();
        $result = $queue->execute(new BeforeAfterFailureJob($faker->name));
        $this->assertInstanceOf('Itr\DelayedJobBundle\DelayedJob\Result\JobResult', $result);
        $arrResult = $result->getResult();
        $this->assertCount(2, $arrResult);
        $this->assertArrayHasKey('called', $arrResult);
        $this->assertEquals('after', $arrResult['called']);

        $result = $queue->execute(new BeforeAfterFailureJob($faker->name, true));
        $this->assertInstanceOf('Itr\DelayedJobBundle\DelayedJob\Result\JobResult', $result);
        $arrResult = $result->getResult();
        $this->assertCount(1, $arrResult);
        $this->assertArrayHasKey('called', $arrResult);
        $this->assertEquals('failure', $arrResult['called']);
    }

    public function testMemoryQueueSimple()
    {
        $faker = \Faker\Factory::create();

        $queue = new MemoryQueue();
        $queue->enqueue(new EchoJob($faker->name));
        $this->assertEquals(1, $queue->count());

        $queue->enqueue(new EchoJob($faker->name), 'custom');
        $this->assertEquals(1, $queue->count());
        $this->assertEquals(1, $queue->count('custom'));

        $queue->enqueue(new EchoJob($faker->name));
        $this->assertEquals(2, $queue->count());
        $this->assertEquals(1, $queue->count('custom'));

        $shouldBeOnTop = new EchoJob($faker->name);
        $shouldBeOnBottom = new EchoJob($faker->name);
        $queue->enqueue($shouldBeOnTop, 'custom', 100);
        $queue->enqueue($shouldBeOnBottom, 'custom', -100);
        $this->assertEquals(2, $queue->count());
        $this->assertEquals(3, $queue->count('custom'));

        $queue->run(1, 'custom');
        $this->assertEquals(2, $queue->count('custom'));

        $queue->run(10, 'custom');
        $this->assertEquals(0, $queue->count('custom'));
    }

    public function testMemoryQueueFailed()
    {
        $faker = \Faker\Factory::create();

        $queue = new MemoryQueue();
        $queue->enqueue(new BeforeAfterFailureJob($faker->name, false), MemoryQueue::DEFAULT_QUEUE, 0, 2);
        $queue->enqueue(new BeforeAfterFailureJob($faker->name, false), MemoryQueue::DEFAULT_QUEUE, 0, 2);
        $queue->enqueue(new BeforeAfterFailureJob($faker->name, false), MemoryQueue::DEFAULT_QUEUE, 0, 2);
        $queue->enqueue(new BeforeAfterFailureJob($faker->name, true), MemoryQueue::DEFAULT_QUEUE, 0, 2);
        $queue->enqueue(new BeforeAfterFailureJob($faker->name, true), MemoryQueue::DEFAULT_QUEUE, 0, 3);

        $this->assertEquals(5, $queue->count());

        $queue->run(5);
        $this->assertEquals(2, $queue->count());
        $queue->run();
        $this->assertEquals(2, $queue->count());
        $queue->run();
        $this->assertEquals(1, $queue->count());
        $queue->run();
        $this->assertEquals(0, $queue->count());
    }

    public function testMemoryQueueCyclic()
    {
        $faker = \Faker\Factory::create();

        $queue = new MemoryQueue();
        $queue->enqueue(new BeforeAfterFailureJob($faker->name, false), MemoryQueue::DEFAULT_QUEUE, 0, 1, true);
        $queue->enqueue(new BeforeAfterFailureJob($faker->name, true), MemoryQueue::DEFAULT_QUEUE, 0, 1, true);

        $this->assertEquals(2, $queue->count());

        $queue->run();
        $this->assertEquals(2, $queue->count());
        $queue->run();
        $this->assertEquals(2, $queue->count());
        $queue->run();
        $this->assertEquals(2, $queue->count());
        $queue->run();
        $this->assertEquals(2, $queue->count());
    }
}

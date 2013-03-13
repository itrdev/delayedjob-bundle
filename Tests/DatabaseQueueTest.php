<?php

namespace Itr\DelayedJobBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Itr\DelayedJobBundle\DelayedJob\MemoryQueue;
use Itr\DelayedJobBundle\Tests\Fixture\Job\EchoJob;
use Itr\DelayedJobBundle\Tests\Fixture\Job\BeforeAfterFailureJob;
use Itr\DelayedJobBundle\Tests\Fixture\Job\FatalErrorJob;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;

class DatabaseQueueTest extends WebTestCase
{
    protected $em;

    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

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
}

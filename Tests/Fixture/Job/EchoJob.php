<?php

namespace Itr\DelayedJobBundle\Tests\Fixture\Job;

use Itr\DelayedJobBundle\DelayedJob\Job\JobInterface;
use Itr\DelayedJobBundle\DelayedJob\Job\AbstractJob;
use Itr\DelayedJobBundle\DelayedJob\Result\JobResult;

/**
 * Simple job class that prints hello message.
 */
class EchoJob extends AbstractJob implements JobInterface
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function run()
    {
        echo 'Hi ' . $this->name . '! From EchoJob.';

        return new JobResult(true);
    }
}

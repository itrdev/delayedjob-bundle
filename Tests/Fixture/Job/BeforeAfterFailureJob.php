<?php

namespace Itr\DelayedJobBundle\Tests\Fixture\Job;

use Itr\DelayedJobBundle\DelayedJob\Job\JobInterface;
use Itr\DelayedJobBundle\DelayedJob\Job\AbstractJob;
use Itr\DelayedJobBundle\DelayedJob\Result\JobResult;

/**
 * Simple job class that prints hello message.
 */
class BeforeAfterFailureJob extends AbstractJob implements JobInterface
{
    private $name;
    private $shouldFail;

    public function __construct($name, $shouldFail = false)
    {
        $this->name = $name;
        $this->shouldFail = $shouldFail;
    }

    public function run()
    {
        if ($this->shouldFail) {
            throw new \ErrorException('jod is invalid');
        }
        echo 'Hi ' . $this->name . '! From EchoJob.';
        return new JobResult(true);
    }

    public function before()
    {
        echo 'before called';
    }

    public function after(JobResult $result)
    {
        $result->setResult(array('called' => 'after', 'more' => 'test'));
    }

    public function failure(JobResult $result)
    {
        $result->setResult(array('called' => 'failure'));
    }
}

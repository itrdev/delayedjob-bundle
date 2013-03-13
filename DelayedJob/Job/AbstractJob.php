<?php

namespace Itr\DelayedJobBundle\DelayedJob\Job;

use Itr\DelayedJobBundle\DelayedJob\Result\JobResult;
use Symfony\Component\DependencyInjection\ContainerAware;

abstract class AbstractJob extends ContainerAware
{

    /**
     * Will be called before 'run' method
     */
    public function before()
    {}

    /**
     * Will be called if there were no errors during 'run' execution
     * @param \Itr\DelayedJobBundle\DelayedJob\Result\JobResult $result
     */
    public function after(JobResult $result)
    {}

    /**
     * Will be called on error
     * @param \Itr\DelayedJobBundle\DelayedJob\Result\JobResult $result
     */
    public function failure(JobResult $result)
    {}
}

<?php

namespace Itr\DelayedJobBundle\DelayedJob\Job;

interface JobInterface
{

    /**
     * This method should return JobResult object
     * @return \Itr\DelayedJobBundle\DelayedJob\Result\JobResult
     */
    public function run();
}

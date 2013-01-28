<?php

namespace Itr\DelayedJobBundle\DelayedJob;

use Itr\DelayedJobBundle\DelayedJob\Job\JobInterface;

class DatabaseJobProxy extends JobProxy
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $lastResult = '';


    /**
     * @param \Itr\DelayedJobBundle\DelayedJob\Job\JobInterface $job
     * @param int $id
     * @param int $priority
     * @param int $attempts
     * @param int $attemptsSpent
     * @param int $period
     * @param bool $cyclic
     */
    public function __construct(JobInterface $job, $id, $priority = 0, $attempts = 5, $attemptsSpent = 0, $cyclic = false, $period = 0)
    {
        $this->setJob($job)
            ->setId($id)
            ->setPriority($priority)
            ->setAttempts($attempts)
            ->setAttemptsSpent($attemptsSpent)
            ->setCyclic($cyclic)
            ->setPeriod($period);
    }

    /**
     * @param int $id
     * @return DatabaseJobProxy
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $lastResult
     * @return DatabaseJobProxy
     */
    public function setLastResult($lastResult)
    {
        $this->lastResult = $lastResult;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastResult()
    {
        return $this->lastResult;
    }
}

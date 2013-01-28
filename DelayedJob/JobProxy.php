<?php

namespace Itr\DelayedJobBundle\DelayedJob;

use Itr\DelayedJobBundle\DelayedJob\Job\JobInterface;

class JobProxy
{
    /**
     * @var int
     */
    protected $priority = 0;

    /**
     * @var int
     */
    protected $attempts = 5;

    /**
     * @var int
     */
    protected $attemptsSpent = 0;

    /**
     * @var bool
     */
    protected $cyclic = true;

    /**
     * @var \Itr\DelayedJobBundle\DelayedJob\Job\JobInterface
     */
    protected $job;

    /**
     * @var int
     */
    protected $period;


    /**
     * @param \Itr\DelayedJobBundle\DelayedJob\Job\JobInterface $job
     * @param int $priority
     * @param int $attempts
     * @param int $attemptsSpent
     * @param int $period
     * @param bool $cyclic
     */
    public function __construct(JobInterface $job, $priority = 0, $attempts = 5, $attemptsSpent = 0, $cyclic = false, $period = 0)
    {
        $this->setJob($job)
            ->setPriority($priority)
            ->setAttempts($attempts)
            ->setAttemptsSpent($attemptsSpent)
            ->setCyclic($cyclic)
            ->setPeriod($period);
    }

    /**
     * @param $attempts
     * @return JobProxy
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * @return int
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * @param int $attemptsSpent
     * @return JobProxy
     */
    public function setAttemptsSpent($attemptsSpent)
    {
        $this->attemptsSpent = $attemptsSpent;

        return $this;
    }

    /**
     * @return int
     */
    public function getAttemptsSpent()
    {
        return $this->attemptsSpent;
    }

    /**
     * @param $cyclic
     * @return JobProxy
     */
    public function setCyclic($cyclic)
    {
        $this->cyclic = $cyclic;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCyclic()
    {
        return $this->cyclic;
    }

    /**
     * @param $job
     * @return JobProxy
     */
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return JobInterface
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param $priority
     * @return JobProxy
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $period
     * @return JobProxy
     */
    public function setPeriod($period)
    {
        $this->period = $period;

        return $this;
    }

    /**
     * @return int
     */
    public function getPeriod()
    {
        return $this->period;
    }
}

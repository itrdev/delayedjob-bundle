<?php

namespace Itr\DelayedJobBundle\DelayedJob;

class DelayedJobProxy
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
     * @var bool
     */
    protected $cyclic = true;

    /**
     * @var DelayedJobInterface
     */
    protected $job;

    /**
     * @param DelayedJobInterface $job
     * @param int $priority
     * @param int $attempts
     * @param bool $cyclic
     */
    public function __construct(DelayedJobInterface $job, $priority = 0, $attempts = 5, $cyclic = false)
    {
        $this->setJob($job)
            ->setPriority($priority)
            ->setAttempts($attempts)
            ->setCyclic($cyclic);
    }

    /**
     * @param $attempts
     * @return DelayedJobProxy
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
     * @param $cyclic
     * @return DelayedJobProxy
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
     * @return DelayedJobProxy
     */
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return DelayedJobInterface
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param $priority
     * @return DelayedJobProxy
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
}

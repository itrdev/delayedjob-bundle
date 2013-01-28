<?php

namespace Itr\DelayedJobBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Job
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $queue
     */
    private $queue;

    /**
     * @var integer $priority
     */
    private $priority;

    /**
     * @var integer $attempts
     */
    private $attempts;

    /**
     * @var integer $attemptsSpent
     */
    private $attemptsSpent;

    /**
     * @var bool $cyclic
     */
    private $cyclic = false;

    /**
     * @var bool $locked
     */
    private $locked = false;

    /**
     * @var \DateTime $createdAt
     */
    private $createdAt;

    /**
     * @var \DateTime $updatedAt
     */
    private $updatedAt;

    /**
     * @var \DateTime $updatedAt
     */
    private $nextRunAt;

    /**
     * @var string $lastResult
     */
    private $lastResult;

    /**
     * @var string $job
     */
    private $job;

    /**
     * @var int $period
     */
    private $period;


    /**
     * @param int $attempts
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;
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
     */
    public function setAttemptsSpent($attemptsSpent)
    {
        $this->attemptsSpent = $attemptsSpent;
    }

    /**
     * @return int
     */
    public function getAttemptsSpent()
    {
        return $this->attemptsSpent;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param boolean $cyclic
     */
    public function setCyclic($cyclic)
    {
        $this->cyclic = $cyclic;
    }

    /**
     * @return boolean
     */
    public function getCyclic()
    {
        return $this->cyclic;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     */
    public function setLastResult($lastResult)
    {
        $this->lastResult = $lastResult;
    }

    /**
     * @return string
     */
    public function getLastResult()
    {
        return $this->lastResult;
    }

    /**
     * @param \DateTime $nextRunAt
     */
    public function setNextRunAt($nextRunAt)
    {
        $this->nextRunAt = $nextRunAt;
    }

    /**
     * @return \DateTime
     */
    public function getNextRunAt()
    {
        return $this->nextRunAt;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param string $job
     */
    public function setJob($job)
    {
        $this->job = $job;
    }

    /**
     * @return string
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param int $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * @return int
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param boolean $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }
}

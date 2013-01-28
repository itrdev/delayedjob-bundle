<?php

namespace Itr\DelayedJobBundle\DelayedJob;

use Itr\DelayedJobBundle\DelayedJob\Job\JobInterface;
use Itr\DelayedJobBundle\DelayedJob\Job\AbstractJob;
use Itr\DelayedJobBundle\DelayedJob\Result\JobResult;
use Itr\DelayedJobBundle\Exception\InvalidJobResultException;

class MemoryQueue
{
    const DEFAULT_QUEUE = 'default';

    protected $queue = array();

    public function __construct()
    {
    }

    /**
     * Return count of jobs in the queue
     * @param string $queue
     * @return int
     */
    public function count($queue = self::DEFAULT_QUEUE)
    {
        return count($this->getQueue($queue));
    }

    /**
     * Adds job into the queue
     *
     * @param JobInterface $job
     * @param string $queue
     * @param int $priority
     * @param int $attempts
     * @param bool $cyclic
     *
     * @return MemoryQueue
     */
    public function enqueue(JobInterface $job, $queue = self::DEFAULT_QUEUE, $priority = 0, $attempts = 5, $cyclic = false)
    {
        $this->getQueue($queue)->insert(new JobProxy($job, $priority, $attempts, 0, $cyclic), $priority);

        return $this;
    }

    /**
     * Runs queue portion
     *
     * @param int $portion
     * @param string $name
     */
    public function run($portion = 10, $name = self::DEFAULT_QUEUE)
    {
        $queue = $this->getQueue($name);
        if ($queue->isEmpty()) {
            return;
        }

        $failed = array();
        $cyclic = array();
        $iterator = 0;
        while (++$iterator <= $portion && $queue->valid()) {

            /** @var JobProxy $proxy  */
            $proxy = $queue->current();
            $result = $this->execute($proxy->getJob());

            // if count of attempts less then spent attempts adds this job again
            if ($result->isFailed() && $proxy->getAttemptsSpent() < $proxy->getAttempts()) {
                $proxy->setAttemptsSpent( $proxy->getAttemptsSpent() + 1);
                $failed[] = array('proxy' => new JobProxy($proxy->getJob(), $proxy->getPriority(), $proxy->getAttempts(), $proxy->getAttemptsSpent(), $proxy->getCyclic()), 'priority' => $proxy->getPriority());

            // if it's a cyclic job adds it again
            } elseif ($proxy->getCyclic()) {
                $cyclic[] = array('proxy' => new JobProxy($proxy->getJob(), $proxy->getPriority(), $proxy->getAttempts(), $proxy->getAttemptsSpent(), $proxy->getCyclic()), 'priority' => $proxy->getPriority());
            }

            $queue->next();
        }

        // inserts all failed jobs that have an attempt
        foreach ($failed as $item) {
            $this->getQueue($name)->insert($item['proxy'], $item['priority']);
        }
        // reinserts all cyclic jobs that have an attempt
        foreach ($cyclic as $item) {
            $this->getQueue($name)->insert($item['proxy'], $item['priority']);
        }
    }

    /**
     * Executes job directly without adding it into the queue
     *
     * @param Job\JobInterface $job
     * @throws \Itr\DelayedJobBundle\Exception\InvalidJobResultException
     * @return JobResult
     */
    public function execute(JobInterface $job)
    {
        $preMethods = false;
        if ($job instanceof AbstractJob) {
            $preMethods = true;
        }

        if ($preMethods) {
            $job->before();
        }

        try {
            $result = $job->run();
        } catch (\Exception $e) {
            $result = $this->createFailureResult($e->getMessage());

            if ($preMethods) {
                $job->failure($result);
            }

            return $result;
        }

        if (!$result instanceof JobResult) {
            throw new InvalidJobResultException(get_class($job) . ' should return JobResult object');
        }

        if ($preMethods) {
            $job->after($result);
        }

        return $result;
    }

    /**
     * Creates failed result
     *
     * @param $message
     * @return Result\JobResult
     */
    protected function createFailureResult($message)
    {
        $result = new JobResult(false);
        $result->setError($message);
        return $result;
    }

    /**
     * Returns \SplPriorityQueue object
     *
     * @param $name
     * @return \SplPriorityQueue
     */
    protected function getQueue($name)
    {
        return $this->initQueue($name);
    }

    /**
     * Initializes queue
     *
     * @param $name
     * @return \SplPriorityQueue
     */
    protected function initQueue($name)
    {
        if (!isset($this->queue[$name])) {
            $this->queue[$name] = new \SplPriorityQueue();
        }

        return $this->queue[$name];
    }
}

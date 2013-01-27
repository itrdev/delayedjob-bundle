<?php

namespace Itr\DelayedJobBundle\DelayedJob;

class Queue
{
    protected $queue = array();

    protected $currentQueue = 'default';

    public function __construct()
    {
    }

    /**
     * Adds job into the queue
     *
     * @param DelayedJobInterface $job
     * @param string $queue
     * @param int $priority
     * @param int $attempts
     * @param bool $cyclic
     *
     * @return Queue
     */
    public function enqueue(DelayedJobInterface $job, $queue = 'default', $priority = 0, $attempts = 5, $cyclic = false)
    {
        $this->currentQueue = $queue;
        $this->getQueue($queue)->insert(new DelayedJobProxy($job, $priority, $attempts, $cyclic), $priority);

        return $this;
    }


    /**
     * Executes job from the top of the current queue
     *
     * @return bool|void
     */
    public function execute()
    {
        $queue = $this->getQueue($this->currentQueue);
        if (!$queue->isEmpty()) {
            return $this->perform($queue->top());
        }

        // return false? job result? null?
        return false;
    }

    protected function perform(DelayedJobProxy $job)
    {
        // run job
        // create result
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

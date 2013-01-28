<?php

namespace Itr\DelayedJobBundle\DelayedJob;

use Itr\DelayedJobBundle\DelayedJob\Job\JobInterface;
use Itr\DelayedJobBundle\DelayedJob\Job\AbstractJob;
use Itr\DelayedJobBundle\DelayedJob\Result\JobResult;
use Itr\DelayedJobBundle\Exception\InvalidJobResultException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Itr\DelayedJobBundle\Entity\Job as JobEntity;

class DatabaseQueue extends MemoryQueue implements ContainerAwareInterface
{

    protected $queue = array();

    protected $container;


    /**
     * Return count of jobs in the queue
     * @param string $queue
     * @return int
     */
    public function count($queue = self::DEFAULT_QUEUE)
    {
        return count($this->getRepository()->findBy(array('queue' => $queue)));
    }

    /**
     * Adds job into the queue
     *
     * @param JobInterface $job
     * @param string $queue
     * @param int $priority
     * @param int $attempts
     * @param bool $cyclic
     * @param int $period
     *
     * @return MemoryQueue
     */
    public function enqueue(JobInterface $job, $queue = self::DEFAULT_QUEUE, $priority = 0, $attempts = 5, $cyclic = false, $period = 0)
    {
        $this->insert(new JobProxy($job, $priority, $attempts, 0, $cyclic, $period));

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
                $proxy->setAttemptsSpent($proxy->getAttemptsSpent() + 1);
                $failed[] = array('proxy' => new JobProxy($proxy->getJob(), $proxy->getPriority(), $proxy->getAttempts(), $proxy->getAttemptsSpent(), $proxy->getCyclic(), $proxy->getPeriod()), 'priority' => $proxy->getPriority());

            // if it's a cyclic job adds it again
            } elseif ($proxy->getCyclic()) {
                $cyclic[] = array('proxy' => new JobProxy($proxy->getJob(), $proxy->getPriority(), $proxy->getAttempts(), $proxy->getAttemptsSpent(), $proxy->getCyclic(), $proxy->getPeriod()), 'priority' => $proxy->getPriority());
            }

            $queue->next();
        }

        // inserts all failed jobs that have an attempt
        foreach ($failed as $item) {
            $this->insert($item['proxy'], $name);
        }
        // reinserts all cyclic jobs that have an attempt
        foreach ($cyclic as $item) {
            $this->insert($item['proxy'], $name);
        }
    }

    /**
     * Inserts job into database table
     *
     * @param JobProxy $proxy
     * @param $queue
     */
    protected function insert($proxy, $queue = self::DEFAULT_QUEUE)
    {
        $jobEntity = $this->proxyToEntity($proxy, $queue);

        $em = $this->container->get('doctrine')->getManager();
        $em->persist($jobEntity);
        $em->flush();
    }

    /**
     * Returns SplPriorityQueue filled from db
     *
     * @param $name
     * @param int $portion
     * @return \SplPriorityQueue
     */
    protected function getQueue($name, $portion = 10)
    {
        $em = $this->container->get('doctrine')->getManager();
        /** @var \Doctrine\ORM\QueryBuilder $qb  */
        $qb = $em->createQueryBuilder();

        $result = $qb->select('j')->from('ItrDelayedJobBundle:Job', 'j')
            ->where('j.queue = :queue')
            ->setParameter('queue', $name)
            ->andWhere('j.nextRunAt <= :next_run')
            ->setParameter('next_run', new \DateTime())
            ->setMaxResults($portion)
            ->orderBy('j.priority')
            ->orderBy('j.nextRunAt')->getQuery()->execute();

        $queue = new \SplPriorityQueue();
        foreach ($result as $entity) {
            $queue->insert($this->entityToProxy($entity), $entity->getPriority());
        }

        return $queue;
    }

    /**
     * Converts proxy to db entity
     *
     * @param $proxy
     * @param $queue
     * @return \Itr\DelayedJobBundle\Entity\Job
     */
    protected function proxyToEntity($proxy, $queue)
    {
        $jobEntity = new JobEntity();
        $jobEntity->setQueue($queue);
        $jobEntity->setPriority($proxy->getPriority());
        $jobEntity->setAttempts($proxy->getAttempts());
        $jobEntity->setAttemptsSpent($proxy->getAttemptsSpent());
        $jobEntity->setCyclic($proxy->getCyclic());
        $jobEntity->setPeriod($proxy->getPeriod());
        $jobEntity->setJob(serialize($proxy->getJob()));

        return $jobEntity;
    }

    /**
     * Converts entity to proxy
     *
     * @param $jobEntity
     * @return JobProxy
     */
    protected function entityToProxy($jobEntity)
    {
        return new JobProxy(unserialize($jobEntity), $jobEntity->getPriority(), $jobEntity->getAttempts(), $jobEntity->getAttemptsSpent(), $jobEntity->getCyclic(), $jobEntity->getPeriod());
    }

    /**
     * @return mixed
     */
    protected function getRepository()
    {
        return $this->container->get('doctrine')->getManager()->getRepository('ItrDelayedJobBundle:Job');
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}

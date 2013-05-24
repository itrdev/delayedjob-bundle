<?php

namespace Itr\DelayedJobBundle\DelayedJob;

use Itr\DelayedJobBundle\DelayedJob\Job\JobInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
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
        $this->insert(new DatabaseJobProxy($job, 0, $priority, $attempts, 0, $cyclic, $period), $queue);

        return $this;
    }

    /**
     * Runs queue portion
     * @param int $portion
     * @param string $name
     * @return int
     */
    public function run($portion = 10, $name = self::DEFAULT_QUEUE)
    {
        $queue = $this->getQueue($name, $portion);
        if ($queue->isEmpty()) {
            return 0 ;
        }

        $failed = array();
        $cyclic = array();
        $iterator = 0;
        $count = 0;
        while (++$iterator <= $portion && $queue->valid()) {

            /** @var DatabaseJobProxy $proxy  */
            $proxy = $queue->current();
            $result = $this->execute($proxy->getJob());

            // if count of attempts less then spent attempts adds this job again
            if ($result->isFailed() && $proxy->getAttemptsSpent() < $proxy->getAttempts()) {
                $proxy->setAttemptsSpent($proxy->getAttemptsSpent() + 1);
                $proxy->setLastResult($result);
                $proxy->setId(0);
                $failed[] = $proxy;

                // if it's a cyclic job adds it again
            } elseif ($proxy->getCyclic()) {
                $proxy->setAttemptsSpent($proxy->getAttemptsSpent() + 1);
                $proxy->setLastResult($result);
                $proxy->setId(0);
                $failed[] = $proxy;
                $count ++;
            } else {
                $count++;
            }

            $queue->next();
        }

        // inserts all failed jobs that have an attempt
        foreach ($failed as $item) {
            $this->insert($item, $name);
        }
        // reinserts all cyclic jobs that have an attempt
        foreach ($cyclic as $item) {
            $this->insert($item, $name);
        }

        return $count;
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
        $jobEntity->setCreatedAt(new \DateTime());
        $dateTime = new \DateTime();
        $dateTime->add(\DateInterval::createFromDateString($proxy->getPeriod() . ' seconds'));
        $jobEntity->setNextRunAt($dateTime);

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

            $em->remove($entity);
            $em->flush();
        }

        return $queue;
    }

    /**
     * Converts proxy to db entity
     *
     * @param DatabaseJobProxy $proxy
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
        if ($proxy->getJob() && $proxy->getJob() instanceof ContainerAware) {
            $proxy->getJob()->setContainer(null);
        }
        $jobEntity->setJob(@serialize($proxy->getJob()));
        $jobEntity->setLastResult(@serialize($proxy->getLastResult()));

        return $jobEntity;
    }

    /**
     * Converts entity to proxy
     *
     * @param $jobEntity
     * @return DatabaseJobProxy
     */
    protected function entityToProxy($jobEntity)
    {
        $job = @unserialize($jobEntity->getJob());
        if ($job && $job instanceof ContainerAware) {
            $job->setContainer($this->container);
        }

        $proxy = new DatabaseJobProxy($job,
            $jobEntity->getId(),
            $jobEntity->getPriority(),
            $jobEntity->getAttempts(),
            $jobEntity->getAttemptsSpent(),
            $jobEntity->getCyclic(),
            $jobEntity->getPeriod());

        $proxy->setLastResult(@unserialize($jobEntity->getLastResult()));
        return $proxy;
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

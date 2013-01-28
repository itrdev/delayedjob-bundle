<?php

namespace Itr\DelayedJobBundle\DelayedJob\Result;

class JobResult
{

    protected $isSuccess = false;
    protected $result = array();
    protected $error;
    protected $message;

    public function __construct($isSuccess, array $result = array())
    {
        $this->isSuccess = (bool) $isSuccess;
        $this->setResult($result);
    }

    public function isSuccess()
    {
        return $this->isSuccess;
    }

    public function isFailed()
    {
        return !$this->isSuccess;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}

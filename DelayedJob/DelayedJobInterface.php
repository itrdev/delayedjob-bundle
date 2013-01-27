<?php

namespace Itr\DelayedJobBundle\DelayedJob;

interface DelayedJobInterface
{
    public function run();

    public function beforeRun();

    public function afterRun();

    public function onSuccess();

    public function onFailure();
}

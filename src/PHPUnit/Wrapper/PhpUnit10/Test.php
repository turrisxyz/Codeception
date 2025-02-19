<?php

namespace Codeception\PHPUnit\Wrapper;

use PHPUnit\Framework\Test as PHPUnitTest;
use PHPUnit\Framework\TestResult;

abstract class Test implements PHPUnitTest
{
    public function run(TestResult $result): void
    {
        $this->realRun($result);
    }

    abstract protected function realRun(TestResult $result): void;
}

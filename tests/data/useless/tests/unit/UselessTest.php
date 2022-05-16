<?php

class UselessTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testMakeNoAssertions()
    {

    }

    public function testMakeUnexpectedAssertion()
    {
        $this->expectNotToPerformAssertions();
        $this->assertTrue(true);
    }
}

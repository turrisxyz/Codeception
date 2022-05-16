<?php

class RunUselessTestsCest
{
    public function checkOutput(CliGuy $I): void
    {
        $I->amInPath('tests/data/useless');
        $I->executeCommand('run');
        // $I->seeInShellOutput('U UselessCept: Make no assertions');
        // $I->seeInShellOutput('U UselessCest: Make no assertions');
        $I->seeInShellOutput('U UselessTest: Make no assertions');
        $I->seeInShellOutput('U UselessTest: Make unexpected assertion');
        $I->seeInShellOutput('OK, but incomplete, skipped, or risky tests!');
        $I->seeInShellOutput('There were 2 risky tests:');

        if (DIRECTORY_SEPARATOR === '/') {
//            $I->seeInShellOutput(
//                '1) UselessCept: Make no assertions
// Test  tests/unit/UselessCept.php
//This test did not perform any assertions'
//            );
//            $I->seeInShellOutput(
//                '
//2) UselessCest: Make no assertions
// Test  tests/unit/UselessCest.php:makeNoAssertions
//This test did not perform any assertions
//
//Scenario Steps:
//
// 1. // make no assertions'
//            );
            $I->seeInShellOutput(
                '
1) UselessTest: Make no assertions
 Test  tests/unit/UselessTest.php:testMakeNoAssertions
This test did not perform any assertions'
            );
            $I->seeInShellOutput(
                '
2) UselessTest: Make unexpected assertion
 Test  tests/unit/UselessTest.php:testMakeUnexpectedAssertion
This test is annotated with "@doesNotPerformAssertions" but performed 1 assertions'
            );

            return;
        }

//        $I->seeInShellOutput(
//            '1) UselessCept: Make no assertions
// Test  tests\unit\UselessCept.php
//This test did not perform any assertions'
//        );
//        $I->seeInShellOutput(
//            '
//2) UselessCest: Make no assertions
// Test  tests\unit\UselessCest.php:makeNoAssertions
//This test did not perform any assertions
//
//Scenario Steps:
//
// 1. // make no assertions'
//        );
        $I->seeInShellOutput(
            '
1) UselessTest: Make no assertions
 Test  tests\unit\UselessTest.php:testMakeNoAssertions
This test did not perform any assertions'
        );
        $I->seeInShellOutput(
            '
2) UselessTest: Make unexpected assertion
 Test  tests\unit\UselessTest.php:testMakeUnexpectedAssertion
This test is annotated with "@doesNotPerformAssertions" but performed 1 assertions'
        );
    }

    public function checkReports(CliGuy $I): void
    {
        $I->amInPath('tests/data/useless');
        $I->executeCommand('run --report --xml --phpunit-xml --html');
        $I->seeInShellOutput('Useless: 2');
        // $I->seeInShellOutput('UselessCept: Make no assertions............................................Useless');
        // $I->seeInShellOutput('UselessCest: Make no assertions............................................Useless');
        $I->seeInShellOutput('UselessTest: Make no assertions............................................Useless');
        $I->seeInShellOutput('UselessTest: Make unexpected assertion.....................................Useless');

        $I->seeFileFound('report.xml', 'tests/_output');
        $I->seeInThisFile(
            '<testsuite name="unit" tests="4" assertions="1" errors="2" failures="0" skipped="0" time="'
        );
        // $I->seeInThisFile('<testcase name="Useless"');
        // $I->seeInThisFile('<testcase name="makeNoAssertions" class="UselessCest"');
        $I->seeInThisFile('<testcase name="testMakeNoAssertions" class="UselessTest" file="');
        $I->seeInThisFile('<testcase name="testMakeUnexpectedAssertion" class="UselessTest" file="');
        $I->seeInThisFile('Risky Test');

        $I->seeFileFound('phpunit-report.xml', 'tests/_output');
        $I->seeInThisFile(
            '<testsuite name="unit" tests="4" assertions="1" errors="2" failures="0" skipped="0" time="'
        );
        // $I->seeInThisFile('<testcase name="Useless"');
        // $I->seeInThisFile('<testcase name="makeNoAssertions" class="UselessCest"');
        $I->seeInThisFile('<testcase name="testMakeNoAssertions" class="UselessTest" file="');
        $I->seeInThisFile('<testcase name="testMakeUnexpectedAssertion" class="UselessTest" file="');
        $I->seeInThisFile('Risky Test');

        $I->seeFileFound('report.html', 'tests/_output');
        $I->seeInThisFile('<td class="scenarioUseless">Useless scenarios:</td>');
        $I->seeInThisFile('<td class="scenarioUselessValue"><strong>2</strong></td>');
        $I->seeInThisFile('UselessCest');
        $I->seeInThisFile('UselessTest');
        $I->seeInThisFile('UselessCept');
    }
}

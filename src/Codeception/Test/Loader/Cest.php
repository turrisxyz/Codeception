<?php

declare(strict_types=1);

namespace Codeception\Test\Loader;

use Codeception\Exception\TestParseException;
use Codeception\Lib\Parser;
use Codeception\Test\Cest as CestFormat;
use Codeception\Util\Annotation;
use Codeception\Util\ReflectionHelper;
use PHPUnit\Framework\DataProviderTestSuite;
use ReflectionClass;
use ReflectionException;

use function array_map;
use function get_class_methods;

class Cest implements LoaderInterface
{
    /**
     * @var DataProviderTestSuite[]|CestFormat[]
     */
    protected array $tests = [];

    /**
     * @return DataProviderTestSuite[]|CestFormat[]
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    public function getPattern(): string
    {
        return '~Cest\.php$~';
    }

    public function loadTests(string $filename): void
    {
        Parser::load($filename);
        $testClasses = Parser::getClassesFromFile($filename);

        foreach ($testClasses as $testClass) {
            if (!str_ends_with($testClass, 'Cest')) {
                continue;
            }
            if (!(new ReflectionClass($testClass))->isInstantiable()) {
                continue;
            }
            $unit = new $testClass();

            $methods = get_class_methods($testClass);
            foreach ($methods as $method) {
                if (str_starts_with($method, '_')) {
                    continue;
                }
                $examples = [];

                // example Annotation
                $rawExamples = Annotation::forMethod($unit, $method)->fetchAll('example');
                if ($rawExamples !== []) {
                    $rawExample = reset($rawExamples);
                    if (is_string($rawExample)) {
                        $examples = array_map(
                            fn ($v): ?array => Annotation::arrayValue($v),
                            $rawExamples
                        );
                    } elseif ($rawExamples) {
                        $examples = $rawExamples;
                    }
                }

                // dataProvider Annotation
                $dataMethod = Annotation::forMethod($testClass, $method)->fetch('dataProvider');
                // lowercase for back compatible
                if (empty($dataMethod)) {
                    $dataMethod = Annotation::forMethod($testClass, $method)->fetch('dataprovider');
                }

                if (!empty($dataMethod)) {
                    try {
                        $data = ReflectionHelper::invokePrivateMethod($unit, $dataMethod);
                        foreach ($data as $example) {
                            $examples[] = $example;
                        }
                    } catch (ReflectionException $e) {
                        throw new TestParseException(
                            $filename,
                            "DataProvider '{$dataMethod}' for {$testClass}->{$method} is invalid or not callable.\n" .
                            "Make sure that the dataprovider exist within the test class."
                        );
                    }
                }

                if (!empty($examples)) {
                    $dataProvider = new DataProviderTestSuite();
                    $index = 0;
                    foreach ($examples as $k => $example) {
                        if ($example === null) {
                            throw new TestParseException(
                                $filename,
                                "Example for {$testClass}->{$method} contains invalid data:\n" .
                                $rawExamples[$k] . "\n" .
                                'Make sure this is a valid JSON (Hint: "-char for strings) or a single-line annotation in Doctrine-style'
                            );
                        }
                        $test = new CestFormat($unit, $method, $filename);
                        $test->getMetadata()->setCurrent(['example' => $example]);
                        $test->getMetadata()->setIndex($index);
                        $dataProvider->addTest($test);
                        ++$index;
                    }
                    $this->tests[] = $dataProvider;
                    continue;
                }
                $this->tests[] = new CestFormat($unit, $method, $filename);
            }
        }
    }
}

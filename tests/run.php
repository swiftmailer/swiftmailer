<?php

require_once dirname(__FILE__) . '/config.php';

require_once SWEETY_SIMPLETEST_PATH . '/unit_tester.php';
require_once SWEETY_SIMPLETEST_PATH . '/mock_objects.php';
require_once SWEETY_SIMPLETEST_PATH . '/reporter.php';
require_once SWEETY_SIMPLETEST_PATH . '/xml.php';

require_once 'Sweety/Runner.php';
require_once 'Sweety/Runner/CliRunner.php';
require_once 'Sweety/Reporter/CliReporter.php';

$runner = new Sweety_Runner_CliRunner(
  explode(PATH_SEPARATOR, SWEETY_TEST_PATH),
  SWEETY_PHP_EXE . ' ' . $argv[0]
  );

$name = isset($argv[1]) ? $argv[1] : 'All Tests';
$runner->setReporter(new Sweety_Reporter_CliReporter($name));

$runner->setIgnoredClassRegex(SWEETY_IGNORED_CLASSES);

$locators = preg_split('/\s*,\s*/', SWEETY_TEST_LOCATOR);
foreach ($locators as $locator)
{
  $runner->registerTestLocator(new $locator());
}

if (isset($argv[1]) && !preg_match('~!?/.*?/~', $argv[1]))
{
  $testName = $argv[1];
  $format = isset($argv[2]) ? $argv[2] : Sweety_Runner::REPORT_TEXT;
  
  $runner->runTestCase($testName, $format);
}
else
{
  $runner->runAllTests();
}

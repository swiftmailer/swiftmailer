<?php

error_reporting(E_ALL); ini_set('display_errors', true);

require_once dirname(__FILE__) . '/lib/simpletest/unit_tester.php';
require_once dirname(__FILE__) . '/lib/simpletest/mock_objects.php';
require_once dirname(__FILE__) . '/lib/simpletest/reporter.php';

$test_class = $argv[1];

require_once dirname(__FILE__) . '/unit/' .
  str_replace('_', '/', $test_class) . '.php';

$test = new $test_class();
$test->run(new TextReporter());

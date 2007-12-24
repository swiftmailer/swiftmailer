<?php

//Error reporting settings
error_reporting(E_ALL); ini_set('display_errors', true);

//Time limit to process all tests
set_time_limit(30);

//The path to the PHP command line executable
define('SWEETY_PHP_EXE', '/usr/bin/php-cli');
//The path to this file
define('SWEETY_HOME', dirname(__FILE__));
//The path to the libs being tested
define('SWEETY_INCLUDE_PATH',
  SWEETY_HOME . '/../lib' . PATH_SEPARATOR .
  SWEETY_HOME . '/expectations'
  );
//The path to the main test suite
define('SWEETY_LIB_PATH', SWEETY_HOME . '/lib');
//The path to simpletest
define('SWEETY_SIMPLETEST_PATH', SWEETY_LIB_PATH . '/simpletest');
//The path to any testing directories
define('SWEETY_TEST_PATH',
  SWEETY_HOME . '/unit' .
  PATH_SEPARATOR . SWEETY_HOME . '/acceptance'
  );
//Test locator strategies, separated by commas
define('SWEETY_TEST_LOCATOR', 'Sweety_TestLocator_PearStyleLocator');
//A pattern used for filtering out certain class names expected to be tests
define('SWEETY_IGNORED_CLASSES', '/(^|_)Abstract/');
//The name which appears at the top of the test suite
define('SWEETY_SUITE_NAME', 'Swift Mailer 4 Unit &amp; Acceptance Tests');
//The path to the template which renders the view
define('SWEETY_UI_TEMPLATE', SWEETY_HOME . '/templates/sweety/suite-ui.tpl.php');

//Most likely you won't want to modify the include_path
set_include_path(
  get_include_path() . PATH_SEPARATOR .
  SWEETY_LIB_PATH . PATH_SEPARATOR .
  SWEETY_INCLUDE_PATH . PATH_SEPARATOR .
  SWEETY_TEST_PATH
);

//Load in any dependencies
require_once 'Sweety/TestLocator/PearStyleLocator.php';

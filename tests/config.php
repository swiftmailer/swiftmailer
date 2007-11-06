<?php

define('LIB_PATH', dirname(__FILE__) . '/../lib');
define('SAMPLE_CLASS_PATH', dirname(__FILE__) . '/classes');
define('UNIT_TEST_DIR', dirname(__FILE__) . '/unit');

set_include_path(
  get_include_path() . PATH_SEPARATOR .
  LIB_PATH . PATH_SEPARATOR .
  SAMPLE_CLASS_PATH . PATH_SEPARATOR .
  UNIT_TEST_DIR
);

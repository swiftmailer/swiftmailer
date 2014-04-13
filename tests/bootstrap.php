<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;

require_once dirname(__DIR__) . '/vendor/autoload.php';

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__DIR__) . '/lib');

\Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

if (is_file(__DIR__ . '/acceptance.conf.php')) {
    require_once __DIR__ . '/acceptance.conf.php';
}
if (is_file(__DIR__ . '/smoke.conf.php')) {
    require_once __DIR__ . '/smoke.conf.php';
}
require_once __DIR__ . '/StreamCollector.php';
require_once __DIR__ . '/IdenticalBinaryConstraint.php';
require_once __DIR__ . '/SwiftMailerTestCase.php';
require_once __DIR__ . '/SwiftMailerSmokeTestCase.php';
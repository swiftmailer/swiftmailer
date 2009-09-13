<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * Autoloader and dependency injection initialization for Swift Mailer.
 */

//Indicate where Swift Mailer lib is found
defined('SWIFT_LIB_DIRECTORY')
  or define('SWIFT_LIB_DIRECTORY', dirname(__FILE__));

//Path to classes inside lib
define('SWIFT_CLASS_DIRECTORY', SWIFT_LIB_DIRECTORY . '/classes');

//Load Swift utility class
require_once SWIFT_CLASS_DIRECTORY . '/Swift.php';

//Start the autoloader
Swift::registerAutoload();

//Load the init script to set up dependency injection
require_once SWIFT_LIB_DIRECTORY . '/swift_init.php';

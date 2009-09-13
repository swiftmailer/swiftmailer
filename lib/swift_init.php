<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * Dependency injection initialization for Swift Mailer.
 */

//Define where the root of Swift Mailer is found
defined('SWIFT_LIB_DIRECTORY')
  or define('SWIFT_LIB_DIRECTORY', dirname(__FILE__));

//Define where dependency maps can be found
define('SWIFT_MAP_DIRECTORY', SWIFT_LIB_DIRECTORY . '/dependency_maps');

//Load in dependency maps
require_once SWIFT_MAP_DIRECTORY . '/cache_deps.php';
require_once SWIFT_MAP_DIRECTORY . '/mime_deps.php';
require_once SWIFT_MAP_DIRECTORY . '/transport_deps.php';

//Load in global library preferences
require_once SWIFT_LIB_DIRECTORY . '/preferences.php';

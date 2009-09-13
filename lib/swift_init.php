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

//Load in dependency maps
require_once dirname(__FILE__) . '/dependency_maps/cache_deps.php';
require_once dirname(__FILE__) . '/dependency_maps/mime_deps.php';
require_once dirname(__FILE__) . '/dependency_maps/transport_deps.php';

//Load in global library preferences
require_once dirname(__FILE__) . '/preferences.php';

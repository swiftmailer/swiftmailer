<?php

/*
 Dependency injection initialization for Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
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

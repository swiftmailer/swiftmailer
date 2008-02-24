<?php

/*
 Autoloader and dependency injector for Swift Mailer.
 
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

define('SWIFT_CLASS_DIRECTORY', dirname(__FILE__) . '/classes');
define('SWIFT_MAP_DIRECTORY', dirname(__FILE__) . '/dependency_maps');
require_once SWIFT_CLASS_DIRECTORY . '/Swift/Di.php';
Swift_Di::setClassPath(SWIFT_CLASS_DIRECTORY);
spl_autoload_register(array('Swift_Di', 'autoload'));
Swift_Di::getInstance()->registerDependencyMap(
  include(SWIFT_MAP_DIRECTORY . '/factory_deps.php')
  );
Swift_MimeFactory::getInstance()->registerDependencyMap(
  include(SWIFT_MAP_DIRECTORY . '/mime_deps.php')
  );
Swift_TransportFactory::getInstance()->registerDependencyMap(
  include(SWIFT_MAP_DIRECTORY . '/transport_deps.php')
  );

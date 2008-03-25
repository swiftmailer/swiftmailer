<?php

/*
 Content encoder change observer for Swift Mailer.
 
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
 
//@require 'Swift/Mime/ContentEncoder.php';

/**
 * Observes changes for a Mime entity's ContentEncoder.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
interface Swift_Mime_EncodingObserver
{
  
  /**
   * Notify this observer that the observed entity's ContentEncoder has changed.
   * @param Swift_Mime_ContentEncoder $encoder
   */
  public function encoderChanged(Swift_Mime_ContentEncoder $encoder);
  
}

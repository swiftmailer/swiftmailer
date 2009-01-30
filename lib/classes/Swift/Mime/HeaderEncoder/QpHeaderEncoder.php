<?php

/*
 The Quoted Printable header encoder in Swift Mailer.

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

require_once dirname(__FILE__) . '/../HeaderEncoder.php';
require_once dirname(__FILE__) . '/../../Encoder/QpEncoder.php';
require_once dirname(__FILE__) . '/../../CharacterStream.php';

/**
 * Handles Quoted Printable (Q) Header Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_HeaderEncoder_QpHeaderEncoder extends Swift_Encoder_QpEncoder
  implements Swift_Mime_HeaderEncoder
{

  private static $_headerSafeMap = array();

  /**
   * Creates a new QpHeaderEncoder for the given CharacterStream.
   * @param Swift_CharacterStream $charStream to use for reading characters
   */
  public function __construct(Swift_CharacterStream $charStream)
  {
    parent::__construct($charStream);
    if (empty(self::$_headerSafeMap))
    {
      foreach (array_merge(
        range(0x61, 0x7A), range(0x41, 0x5A),
        range(0x30, 0x39), array(0x20, 0x21, 0x2A, 0x2B, 0x2D, 0x2F)
        ) as $byte)
      {
        self::$_headerSafeMap[$byte] = chr($byte);
      }
    }
  }

  /**
   * Get the name of this encoding scheme.
   * Returns the string 'Q'.
   * @return string
   */
  public function getName()
  {
    return 'Q';
  }

  /**
   * Takes an unencoded string and produces a Q encoded string from it.
   * @param string $string to encode
   * @param int $firstLineOffset, optional
   * @param int $maxLineLength, optional, 0 indicates the default of 76 chars
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0)
  {
    return str_replace(array(' ', '=20', "=\r\n"), array('_', '_', "\r\n"),
      parent::encodeString($string, $firstLineOffset, $maxLineLength)
      );
  }

  // -- Overridden points of extension

  /**
   * Encode the given byte array into a verbatim QP form.
   * @param int[] $bytes
   * @return string
   * @access protected
   */
  protected function _encodeByteSequence(array $bytes, &$size)
  {
    $ret = '';
    $size=0;
    foreach ($bytes as $b)
    {
      if (isset(self::$_headerSafeMap[$b]))
      {
        $ret .= self::$_headerSafeMap[$b];
        ++$size;
      }
      else
      {
        $ret .= self::$_qpMap[$b];
        $size+=3;
      }
    }
    return $ret;
  }

}

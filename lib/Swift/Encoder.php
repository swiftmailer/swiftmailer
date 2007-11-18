<?php

/**
 * Interface for all Encoder schemes.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
interface Swift_Encoder
{
  
  /**
   * Encode a given string to produce an encoded string.
   * @param string $string
   * @param string $charset used
   * @param int $firstLineOffset if first line needs to be shorter
   * @return string
   */
  public function encodeString($string, $charset = null, $firstLineOffset = 0);
  
}

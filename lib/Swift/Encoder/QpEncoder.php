<?php

require_once dirname(__FILE__) . '/../Encoder.php';

/**
 * Handles Quoted Printable (QP) Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_Encoder_QpEncoder implements Swift_Encoder
{
  
  /**
   * Takes an unencoded string and produce a QP encoded string from it.
   * QP encoded strings have a maximum line length of 76 + 2 characters.
   * If the first line needs to be shorter, indicate the difference $firstLineOffset.
   * @param string $string to encode
   * @param int $firstLineOffset
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0)
  {
    $lines = explode("\r\n", $string);
    foreach ($lines as $i => $line)
    {
      $lastByte = ord(substr($line, -1));
      
      //RFC 2045, sect 6.7 (3)
      if (in_array($lastByte, array(0x09, 0x20)))
      {
        $line = substr($line, 0, -1) . sprintf('=%02X', $lastByte);
      }
      
      //RFC 2045, sect 6.7 (5)
      $encodedLine = '';
      $loop = false;
      do
      {
        if ($loop)
        {
          $encodedLine .= "=\r\n";
        }
        $encodedLine .= substr($line, 0, 76);
        $line = substr($line, 76);
        $loop = true;
      }
      while (0 != strlen($line));
      
      $lines[$i] = $encodedLine;
    }
    
    return implode("\r\n", $lines);
  }
  
}

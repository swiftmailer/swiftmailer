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
   * True if the multibyte encoding library is present.
   * @var boolean
   * @access private
   */
  private $_hasMb = false;
  
  /**
   * Creates a new QpEncoder.
   */
  public function __construct()
  {
    $this->_hasMb = (
      function_exists('mb_subtr')
      && function_exists('mb_strlen')
      && function_exists('mb_internal_encoding')
      );
  }
  
  /**
   * Takes an unencoded string and produce a QP encoded string from it.
   * QP encoded strings have a maximum line length of 76 *characters*.
   * If the first line needs to be shorter, indicate the difference with
   * $firstLineOffset.
   *
   * @param string $string to encode
   * @param string $charset used
   * @param int $firstLineOffset
   * @return string
   */
  public function encodeString($string, $charset = null, $firstLineOffset = 0)
  {
    $lines = explode("\r\n", $string);
    foreach ($lines as $i => $line)
    {
      //RFC 2045, sect 6.7 (5)
      $wrappedLines = array();
      do
      {
        $wrappedLines[] = $this->_substr($line, 0, 76, $charset);
        $line = $this->_substr($line, 76, null, $charset);
      }
      while (0 != strlen($line));
      
      foreach ($wrappedLines as $j => $wrappedLine)
      {
        $lastByte = ord(substr($wrappedLine, -1));
        
        //RFC 2045, sect 6.7 (3)
        if (in_array($lastByte, array(0x09, 0x20)))
        {
          $wrappedLine = substr($wrappedLine, 0, -1) . sprintf('=%02X', $lastByte);
        }
        
        $wrappedLines[$j] = $wrappedLine;
      }
      
      //RFC 2045, sect 6.7 (5)
      $lines[$i] = implode("=\r\n", $wrappedLines);
    }
    
    return implode("\r\n", $lines);
  }
  
  /**
   * Selective substr() which uses mb_substr() if possible.
   * @param string $string
   * @param int $start
   * @param int $length
   * @param string $encoding
   * @return string
   */
  private function _substr($string, $start, $length = null, $encoding = null)
  {
    if ($this->_hasMb)
    {
      if (is_null($length))
      {
        $length = mb_strlen($string);
      }
      
      if (is_null($encoding))
      {
        $encoding = mb_internal_encoding();
      }
      
      return mb_substr($string, $start, $length, $encoding);
    }
    else
    {
      if (is_null($length))
      {
        $length = strlen($string);
      }
      
      return substr($string, $start, $length);
    }
  }
  
}

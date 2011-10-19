<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__) . '/../HeaderEncoder.php';
require_once dirname(__FILE__) . '/../../Encoder/Base64Encoder.php';


/**
 * Handles Base64 (B) Header Encoding in Swift Mailer.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_HeaderEncoder_Base64HeaderEncoder
  extends Swift_Encoder_Base64Encoder
  implements Swift_Mime_HeaderEncoder
{
  
  /**
   * Get the name of this encoding scheme.
   * Returns the string 'B'.
   * @return string
   */
  public function getName()
  {
    return 'B';
  }
  
  /**
   * Takes an unencoded string and produces a Base64 encoded string from it.
   * If the charset is iso-2022-jp, it calls a special method, encodeJIS to 
   * convert the string, otherwise pass to the parent method.
   * @param string $string to encode
   * @param int $firstLineOffset
   * @param int $maxLineLength, optional, 0 indicates the default of 76 bytes
   * @param string $charset
   * @return string
   */
  public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0, $charset = 'utf-8')
  {
    $charset = strtolower($charset);
    if ($charset == 'iso-2022-jp')
    {
      $string = Swift_Charset::convertString($string, $charset);
      return $this->encodeJIS($string, $maxLineLength);
    }
    return parent::encodeString($string, $firstLineOffset, $maxLineLength);
  }
  
  /**
   * Copied some code from _sub_encode_base64 in mb-emulator.php
   * Since the encoding with JIS(iso-2022-jp) with base64 does not work propery with base64_encode,
   * this function convert the string properly.
   * @param string $string to encode
   * @param int $maxLineLength, optional, 0 indicates the default of 76 bytes
   * @return string
   */
  protected function encodeJIS($string, $maxLineLength)
  {
    $jis =  "(?:^|\x1B\(\x42)([\x01-\x1A,\x1C-\x7F]*)|(?:\x1B\\$\x42([\x01-\x1A,\x1C-\x7F]*))|(?:\x1B\(I([\x01-\x1A,\x1C-\x7F]*))";
    $linefeed = "\r\n";
    $max = preg_match_all('/'.$jis.'/', $string, $allchunks);

    $encodedString = '';
    $maxbytes = floor($maxLineLength * 3 / 4);
    $len = $maxbytes;
    $line = '';
    $needterminate = FALSE;
    for ($i = 0; $i < $max; ++$i)
    {
      if (ord($allchunks[1][$i]))
      { // alpha num
        if ($needterminate)
        {
          $line .= chr(0x1B) . '(B';
          $len -= 3;
        }
        $tmpstr = $allchunks[1][$i];
        $l = strlen($tmpstr);
        while ($l > $len)
        {
          $line .= substr($tmpstr, 0, $len);
          $encodedString .= base64_encode($line) . $linefeed;
          $l -= $len;
          $tmpstr = substr($tmpstr, $len);
          $len = $maxbytes;
          $line = '';
        }
        $line .= $tmpstr;
        $len -= $l;
        $needterminate = FALSE;
      }
      elseif (ord($allchunks[2][$i])) // kanji
      {
        $tmpstr = substr($allchunks[0][$i], 3);
        if ($len < 8)
        { // needs 8 bytes here to add extra char
          if ($needterminate)
          {
            $line .= chr(0x1B) . '(B';
          }
          $encodedString .= base64_encode($line) . $linefeed;
          $len = $maxbytes;
          $line = '';
        }
        $l = strlen($tmpstr);
        $line .= chr(0x1B) . '$B';
        $len -= 3;
        while ($l > $len - 3)
        {
          $add = floor(($len - 3) / 2) * 2;
          if ($add == 0)
          {
            break;
          }
          $line .= substr($tmpstr, 0, $add) . chr(0x1B) . '(B';
          $encodedString .= base64_encode($line) . $linefeed;
          $l -= $add;
          $tmpstr = substr($tmpstr, $add);
          $len = $maxbytes - 3;
          $line = chr(0x1B).'$B';
        }
        $line .= $tmpstr;
        $len -= $l;
        $needterminate = TRUE;

      }
      elseif (ord($allchunks[3][$i])) // hankaku kana
      {
        $tmpstr = $allchunks[3][$i];
        if ($len < 7) // needs 7 bytes here to add extra char
        {
          if ($needterminate)
          {
            $line .= chr(0x1B) . '(B';
          }
          $encodedString .= base64_encode($line) . $linefeed;
          $len = $maxbytes;
          $line = '';
        }
        $l = strlen($tmpstr);
        $line .= chr(0x1B) . '(I';
        $len -= 3;
        while ($l > $len - 3)
        {
          $line .= substr($tmpstr, 0, $len - 3) . chr(0x1B) . '(B';
          $encodedString .= base64_encode($line) . $linefeed;
          $l -= $len;
          $tmpstr = substr($tmpstr, $len - 3);
          $len = $maxbytes - 3;
          $line = chr(0x1B) . '(I';
        }
        $line .= $tmpstr;
        $len -= $l;
        $needterminate = TRUE;
      }
    }
    if ($needterminate)
    {
      $line .= chr(0x1B) . '(B';
    }
    $encodedString .= base64_encode($line);
    return $encodedString;
  }
  
}

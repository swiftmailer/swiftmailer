<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Provides helper method to convert charset text in headers and body
 *
 * @package Swift
 * @subpackage Charset
 * @author Shin Ohno
 */
class Swift_Charset
{
  
  /**
   * Encode charset when charset is not utf-8
   *
   * @param $string
   * @param $charset
   * @return string
   */
  public static function convertString($string, $charset = 'utf-8')
  {
    $charset = strtolower($charset);
    if (!in_array($charset, array('utf-8', 'iso-8859-1', "")))
    {
      // mb_convert_encoding must be the first one to check, since iconv cannot convert some words.
      if (function_exists('mb_convert_encoding'))
      {
        $string = mb_convert_encoding($string, $charset, 'utf-8');
      }
      else if (function_exists('iconv'))
      {
        $string = iconv($charset, 'utf-8//TRANSLIT//IGNORE', $string);
      }
      else
      {
        throw new Swift_CharsetException('No suitable convert encoding function (use UTF-8 as your charset or install the mbstring or iconv extension).');
      }
    }
    return $string;
  }
  
}


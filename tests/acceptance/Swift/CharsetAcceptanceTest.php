<?php

require_once 'swift_required.php';
require_once 'Swift/Charset.php';

class Swift_CharsetAcceptanceTest
  extends UnitTestCase
{

  public function testCharsetConvert()
  {
    $string = "長い長い長い長い長い長い長い長い長い長い長い長い長い長い長い長い長い長い長い長い件名";
    if (function_exists('mb_convert_encoding'))
    {
      $convertedString = Swift_Charset::convertString($string, "iso-2022-jp");
      $this->assertEqual(
        mb_convert_encoding($convertedString, 'utf-8', 'iso-2022-jp'), $string,
        'Convert string should decode back to original string for sample '
      );
    }
  }

}

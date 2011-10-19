<?php

require_once 'Swift/Mime/HeaderEncoder/Base64HeaderEncoder.php';
require_once 'Swift/Charset.php';
require_once 'Swift/ByteStream/ArrayByteStream.php';

class Swift_Mime_HeaderEncoder_Base64HeaderEncoderAcceptanceTest
  extends UnitTestCase
{
  
  private $_encoder;
  
  public function setUp()
  {
    $this->_encoder = new Swift_Mime_HeaderEncoder_Base64HeaderEncoder();
  }
  
  public function testEncodingJIS()
  {
    if (function_exists('mb_convert_encoding'))
    {
      // base64_encode and split cannot handle long JIS text to fold
      $subject = "長い長い長い長い長い長い長い長い長い長い長い長い長い長い長い長い長い長い長い長い件名";

      $encodedWrapperLength = strlen('=?iso-2022-jp?' . $this->_encoder->getName() . '??=');

      $encoded = $this->_encoder->encodeString($subject, 0, 75 - $encodedWrapperLength, 'iso-2022-jp');

      $decoded = str_replace("\r\n", "", $encoded);
      $base64Decoded = base64_decode($decoded);

      $this->assertEqual(
        mb_convert_encoding($base64Decoded, 'utf-8', 'iso-2022-jp'), $subject,
        'Encoded string should decode back to original string for sample '
      );
    }
  }
  
}

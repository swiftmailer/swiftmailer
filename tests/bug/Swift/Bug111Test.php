<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';

class Swift_Bug111Test extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testUnstructuredHeaderSlashesShouldNotBeEscaped()
  {
    $complicated_header = array(
      'to'=> array(
        'email1@example.com',
        'email2@example.com',
        'email3@example.com',
        'email4@example.com',
        'email5@example.com',
      ),
      'sub' => array(
        '-name-' => array(
          'email1',
          '"email2"',
          'email3\\',
          'email4',
          'email5',
        ),
        '-url-' => array(
          'http://google.com',
          'http://yahoo.com',
          'http://hotmail.com',
          'http://aol.com',
          'http://facebook.com',
        ),
      )
    );
    $json = json_encode($complicated_header);
    
    $message = new Swift_Message();
    $headers = $message->getHeaders();
    $headers->addTextHeader('X-SMTPAPI', $json);
    $header = $headers->get('X-SMTPAPI');
    
    $this->assertEqual('Swift_Mime_Headers_UnstructuredHeader', get_class($header));
    $this->assertEqual($json, $header->getFieldBody());
  }
  
}

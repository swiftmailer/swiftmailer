<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mime/Message.php';
require_once 'Swift/Mime/HeaderSet.php';
require_once 'Swift/Signers/DKIMSignerTest.php';

class Swift_Signers_DKIMSignerTest extends Swift_Tests_SwiftUnitTestCase
{
  
  public function testBasicSigning()
  {
    $headers=$this->_createHeaders();
    $message=$this->_createMessageWithByteCount($bytes);
    $signer=new Swift_Signers_DKIMSigner($privateKey, $domainName, $selector);
  }
  
  // -- Creation Methods
  /**
   * @return Swift_Mime_HeaderSet
   */
  private function _createHeaders()
  {
    return $this->_mock('Swift_Mime_HeaderSet');
  }
  
  private function _createMessageWithByteCount($bytes)
  {
    $this->_bytes = $bytes;
    $msg = $this->_mock('Swift_Mime_Message');
    $this->_checking(Expectations::create()
      -> ignoring($msg)->toByteStream(any()) -> calls(array($this, '_write'))
    );
    return $msg;
  }
  
  private function _createSendEvent($message)
  {
    $evt = $this->_mock('Swift_Events_SendEvent');
    $this->_checking(Expectations::create()
      -> ignoring($evt)->getMessage() -> returns($message)
      );
    return $evt;
  }
  
  private $_bytes = 0;
  public function _write($invocation)
  {
    $args = $invocation->getArguments();
    $is = $args[0];
    for ($i = 0; $i < $this->_bytes; ++$i)
    {
      $is->write('x');
    }
  }
  
}

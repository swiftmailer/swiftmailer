<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/Esmtp/AuthHandler.php';
require_once 'Swift/Transport/Esmtp/Authenticator.php';
require_once 'Swift/Transport/EsmtpBufferWrapper.php';

Mock::generate('Swift_Transport_Esmtp_Authenticator',
  'Swift_Transport_Esmtp_MockAuthenticator'
  );
Mock::generate('Swift_Transport_EsmtpBufferWrapper',
  'Swift_Transport_MockEsmtpBufferWrapper'
  );

class Swift_Transport_Esmtp_AuthHandlerTest extends Swift_Tests_SwiftUnitTestCase
{
  
  private $_buffer;
  
  public function setUp()
  {
    $this->_buffer = new Swift_Transport_MockEsmtpBufferWrapper();
  }
  
  public function testKeywordIsAuth()
  {
    $auth = $this->_getHandler(array());
    $this->assertEqual('AUTH', $auth->getHandledKeyword());
  }
  
  public function testUsernameCanBeSetAndFetched()
  {
    $auth = $this->_getHandler(array());
    $auth->setUsername('jack');
    $this->assertEqual('jack', $auth->getUsername());
  }
  
  public function testPasswordCanBeSetAndFetched()
  {
    $auth = $this->_getHandler(array());
    $auth->setPassword('pass');
    $this->assertEqual('pass', $auth->getPassword());
  }
  
  public function testMixinMethods()
  {
    $auth = $this->_getHandler(array());
    $mixins = $auth->exposeMixinMethods();
    $this->assertTrue(in_array('getUsername', $mixins),
      '%s: getUsername() should be accessible via mixin'
      );
    $this->assertTrue(in_array('setUsername', $mixins),
      '%s: setUsername() should be accessible via mixin'
      );
    $this->assertTrue(in_array('getPassword', $mixins),
      '%s: getPassword() should be accessible via mixin'
      );
    $this->assertTrue(in_array('setPassword', $mixins),
      '%s: setPassword() should be accessible via mixin'
      );
  }
  
  public function testAuthenticatorsAreCalledAccordingToParamsAfterEhlo()
  {
    $a1 = new Swift_Transport_Esmtp_MockAuthenticator();
    $a1->setReturnValue('getAuthKeyword', 'PLAIN');
    $a1->expectNever('authenticate');
    
    $a2 = new Swift_Transport_Esmtp_MockAuthenticator();
    $a2->setReturnValue('getAuthKeyword', 'LOGIN');
    $a2->expectOnce('authenticate', array($this->_buffer, 'jack', 'pass'));
    $a2->setReturnValue('authenticate', true, array($this->_buffer, 'jack', 'pass'));
    
    $auth = $this->_getHandler(array($a1, $a2));
    $auth->setUsername('jack');
    $auth->setPassword('pass');
    
    $auth->setKeywordParams(array('CRAM-MD5', 'LOGIN'));
    $auth->afterEhlo($this->_buffer);
  }
  
  public function testAuthenticatorsAreNotUsedIfNoUsernameSet()
  {
    $a1 = new Swift_Transport_Esmtp_MockAuthenticator();
    $a1->setReturnValue('getAuthKeyword', 'PLAIN');
    $a1->expectNever('authenticate');
    
    $a2 = new Swift_Transport_Esmtp_MockAuthenticator();
    $a2->setReturnValue('getAuthKeyword', 'LOGIN');
    $a2->expectNever('authenticate');
    
    $auth = $this->_getHandler(array($a1, $a2));
    
    $auth->setKeywordParams(array('CRAM-MD5', 'LOGIN'));
    $auth->afterEhlo($this->_buffer);
  }
  
  public function testSeveralAuthenticatorsAreTriedIfNeeded()
  {
    $a1 = new Swift_Transport_Esmtp_MockAuthenticator();
    $a1->setReturnValue('getAuthKeyword', 'PLAIN');
    $a1->expectOnce('authenticate', array($this->_buffer, 'jack', 'pass'));
    $a1->setReturnValue('authenticate', false);
    
    $a2 = new Swift_Transport_Esmtp_MockAuthenticator();
    $a2->setReturnValue('getAuthKeyword', 'LOGIN');
    $a2->expectOnce('authenticate', array($this->_buffer, 'jack', 'pass'));
    $a2->setReturnValue('authenticate', true);
    
    $a3 = new Swift_Transport_Esmtp_MockAuthenticator();
    $a3->setReturnValue('getAuthKeyword', 'CRAM-MD5');
    $a3->expectNever('authenticate');
    
    $auth = $this->_getHandler(array($a1, $a2, $a3));
    $auth->setUsername('jack');
    $auth->setPassword('pass');
    
    $auth->setKeywordParams(array('PLAIN', 'LOGIN'));
    $auth->afterEhlo($this->_buffer);
  }
  
  public function testFirstAuthenticatorToPassBreaksChain()
  {
    $a1 = new Swift_Transport_Esmtp_MockAuthenticator();
    $a1->setReturnValue('getAuthKeyword', 'PLAIN');
    $a1->expectOnce('authenticate', array($this->_buffer, 'jack', 'pass'));
    $a1->setReturnValue('authenticate', true);
    
    $a2 = new Swift_Transport_Esmtp_MockAuthenticator();
    $a2->setReturnValue('getAuthKeyword', 'LOGIN');
    $a2->expectNever('authenticate');
    
    $a3 = new Swift_Transport_Esmtp_MockAuthenticator();
    $a3->setReturnValue('getAuthKeyword', 'CRAM-MD5');
    $a3->expectNever('authenticate');
    
    $auth = $this->_getHandler(array($a1, $a2, $a3));
    $auth->setUsername('jack');
    $auth->setPassword('pass');
    
    $auth->setKeywordParams(array('PLAIN', 'LOGIN'));
    $auth->afterEhlo($this->_buffer);
  }
  
  // -- Private helpers
  
  private function _getHandler($authenticators)
  {
    return new Swift_Transport_Esmtp_AuthHandler($authenticators);
  }
  
}

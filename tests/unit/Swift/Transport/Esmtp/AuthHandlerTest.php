<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/Esmtp/AuthHandler.php';
require_once 'Swift/Transport/Esmtp/Authenticator.php';
require_once 'Swift/Transport/SmtpAgent.php';

class Swift_Transport_Esmtp_AuthHandlerTest
    extends Swift_Tests_SwiftUnitTestCase
{
    private $_agent;

    public function setUp()
    {
        $this->_agent = $this->_mock('Swift_Transport_SmtpAgent');
    }

    public function testKeywordIsAuth()
    {
        $auth = $this->_createHandler(array());
        $this->assertEqual('AUTH', $auth->getHandledKeyword());
    }

    public function testUsernameCanBeSetAndFetched()
    {
        $auth = $this->_createHandler(array());
        $auth->setUsername('jack');
        $this->assertEqual('jack', $auth->getUsername());
    }

    public function testPasswordCanBeSetAndFetched()
    {
        $auth = $this->_createHandler(array());
        $auth->setPassword('pass');
        $this->assertEqual('pass', $auth->getPassword());
    }

    public function testAuthModeCanBeSetAndFetched()
    {
        $auth = $this->_createHandler(array());
        $auth->setAuthMode('PLAIN');
        $this->assertEqual('PLAIN', $auth->getAuthMode());
    }

    public function testMixinMethods()
    {
        $auth = $this->_createHandler(array());
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
        $this->assertTrue(in_array('setAuthMode', $mixins),
            '%s: setAuthMode() should be accessible via mixin'
            );
        $this->assertTrue(in_array('getAuthMode', $mixins),
            '%s: getAuthMode() should be accessible via mixin'
            );
    }

    public function testAuthenticatorsAreCalledAccordingToParamsAfterEhlo()
    {
        $a1 = $this->_createMockAuthenticator('PLAIN');
        $a2 = $this->_createMockAuthenticator('LOGIN');

        $this->_checking(Expectations::create()
            -> never($a1)->authenticate($this->_agent, 'jack', 'pass')
            -> one($a2)->authenticate($this->_agent, 'jack', 'pass') -> returns(true)
            );

        $auth = $this->_createHandler(array($a1, $a2));
        $auth->setUsername('jack');
        $auth->setPassword('pass');

        $auth->setKeywordParams(array('CRAM-MD5', 'LOGIN'));
        $auth->afterEhlo($this->_agent);
    }

    public function testAuthenticatorsAreNotUsedIfNoUsernameSet()
    {
        $a1 = $this->_createMockAuthenticator('PLAIN');
        $a2 = $this->_createMockAuthenticator('LOGIN');

        $this->_checking(Expectations::create()
            -> never($a1)->authenticate($this->_agent, 'jack', 'pass')
            -> never($a2)->authenticate($this->_agent, 'jack', 'pass') -> returns(true)
            );

        $auth = $this->_createHandler(array($a1, $a2));

        $auth->setKeywordParams(array('CRAM-MD5', 'LOGIN'));
        $auth->afterEhlo($this->_agent);
    }

    public function testSeveralAuthenticatorsAreTriedIfNeeded()
    {
        $a1 = $this->_createMockAuthenticator('PLAIN');
        $a2 = $this->_createMockAuthenticator('LOGIN');

        $this->_checking(Expectations::create()
            -> one($a1)->authenticate($this->_agent, 'jack', 'pass') -> returns(false)
            -> one($a2)->authenticate($this->_agent, 'jack', 'pass') -> returns(true)
            );

        $auth = $this->_createHandler(array($a1, $a2));
        $auth->setUsername('jack');
        $auth->setPassword('pass');

        $auth->setKeywordParams(array('PLAIN', 'LOGIN'));
        $auth->afterEhlo($this->_agent);
    }

    public function testFirstAuthenticatorToPassBreaksChain()
    {
        $a1 = $this->_createMockAuthenticator('PLAIN');
        $a2 = $this->_createMockAuthenticator('LOGIN');
        $a3 = $this->_createMockAuthenticator('CRAM-MD5');

        $this->_checking(Expectations::create()
            -> one($a1)->authenticate($this->_agent, 'jack', 'pass') -> returns(false)
            -> one($a2)->authenticate($this->_agent, 'jack', 'pass') -> returns(true)
            -> never($a3)->authenticate($this->_agent, 'jack', 'pass')
            );

        $auth = $this->_createHandler(array($a1, $a2));
        $auth->setUsername('jack');
        $auth->setPassword('pass');

        $auth->setKeywordParams(array('PLAIN', 'LOGIN', 'CRAM-MD5'));
        $auth->afterEhlo($this->_agent);
    }

    // -- Private helpers

    private function _createHandler($authenticators)
    {
        return new Swift_Transport_Esmtp_AuthHandler($authenticators);
    }

    private function _createMockAuthenticator($type)
    {
        $authenticator = $this->_mock('Swift_Transport_Esmtp_Authenticator');
        $this->_checking(Expectations::create()
            -> ignoring($authenticator)->getAuthKeyword() -> returns($type)
            );
        return $authenticator;
    }
}

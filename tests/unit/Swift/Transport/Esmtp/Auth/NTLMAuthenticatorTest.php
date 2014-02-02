<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Transport/SmtpAgent.php';
require_once 'Swift/Transport/Esmtp/Auth/PlainAuthenticator.php';
require_once 'Swift/TransportException.php';

class Swift_Transport_Esmtp_Auth_NTLMAuthenticatorTest extends Swift_Tests_SwiftUnitTestCase
{
    private $_agent;

    private $_message1 = "4e544c4d535350000100000007020000";
    private $_message2 = "4e544c4d53535000020000000c000c003000000035828980514246973ea892c10000000000000000460046003c00000054004500530054004e00540002000c0054004500530054004e00540001000c004d0045004d0042004500520003001e006d0065006d006200650072002e0074006500730074002e0063006f006d0000000000";
    private $_message3 = "4e544c4d5353500003000000180018006000000076007600780000000c000c0040000000080008004c0000000c000c0054000000000000009a0000000102000054004500530054004e00540074006500730074004d0045004d00420045005200bf2e015119f6bdb3f6fdb768aa12d478f5ce3d2401c8f6e9caa4da8f25d5e840974ed8976d3ada46010100000000000030fa7e3c677bc301f5ce3d2401c8f6e90000000002000c0054004500530054004e00540001000c004d0045004d0042004500520003001e006d0065006d006200650072002e0074006500730074002e0063006f006d000000000000000000";

    public function setUp()
    {
        $this->skipIf(!function_exists('mcrypt_module_open') || !function_exists('openssl_random_pseudo_bytes') || !function_exists('bcmul'));

        $this->_agent = $this->_mock('Swift_Transport_SmtpAgent');
    }

    public function testKeywordIsNtlm()
    {
        $login = $this->_getAuthenticator();
        $this->assertEqual('NTLM', $login->getAuthKeyword());
    }

    public function testMessage1Generator()
    {
        $login = $this->_getAuthenticator();
        $message1 = $this->_invokePrivateMethod('createMessage1', $login);

        $this->assertEqual($this->_message1, bin2hex($message1),
            '%s: We send the smallest ntlm message which should never fail.'
        );
    }

    public function testLMv1Generator()
    {
        $password = "test1234";
        $challenge = "b019d38bad875c9d";
        $lmv1 = "1879f60127f8a877022132ec221bcbf3ca016a9f76095606";

        $login = $this->_getAuthenticator();
        $lmv1Result = $this->_invokePrivateMethod('createLMPassword', $login, array($password, $this->hex2bin($challenge)));

        $this->assertEqual($lmv1, bin2hex($lmv1Result),
            '%s: The keys should be the same cause we use the same values to generate them.'
        );
    }

    public function testLMv2Generator()
    {
        $username = "user";
        $password = "SecREt01";
        $domain = "DOMAIN";
        $challenge = "0123456789abcdef";
        $lmv2 = "d6e6152ea25d03b7c6ba6629c2d6aaf0ffffff0011223344";

        $login = $this->_getAuthenticator();
        $lmv2Result = $this->_invokePrivateMethod('createLMv2Password', $login, array($password, $username, $domain, $this->hex2bin($challenge), $this->hex2bin("ffffff0011223344")));

        $this->assertEqual($lmv2, bin2hex($lmv2Result),
            '%s: The keys should be the same cause we use the same values to generate them.'
        );
    }

    public function testNTLMv1Generator()
    {
        $password = "test1234";
        $challenge = "b019d38bad875c9d";
        $ntlm = "e6285df3287c5d194f84df1a94817c7282d09754b6f9e02a";

        $login = $this->_getAuthenticator();
        $ntlmResult = $this->_invokePrivateMethod('createNTLMPassword', $login, array($password, $this->hex2bin($challenge)));

        $this->assertEqual($ntlm, bin2hex($ntlmResult),
            '%s: The keys should be the same cause we use the same values to generate them.'
        );
    }

    public function testNTLMv2Generator()
    {
        $username = "user";
        $password = "SecREt01";
        $domain = "DOMAIN";
        $challenge = "0123456789abcdef";
        $targetInfo = "02000c0044004f004d00410049004e0001000c005300450052005600450052000400140064006f006d00610069006e002e0063006f006d00030022007300650072007600650072002e0064006f006d00610069006e002e0063006f006d0000000000";
        $timestamp = "0090d336b734c301";
        $ntlm2 = "cbabbca713eb795d04c97abc01ee498301010000000000000090d336b734c301ffffff00112233440000000002000c0044004f004d00410049004e0001000c005300450052005600450052000400140064006f006d00610069006e002e0063006f006d00030022007300650072007600650072002e0064006f006d00610069006e002e0063006f006d000000000000000000";

        $login = $this->_getAuthenticator();
        $ntlmResult = $this->_invokePrivateMethod('createNTLMv2Hash', $login, array($password, $username, $domain, $this->hex2bin($challenge), $this->hex2bin($targetInfo), $this->hex2bin($timestamp), $this->hex2bin("ffffff0011223344")));

        $this->assertEqual($ntlm2, bin2hex($ntlmResult),
            '%s: The keys should be the same cause we use the same values to generate them.'
        );
    }

    public function testMessage3v1Generator()
    {
        $username = "test";
        $domain = "TESTNT";
        $workstation = "MEMBER";
        $lmResponse = "1879f60127f8a877022132ec221bcbf3ca016a9f76095606";
        $ntlmResponse = "e6285df3287c5d194f84df1a94817c7282d09754b6f9e02a";
        $message3T = "4e544c4d5353500003000000180018006000000018001800780000000c000c0040000000080008004c0000000c000c0054000000000000009a0000000102000054004500530054004e00540074006500730074004d0045004d004200450052001879f60127f8a877022132ec221bcbf3ca016a9f76095606e6285df3287c5d194f84df1a94817c7282d09754b6f9e02a";

        $login = $this->_getAuthenticator();
        $message3 = $this->_invokePrivateMethod('createMessage3', $login, array($domain, $username, $workstation, $this->hex2bin($lmResponse), $this->hex2bin($ntlmResponse)));

        $this->assertEqual($message3T, bin2hex($message3),
            '%s: We send the same information as the example is created with so this should be the same'
        );
    }

    public function testMessage3v2Generator()
    {
        $username = "test";
        $domain = "TESTNT";
        $workstation = "MEMBER";
        $lmResponse = "bf2e015119f6bdb3f6fdb768aa12d478f5ce3d2401c8f6e9";
        $ntlmResponse = "caa4da8f25d5e840974ed8976d3ada46010100000000000030fa7e3c677bc301f5ce3d2401c8f6e90000000002000c0054004500530054004e00540001000c004d0045004d0042004500520003001e006d0065006d006200650072002e0074006500730074002e0063006f006d000000000000000000";

        $login = $this->_getAuthenticator();
        $message3 = $this->_invokePrivateMethod('createMessage3', $login, array($domain, $username, $workstation, $this->hex2bin($lmResponse), $this->hex2bin($ntlmResponse)));

        $this->assertEqual($this->_message3, bin2hex($message3),
            '%s: We send the same information as the example is created with so this should be the same'
        );
    }

    public function testGetDomainAndUsername()
    {
        $username = "DOMAIN\user";

        $login = $this->_getAuthenticator();
        list($domain, $user) = $this->_invokePrivateMethod('getDomainAndUsername', $login, array($username));

        $this->assertEqual('DOMAIN', $domain,
            '%s: the fetched domain did not match'
        );
        $this->assertEqual('user', $user,
            '%s: the fetched user did not match'
        );
    }

    public function testGetDomainAndUsernameWithExtension()
    {
        $username = "domain.com\user";

        $login = $this->_getAuthenticator();
        list($domain, $user) = $this->_invokePrivateMethod('getDomainAndUsername', $login, array($username));

        $this->assertEqual('domain.com', $domain,
            '%s: the fetched domain did not match'
        );
        $this->assertEqual('user', $user,
            '%s: the fetched user did not match'
        );
    }

    public function testGetDomainAndUsernameWithAtSymbol()
    {
        $username = "user@DOMAIN";

        $login = $this->_getAuthenticator();
        list($domain, $user) = $this->_invokePrivateMethod('getDomainAndUsername', $login, array($username));

        $this->assertEqual('DOMAIN', $domain,
            '%s: the fetched domain did not match'
        );
        $this->assertEqual('user', $user,
            '%s: the fetched user did not match'
        );
    }

    public function testGetDomainAndUsernameWithAtSymbolAndExtension()
    {
        $username = "user@domain.com";

        $login = $this->_getAuthenticator();
        list($domain, $user) = $this->_invokePrivateMethod('getDomainAndUsername', $login, array($username));

        $this->assertEqual('domain.com', $domain,
            '%s: the fetched domain did not match'
        );
        $this->assertEqual('user', $user,
            '%s: the fetched user did not match'
        );
    }

    public function testSuccessfulAuthentication()
    {
        $domain = "TESTNT";
        $username = "test";
        $secret = "test1234";

        $ntlm = $this->_getAuthenticator();
        $this->_checking(Expectations::create()
                ->one($this->_agent)->executeCommand('AUTH NTLM ' . base64_encode(
                        $this->_invokePrivateMethod('createMessage1', $ntlm)
                    ) . "\r\n", array(334))
                ->returns("334 " . base64_encode($this->hex2bin("4e544c4d53535000020000000c000c003000000035828980514246973ea892c10000000000000000460046003c00000054004500530054004e00540002000c0054004500530054004e00540001000c004d0045004d0042004500520003001e006d0065006d006200650072002e0074006500730074002e0063006f006d0000000000")))

                ->one($this->_agent)->executeCommand(base64_encode(
                        $this->_invokePrivateMethod('createMessage3', $ntlm, array($domain, $username, $this->hex2bin("4d0045004d00420045005200"), $this->hex2bin("bf2e015119f6bdb3f6fdb768aa12d478f5ce3d2401c8f6e9"), $this->hex2bin("caa4da8f25d5e840974ed8976d3ada46010100000000000030fa7e3c677bc301f5ce3d2401c8f6e90000000002000c0054004500530054004e00540001000c004d0045004d0042004500520003001e006d0065006d006200650072002e0074006500730074002e0063006f006d000000000000000000"))
                        )) . "\r\n", array(235))
        );

        $this->assertTrue($ntlm->authenticate($this->_agent, $username . '@' . $domain, $secret, $this->hex2bin("30fa7e3c677bc301"), $this->hex2bin("f5ce3d2401c8f6e9")),
            '%s: The buffer accepted all commands authentication should succeed'
        );
    }

    public function testAuthenticationFailureSendRsetAndReturnFalse()
    {
        $domain = "TESTNT";
        $username = "test";
        $secret = "test1234";

        $ntlm = $this->_getAuthenticator();
        $this->_checking(Expectations::create()
                ->one($this->_agent)->executeCommand('AUTH NTLM ' . base64_encode(
                        $this->_invokePrivateMethod('createMessage1', $ntlm)
                    ) . "\r\n", array(334))
                -> throws(new Swift_TransportException(""))

                ->one($this->_agent)->executeCommand("RSET\r\n", array(250))
        );

        $this->assertFalse($ntlm->authenticate($this->_agent, $username . '@' . $domain, $secret, $this->hex2bin("30fa7e3c677bc301"), $this->hex2bin("f5ce3d2401c8f6e9")),
            '%s: Authentication fails, so RSET should be sent'
        );
    }

    // -- Private helpers
    private function _getAuthenticator()
    {
        return new Swift_Transport_Esmtp_Auth_NTLMAuthenticator();
    }

    private function _invokePrivateMethod($method, $instance, array $args = array())
    {
        $methodC = new ReflectionMethod($instance, trim($method));
        $methodC->setAccessible(true);

        return $methodC->invokeArgs($instance, $args);
    }

    /**
     * Hex2bin replacement for < PHP 5.4
     * @param string $hex
     * @return string Binary
     */
    protected function hex2bin($hex)
    {
        return function_exists('hex2bin') ? hex2bin($hex) : pack('H*', $hex);
    }
}

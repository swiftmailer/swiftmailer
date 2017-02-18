<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * DKIM Signer used to apply DKIM Signature to a message.
 * DKIM is the further development of DomainKey. This class obsoletes DomainKeySigner.php.
 * This class follows RFC6376.
 *
 * @author Xavier De Cock <xdecock@gmail.com>
 * @author Ludwig Grill (www.rotzbua.de)
 */
class Swift_Signers_DKIMSigner implements Swift_Signers_HeaderSigner
{
    /**
     * PrivateKey.
     *
     * @var string
     */
    protected $_privateKey;

    /**
     * DomainName.
     *
     * @var string
     */
    protected $_domainName;

    /**
     * Selector.
     *
     * @var string
     */
    protected $_selector;

    /**
     * Hash algorithm used.
     *
     * @see RFC6376 3.3: Signers MUST implement and SHOULD sign using rsa-sha256.
     *
     * @var string
     */
    protected $_hashAlgorithm = 'rsa-sha256';
    
    /**
     * Contains openssl representation of $_hashAlgorithm.
     * Is only set by setHashAlgorithm().
     * 
     * @var int
     */
    protected $_hashAlgorithmOpenssl = -1;

    /**
     * Body canon method.
     *
     * @var string
     */
    protected $_bodyCanon = 'simple';

    /**
     * Header canon method.
     *
     * @var string
     */
    protected $_headerCanon = 'simple';

    /**
     * Headers not being signed.
     * 
     * @see rfc6376 - 5.4.1. Recommended Signature Content
     *
     * @var array
     */
    protected $_ignoredHeaders = array('return-path' => true, // rfc6376
                                       'received' => true, // rfc6376
                                       'comments' => true, // rfc6376
                                       'keywords' => true, // rfc6376
                                       'authentication-results' => true // good practice recommendation
    );

    /**
     * Signer identity.
     *
     * @var string
     */
    protected $_signerIdentity;

    /**
     * BodyLength.
     *
     * @var int
     */
    protected $_bodyLen = 0;

    /**
     * Maximum signedLen.
     *
     * @var int
     */
    protected $_maxLen = PHP_INT_MAX;

    /**
     * Embbed bodyLen in signature.
     *
     * @var bool
     */
    protected $_showLen = false;

    /**
     * When the signature has been applied.
     * If integer is set, value is used.
     * If false means no timestamp is embedded.
     *
     * @var bool|int
     */
    protected $_signatureTimestamp = true;

    /**
     * When the signature will expires.
     * If integer is set, value is used.
     * If false means no timestamp embedded.
     *
     * @var bool|int
     */
    protected $_signatureExpiration = false;

    /**
     * Must we embed signed headers?
     *
     * @var bool
     */
    protected $_debugHeaders = false;

    // work variables
    /**
     * Headers used to generate hash.
     *
     * @var array
     */
    protected $_signedHeaders = array();

    /**
     * If debugHeaders is set store debugData here.
     *
     * @var string
     */
    private $_debugHeadersData = '';

    /**
     * Stores the bodyHash.
     *
     * @var string
     */
    private $_bodyHash = '';

    /**
     * Stores the signature header.
     *
     * @var Swift_Mime_Headers_ParameterizedHeader
     */
    protected $_dkimHeader;

    private $_bodyHashHandler;

    private $_headerHash;

    private $_headerCanonData = '';

    private $_bodyCanonEmptyCounter = 0;

    private $_bodyCanonIgnoreStart = 2;

    private $_bodyCanonSpace = false;

    private $_bodyCanonLastChar = null;

    private $_bodyCanonLine = '';

    private $_bound = array();

    /**
     * Constructor.
     *
     * @param string $privateKey RSA: >=1024bit
     * @param string $domainName
     * @param string $selector
     */
    public function __construct($privateKey, $domainName, $selector)
    {
        $this->_privateKey = $privateKey;
        $this->_domainName = $domainName;
        $this->_signerIdentity = '@'.$domainName;
        $this->_selector = $selector;

        // keep fallback hash algorithm sha1 if php version is lower than 5.4.8
        if (PHP_VERSION_ID < 50408) {
            $this->_hashAlgorithm = 'rsa-sha1';
        }
    }

    /**
     * Instanciate DKIMSigner.
     *
     * @param string $privateKey RSA: >=1024bit
     * @param string $domainName
     * @param string $selector
     *
     * @return self
     */
    public static function newInstance($privateKey, $domainName, $selector)
    {
        return new static($privateKey, $domainName, $selector);
    }

    /**
     * Reset the Signer.
     *
     * @see Swift_Signer::reset()
     */
    public function reset()
    {
        $this->_headerHash = null;
        $this->_signedHeaders = array();
        $this->_bodyHash = null;
        $this->_bodyHashHandler = null;
        $this->_bodyCanonIgnoreStart = 2;
        $this->_bodyCanonEmptyCounter = 0;
        $this->_bodyCanonLastChar = null;
        $this->_bodyCanonSpace = false;
    }

    /**
     * Writes $bytes to the end of the stream.
     *
     * Writing may not happen immediately if the stream chooses to buffer.  If
     * you want to write these bytes with immediate effect, call {@link commit()}
     * after calling write().
     *
     * This method returns the sequence ID of the write (i.e. 1 for first, 2 for
     * second, etc etc).
     *
     * @param string $bytes
     *
     * @throws Swift_IoException
     *
     * @return int
     */
    // TODO fix return
    public function write($bytes)
    {
        $this->_canonicalizeBody($bytes);
        foreach ($this->_bound as $is) {
            $is->write($bytes);
        }
    }

    /**
     * For any bytes that are currently buffered inside the stream, force them
     * off the buffer.
     */
    public function commit()
    {
        // Nothing to do
        return;
    }

    /**
     * Attach $is to this stream.
     * The stream acts as an observer, receiving all data that is written.
     * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
     *
     * @param Swift_InputByteStream $is
     */
    public function bind(Swift_InputByteStream $is)
    {
        // Don't have to mirror anything
        $this->_bound[] = $is;

        return;
    }

    /**
     * Remove an already bound stream.
     * If $is is not bound, no errors will be raised.
     * If the stream currently has any buffered data it will be written to $is
     * before unbinding occurs.
     *
     * @param Swift_InputByteStream $is
     */
    public function unbind(Swift_InputByteStream $is)
    {
        // Don't have to mirror anything
        foreach ($this->_bound as $k => $stream) {
            if ($stream === $is) {
                unset($this->_bound[$k]);

                return;
            }
        }
    }

    /**
     * Flush the contents of the stream (empty it) and set the internal pointer
     * to the beginning.
     *
     * @throws Swift_IoException
     */
    public function flushBuffers()
    {
        $this->reset();
    }

    /**
     * Set and initialise hash algorithm, must be one of 'rsa-sha1' or 'rsa-sha256'.
     *
     * @param string $hash 'rsa-sha1' or 'rsa-sha256'
     *
     * @throws Swift_SwiftException
     *
     * @return $this
     */
    public function setHashAlgorithm($hash)
    {
        switch ($hash) {
            case 'rsa-sha1':
                $this->_hashAlgorithm = 'rsa-sha1';
                $this->_bodyHashHandler = hash_init('sha1');
                $this->_hashAlgorithmOpenssl = OPENSSL_ALGO_SHA1;
                break;
            case 'rsa-sha256':
                if (!defined('OPENSSL_ALGO_SHA256')) {
                    // should be only thrown by php versions below 5.4.8
                    throw new Swift_SwiftException('Unable to set sha256 as it is not supported by OpenSSL.');
                }
                $this->_hashAlgorithm = 'rsa-sha256';
                $this->_bodyHashHandler = hash_init('sha256');
                $this->_hashAlgorithmOpenssl = OPENSSL_ALGO_SHA256;
                break;
            default:
                throw new Swift_SwiftException('Unable to set the hash algorithm, must be one of rsa-sha1 or rsa-sha256 (%s given).', $hash);
        }

        return $this;
    }

    /**
     * Set the body canonicalization algorithm.
     *
     * @param string $canon
     *
     * @throws Swift_SwiftException
     *
     * @return $this
     */
    public function setBodyCanon($canon)
    {
        switch ($canon) {
            case 'simple':
                $this->_bodyCanon = 'simple';
                break;
            case 'relaxed':
                $this->_bodyCanon = 'relaxed';
                break;
            default:
                throw new Swift_SwiftException('Unable to set the body canon, must be one of simple or relaxed (%s given).', $canon);
        }

        return $this;
    }

    /**
     * Set the header canonicalization algorithm.
     *
     * @param string $canon
     * 
     * @throws Swift_SwiftException
     * 
     * @return $this
     */
    public function setHeaderCanon($canon)
    {
        switch ($canon) {
            case 'simple':
                $this->_headerCanon = 'simple';
                break;
            case 'relaxed':
                $this->_headerCanon = 'relaxed';
                break;
            default:
                throw new Swift_SwiftException('Unable to set the header canon, must be one of simple or relaxed (%s given).', $canon);
        }

        return $this;
    }

    /**
     * Set the signer identity.
     *
     * @param string $identity
     *
     * @return $this
     */
    public function setSignerIdentity($identity)
    {
        $this->_signerIdentity = $identity;

        return $this;
    }

    /**
     * Set the length of the body to sign.
     *
     * @param mixed $len (bool or int)
     *
     * @return $this
     */
    public function setBodySignedLen($len)
    {
        if ($len === true) {
            $this->_showLen = true;
            $this->_maxLen = PHP_INT_MAX;
        } elseif ($len === false) {
            $this->_showLen = false;
            $this->_maxLen = PHP_INT_MAX;
        } else {
            $this->_showLen = true;
            $this->_maxLen = (int) $len;
        }

        return $this;
    }

    /**
     * Set the signature timestamp.
     * If true actual time is used.
     * If false no timestamp will be set.
     * Timestamp in the future are not recommended.
     *
     * @param bool|int $time De-/Activate|A timestamp
     *    
     * @throws Swift_SwiftException
     *
     * @return $this
     */
    public function setSignatureTimestamp($time)
    {
        if (!(is_bool($time) || (is_int($time) && 0 < $time))) {
            throw new Swift_SwiftException('Unable to set the signature timestamp (' . $time . ' given).');
        }
        if (!(is_bool($time) || $this->_signatureExpiration === false || ($this->_signatureExpiration !== false && $time < $this->_signatureExpiration))) {
            throw new Swift_SwiftException('Signature timestamp must be less than expiration timestamp.');
        }
        $this->_signatureTimestamp = $time;

        return $this;
    }

    /**
     * Set the signature expiration timestamp.
     * If true actual time + delta is used.
     * If false no timestamp will be set.
     *
     * @param bool|int $time De-/Activate|A timestamp
     *
     * @throws Swift_SwiftException
     *
     * @return $this
     */
    public function setSignatureExpiration($time)
    {
        if ($time === true) {
            $time = time() + 60 * 60 * 24 * 30; // dkim signature for 30 days valid
        }
        if (!(is_bool($time) || (is_int($time) && 0 < $time))) {
            throw new Swift_SwiftException('Unable to set the expiration timestamp (' . $time . ' given).');
        }
        if (!(is_bool($time) || $this->_signatureTimestamp === false || ($this->_signatureTimestamp !== false && $this->_signatureTimestamp < $time))) {
            throw new Swift_SwiftException('Expiration timestamp must be grater than signature timestamp.');
        }
        $this->_signatureExpiration = $time;

        return $this;
    }

    /**
     * Enable / disable the DebugHeaders.
     *
     * @param bool $debug
     *
     * @return Swift_Signers_DKIMSigner
     */
    public function setDebugHeaders($debug)
    {
        $this->_debugHeaders = (bool) $debug;

        return $this;
    }

    /**
     * Start Body.
     */
    public function startBody()
    {
        // Init hash algorithm
        $this->setHashAlgorithm($this->_hashAlgorithm);
        $this->_bodyCanonLine = '';
    }

    /**
     * End Body.
     */
    public function endBody()
    {
        $this->_endOfBody();
    }

    /**
     * Returns the list of Headers Tampered by this plugin.
     *
     * @return array
     */
    public function getAlteredHeaders()
    {
        if ($this->_debugHeaders) {
            return array('DKIM-Signature', 'X-DebugHash');
        } else {
            return array('DKIM-Signature');
        }
    }

    /**
     * Adds an ignored Header.
     *
     * @param string $header_name
     *
     * @return Swift_Signers_DKIMSigner
     */
    public function ignoreHeader($header_name)
    {
        $this->_ignoredHeaders[strtolower($header_name)] = true;

        return $this;
    }

    /**
     * Set the headers to sign.
     *
     * @param Swift_Mime_HeaderSet $headers
     *
     * @return Swift_Signers_DKIMSigner
     */
    public function setHeaders(Swift_Mime_HeaderSet $headers)
    {
        $this->_headerCanonData = '';
        // Loop through Headers
        $listHeaders = $headers->listAll();
        foreach ($listHeaders as $hName) {
            // Check if we need to ignore Header
            if (!isset($this->_ignoredHeaders[strtolower($hName)])) {
                if ($headers->has($hName)) {
                    $tmp = $headers->getAll($hName);
                    foreach ($tmp as $header) {
                        if ($header->getFieldBody() != '') {
                            $this->_addHeader($header->toString());
                            $this->_signedHeaders[] = $header->getFieldName();
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Add the signature to the given Headers.
     *
     * @param Swift_Mime_HeaderSet $headers
     * @throws Swift_SwiftException
     * @return Swift_Signers_DKIMSigner
     */
    public function addSignature(Swift_Mime_HeaderSet $headers)
    {
        // Prepare the DKIM-Signature
        $params = array('v' => '1', // required
                        'a' => $this->_hashAlgorithm, // required
                        'bh' => base64_encode($this->_bodyHash), // required
                        'd' => $this->_domainName, // required
                        'h' => implode(':', $this->_signedHeaders), // required
                        'i' => $this->_signerIdentity, // optional
                        's' => $this->_selector // required
        );
        // optional, 'simple' is default, only if canon is different add parameter
        if ($this->_bodyCanon != 'simple') {
            $params['c'] = $this->_headerCanon . '/' . $this->_bodyCanon;
        } elseif ($this->_headerCanon != 'simple') {
            $params['c'] = $this->_headerCanon;
        }
        // optional
        if ($this->_showLen) {
            $params['l'] = $this->_bodyLen;
        }
        // optional
        if ($this->_signatureTimestamp !== false) {
            if ($this->_signatureTimestamp === true) {
                $params['t'] = time(); // actual time
            } else {
                $params['t'] = $this->_signatureTimestamp;
            }
        }
        // optional
        if ($this->_signatureExpiration !== false) {
            if ($this->_signatureExpiration === true) {
                $params['x'] = time() + 60 * 60 * 24 * 30; // dkim signature for 30 days valid
            } else {
                $params['x'] = $this->_signatureExpiration;
            }
        }
        // check timestamps, expiration must be after signing
        if (isset($params['t']) && isset($params['x']) && $params['t'] < $params['x']) {
            throw new Swift_SwiftException('Expiration timestamp must be higher than signature timestamp');
        }
        // optional
        if ($this->_debugHeaders) {
            $params['z'] = implode('|', $this->_debugHeadersData);
        }
        
        // concat signature
        $string = '';
        foreach ($params as $k => $v) {
            $string .= $k.'='.$v.'; ';
        }
        $string = trim($string);
        $headers->addTextHeader('DKIM-Signature', $string);
        // Add the last DKIM-Signature
        $tmp = $headers->getAll('DKIM-Signature');
        $this->_dkimHeader = end($tmp);
        $this->_addHeader(trim($this->_dkimHeader->toString())."\r\n b=", true);
        $this->_endOfHeaders();
        if ($this->_debugHeaders) {
            $headers->addTextHeader('X-DebugHash', base64_encode($this->_headerHash));
        }
        $this->_dkimHeader->setValue($string.' b='.trim(chunk_split(base64_encode($this->_getEncryptedHash()), 73, ' ')));

        return $this;
    }

    /* Private helpers */

    protected function _addHeader($header, $is_sig = false)
    {
        switch ($this->_headerCanon) {
            case 'simple':
                // Nothing to do
                break;
            case 'relaxed':
                // Prepare Header and cascade
                $exploded = explode(':', $header, 2);
                $name = strtolower(trim($exploded[0]));
                $value = str_replace("\r\n", '', $exploded[1]);
                $value = preg_replace("/[ \t][ \t]+/", ' ', $value);
                $header = $name.':'.trim($value).($is_sig ? '' : "\r\n");
                break;
        }
        $this->_addToHeaderHash($header);
    }

    /**
     * @deprecated This method is currently useless in this class but it must be
     *             kept for BC reasons due to its "protected" scope. This method
     *             might be overridden by custom client code.
     */
    protected function _endOfHeaders()
    {
    }

    protected function _canonicalizeBody($string)
    {
        $len = strlen($string);
        $canon = '';
        $method = ($this->_bodyCanon == 'relaxed');
        for ($i = 0; $i < $len; ++$i) {
            if ($this->_bodyCanonIgnoreStart > 0) {
                --$this->_bodyCanonIgnoreStart;
                continue;
            }
            switch ($string[$i]) {
                case "\r":
                    $this->_bodyCanonLastChar = "\r";
                    break;
                case "\n":
                    if ($this->_bodyCanonLastChar == "\r") {
                        if ($method) {
                            $this->_bodyCanonSpace = false;
                        }
                        if ($this->_bodyCanonLine == '') {
                            ++$this->_bodyCanonEmptyCounter;
                        } else {
                            $this->_bodyCanonLine = '';
                            $canon .= "\r\n";
                        }
                    } else {
                        // Wooops Error
                        // todo handle it but should never happen
                        // todo what is this error?
                        throw new Swift_SwiftException('Error while canonicalizing Body');
                    }
                    break;
                case ' ':
                case "\t":
                    if ($method) {
                        $this->_bodyCanonSpace = true;
                        break;
                    }
                default:
                    if ($this->_bodyCanonEmptyCounter > 0) {
                        $canon .= str_repeat("\r\n", $this->_bodyCanonEmptyCounter);
                        $this->_bodyCanonEmptyCounter = 0;
                    }
                    if ($this->_bodyCanonSpace) {
                        $this->_bodyCanonLine .= ' ';
                        $canon .= ' ';
                        $this->_bodyCanonSpace = false;
                    }
                    $this->_bodyCanonLine .= $string[$i];
                    $canon .= $string[$i];
            }
        }
        $this->_addToBodyHash($canon);
    }

    protected function _endOfBody()
    {
        // Add trailing Line return if last line is non empty
        if (strlen($this->_bodyCanonLine) > 0) {
            $this->_addToBodyHash("\r\n");
        }
        $this->_bodyHash = hash_final($this->_bodyHashHandler, true);
    }

    private function _addToBodyHash($string)
    {
        $len = strlen($string);
        if ($len > ($new_len = ($this->_maxLen - $this->_bodyLen))) {
            $string = substr($string, 0, $new_len);
            $len = $new_len;
        }
        hash_update($this->_bodyHashHandler, $string);
        $this->_bodyLen += $len;
    }

    private function _addToHeaderHash($header)
    {
        if ($this->_debugHeaders) {
            $this->_debugHeadersData[] = trim($header);
        }
        $this->_headerCanonData .= $header;
    }

    /**
     * @throws Swift_SwiftException
     *
     * @return string
     */
    private function _getEncryptedHash()
    {
        $signature = '';

        // load private key
        $pkeyId = openssl_pkey_get_private($this->_privateKey);
        if ($pkeyId === false) {
            throw new Swift_SwiftException('Unable to load DKIM Private Key ['.openssl_error_string().']');
        }
        // get details about key
        $pkeyId_details = openssl_pkey_get_details($pkeyId);
        // security: dkim headers below 1024 bit will be ignored by google mail
        // rfc6376 3.3.3. Key Sizes: The security constraint that keys smaller than 1024 bits are subject to off-line attacks
        if (isset($pkeyId_details['type']) && $pkeyId_details['type'] == OPENSSL_KEYTYPE_RSA && isset($pkeyId_details['bits']) && $pkeyId_details['bits'] < 1024) {
            throw new  Swift_SwiftException('DKIM Private Key must have at least 1024 bit or higher');
        }
        // sign
        if (!openssl_sign($this->_headerCanonData, $signature, $pkeyId, $this->_hashAlgorithmOpenssl)) {
            throw new Swift_SwiftException('Unable to sign DKIM Hash ['.openssl_error_string().']');
        }
        return $signature;
    }
}

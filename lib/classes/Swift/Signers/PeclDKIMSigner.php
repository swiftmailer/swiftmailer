<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * DKIM Signer used to apply DKIM Signature to a message
 * Takes advantage of pecl extension or fallbacks to php implementation
 *
 * @package    Swift
 * @subpackage Signatures
 * @author     Xavier De Cock <xdecock@gmail.com>
 */

class Swift_Signer_PeclDKIMSigner extends Swift_Signers_DKIMSigner
{
    private $_peclLoaded = false;

    private $_dkimHandler = null;

    public function __construct($privateKey, $domainName, $selector)
    {
        parent::__construct($privateKey, $domainName, $selector);
        if (extension_loaded('dkim')) {
            $this->_peclLoaded = true;
        }
    }

    public function addSignature(Swift_Mime_HeaderSet $headers)
    {
        if (! $this->_peclLoaded) {
            return parent::addSignature($headers);
        } else {
            dkim_eom($this->_dkimHandler);
            $headers->addTextHeader('DKIM-Signature: ', dkim_getsighdr($this->_dkimHandler));
        }

        return $this;
    }

    public function setHeaders(Swift_Mime_HeaderSet $headers)
    {
        if (! $this->_peclLoaded) {
            return parent::setHeaders($headers);
        }
        //dkim_sign(privateKey, selector, domain[, header_canon[, body_canon[, sign_alg[, body_length]]]])
        $bodyLen = $this->_bodyLen();
        if (is_bool($bodyLen)) {
            $bodyLen = - 1;
        }
        $hash = ($this->_hashAlgorithm == 'rsa-sha1') ? DKIM_SIGN_RSASHA1 : DKIM_SIGN_RSASHA256;
        $bodyCanon = ($this->_bodyCanon == 'simple') ? DKIM_CANON_SIMPLE : DKIM_CANON_RELAXED;
        $headerCanon = ($this->_headerCanon == 'simple') ? DKIM_CANON_SIMPLE : DKIM_CANON_RELAXED;
        $this->_dkimHandler = dkim_sign($this->_privateKey, $this->_selector, $this->_domainName, $headerCanon, $bodyCanon, $hash, $bodyLen);

        $listHeaders = $headers->listAll();
        foreach ($listHeaders as $hName) {
            // Check if we need to ignore Header
            if (! isset($this->_ignoredHeaders[strtolower($hName)])) {
                $tmp = $headers->getAll($hName);
                if ($headers->has($hName)) {
                    foreach ($tmp as $header) {
                        if ($header->getFieldBody() != '') {
                            dkim_header($this->_dkimHandler, $header->toString());
                            $this->_signedHeaders[] = $header->getFieldName();
                        }
                    }
                }
            }
        }

        return $this;
    }

    public function startBody()
    {
        if (! $this->_peclLoaded) {
            return parent::startBody();
        }
        dkim_eoh($this->_dkimHandler);

        return $this;
    }

    public function endBody()
    {
        if (! $this->_peclLoaded) {
            return parent::endBody();
        }
        dkim_eob($this->_dkimHandler);

        return $this;
    }

    public function reset()
    {
        $this->_dkimHandler = null;
        parent::reset();
    }

    // Protected

    protected function _canonicalizeBody($string)
    {
        if (! $this->_peclLoaded) {
            return parent::_canonicalizeBody($string);
        }
        dkim_body($this->_dkimHandler, $string);
    }
}

<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Signed Message, message that can be signed using a signer.
 *
 * @package    Swift
 * @subpackage Signed
 *
 * @author     Xavier De Cock <xdecock@gmail.com>
 */
class Swift_SignedMessage extends Swift_Message
{
    /**
     * @var Swift_Signers_HeaderSigner[]
     */
    private $headerSigners = array();

    /**
     * @var Swift_Signers_BodySigner[]
     */
    private $bodySigners = array();

    /**
     * @var array
     */
    private $savedMessage = array();

    /**
     * Create a new Message.
     *
     * @param string $subject
     * @param string $body
     * @param string $contentType
     * @param string $charset
     *
     * @return Swift_SignedMessage
     */
    public static function newInstance($subject = null, $body = null, $contentType = null, $charset = null)
    {
        return new self($subject, $body, $contentType, $charset);
    }

    /**
     * Attach a new signature handler to the message.
     *
     * @param Swift_Signer $signer
     * @return Swift_SignedMessage
     */
    public function attachSigner(Swift_Signer $signer)
    {
        if ($signer instanceof Swift_Signers_HeaderSigner) {
            $this->headerSigners[] = $signer;
        }
        elseif ($signer instanceof Swift_Signers_BodySigner) {
            $this->bodySigners[] = $signer;
        }

        return $this;
    }

    /**
     * Get this message as a complete string.
     *
     * @return string
     */
    public function toString()
    {
        $this->saveMessage();

        $this->doSign();
        $string = parent::toString();
        $this->restoreMessage();

        return $string;
    }

    /**
     * Write this message to a {@link Swift_InputByteStream}.
     *
     * @param Swift_InputByteStream $is
     */
    public function toByteStream(Swift_InputByteStream $is)
    {
        $this->saveMessage();
        $this->doSign();

        parent::toByteStream($is);
        $this->restoreMessage();
    }

    protected function doSign()
    {
        foreach ($this->bodySigners as $signer) {
            $altered = $signer->getAlteredHeaders();
            $this->saveHeaders($altered);
            $signer->signMessage($this);
        }

        foreach ($this->headerSigners as $signer) {
            $altered = $signer->getAlteredHeaders();
            $this->saveHeaders($altered);
            $signer->reset();

            $signer->setHeaders($this->getHeaders());

            $signer->startBody();
            $this->_bodyToByteStream($signer);
            $signer->endBody();

            $signer->addSignature($this->getHeaders());
        }
    }

    protected function saveMessage()
    {
        $this->savedMessage = array('headers'=> array());
        $this->savedMessage['body'] = $this->getBody();
        $this->savedMessage['children'] = $this->getChildren();
    }

    protected function saveHeaders(array $altered)
    {
        foreach ($altered as $head) {
            $lc = strtolower($head);

            if (!isset($this->savedMessage['headers'][$lc])) {
                $this->savedMessage['headers'][$lc] = $this->getHeaders()->getAll($head);
            }
        }
    }

    protected function restoreHeaders()
    {
        foreach ($this->savedMessage['headers'] as $name => $savedValue) {
            $headers = $this->getHeaders()->getAll($name);

            foreach ($headers as $key => $value) {
                if (!isset($savedValue[$key])) {
                    $this->getHeaders()->remove($name, $key);
                }
            }
        }
    }

    protected function restoreMessage()
    {
        $this->setBody($this->savedMessage['body']);
        $this->setChildren($this->savedMessage['children']);

        $this->restoreHeaders();
        $this->savedMessage = array();
    }
}

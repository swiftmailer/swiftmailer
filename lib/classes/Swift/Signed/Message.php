<?php

/*
 Signed Message for SwiftMailer
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

/**
 * Signed Message Special Message where we can apply signatures
 * @package Swift
 * @subpackage Signatures
 * @author Xavier De Cock <xdecock@gmail.com>
 */
class Swift_Signed_Message extends Swift_Mime_SimpleMessage
{
  /**
   * Signature handlers
   *
   * @var Swift_Signer[]
   */
  private $_signers;
  
  /**
   * Create a new Message.
   * Details may be optionally passed into the constructor.
   * @param string $subject
   * @param string $body
   * @param string $contentType
   * @param string $charset
   */
  public function __construct($subject = null, $body = null,
    $contentType = null, $charset = null)
  {
    call_user_func_array(array($this, 'parent::__construct'),
      Swift_DependencyContainer::getInstance()->createDependenciesFor(
        'mime.message'));
    
    if (!isset($charset))
    {
      $charset = Swift_DependencyContainer::getInstance()->lookup(
        'properties.charset');
    }
    $this->setSubject($subject);
    $this->setBody($body);
    $this->setCharset($charset);
    if ($contentType)
    {
      $this->setContentType($contentType);
    }
  }
  
  /**
   * Create a new Message.
   * @param string $subject
   * @param string $body
   * @param string $contentType
   * @param string $charset
   * @return Swift_Mime_Message
   */
  public static function newInstance($subject = null, $body = null,
    $contentType = null, $charset = null)
  {
    return new self($subject, $body, $contentType, $charset);
  }
  
  /**
   * Add a MimePart to this Message.
   * @param string|Swift_OutputByteStream $body
   * @param string $contentType
   * @param string $charset
   */
  public function addPart($body, $contentType = null, $charset = null)
  {
    return $this->attach(
      Swift_MimePart::newInstance($body, $contentType,
        $charset));
  }
  
  /**
   * Attach a new signature handler to the message
   *
   * @param Swift_Signer $signer
   */
  public function attachSigner(Swift_Signer $signer)
  {
    $this->_signers[] = $signer;
  }
  
  /**
   * Get this message as a complete string.
   * @return string
   */
  public function toString()
  {
    $cache = $this->_getCache();
    // BodySigners
    foreach ($this->_signers as $signer)
    {
      if ($signer instanceof Swift_Signers_BodySigner)
      {
        // Do body Signer Specific stuff here
      }
    }
    foreach ($this->_signers as $signer)
    {
      if ($signer instanceof Swift_Signers_HeaderSigner)
      {
        /* @var $signer Swift_Signers_HeaderSigner */
        $signer->reset();
        $signer->setHeaders(
          $this->getHeaders());
        $signer->startBody();
        $this->_bodyToByteStream($signer);
        $signer->endBody();
        $signer->addSignature(
          $this->getHeaders());
      }
    }
    return parent::toString();
  }
  
  /**
   * Write this message to a {@link Swift_InputByteStream}.
   * @param Swift_InputByteStream $is
   */
  public function toByteStream(Swift_InputByteStream $is)
  {
    // BodySigners
    foreach ($this->_signers as $signer)
    {
      if ($signer instanceof Swift_Signers_BodySigner)
      {
        // Do body Signer Specific stuff here
      }
    }
    foreach ($this->_signers as $signer)
    {
      if ($signer instanceof Swift_Signers_HeaderSigner)
      {
        /* @var $signer Swift_Signers_HeaderSigner */
        $signer->reset();
        $signer->startBody();
        $this->_bodyToByteStream($signer);
        $signer->endBody();
        $signer->setHeaders(
          $this->getHeaders());
        $signer->addSignature(
          $this->getHeaders());
      }
    }
    return parent::toByteStream($is);
  }
}

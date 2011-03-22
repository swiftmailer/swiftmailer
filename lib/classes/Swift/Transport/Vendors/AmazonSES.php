<?php
/*
 * This file is part of SwiftMailer.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This Transport Implements SES Interface
 * @package Swift
 * @subpackage Transport
 * @author Xavier De Cock <xdecock@gmail.com>
 * @author Rubn on Lighthouse Bug #176
 */
class Swift_Transport_Vendors_AmazonSES implements Swift_Transport 
{
  protected $_region;
  
  protected $_sendRaw = false;
  
  public function __construct($region = null, $sendRaw = false)
  {
    if (!class_exists('AmazonSES', false))
    {
      throw new Swift_TransportException('AmazonSDK is not loaded, please load it before Starting Swift\'s '.get_class($this));
    }
    if ($region === null)
    {
      $region = AmazonSES::DEFAULT_URL;
    }
    $this->_region = $region;
    $this->_sendRaw = $sendRaw;
  }
  
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    try {
      $ses = new AmazonSES();
      $ses->setRegion($this->region);
      if ($this->_sendRaw)
      {
        $messageContent=$message->toString();
        $ses->send_raw_email($messageContent);
      }
      else
      {
        /* Find Sender */
        $returnPath = $message->getReturnPath();
        if (empty($returnPath))
        {
          $returnPath = false;
        }
        $sender = $message->getSender();
        if (empty($sender))
        {
          $sender = $message->getFrom();
          if (count($sender) > 0)
          {
            throw new Swift_TransportException('Multiple From in the mail, but no sender defined, please correct this');
          }
        }
        $sender = reset($this->_normalizeAddresses($sender));
        $destinations = array();
        /* Add Recipients */
        $to = $message->getTo();
        if (count($to))
        {
          $destinations['ToAddresses'] = $this->_normalizeAddresses($to);
        }
        unset($to);
        /* Add Cc */
        $cc = $message->getCc();
        if (count($cc))
        {
          $destinations['CcAddresses'] = $this->_normalizeAddresses($cc);
        }
        unset($cc);
        /* Add Bcc */
        $bcc = $message->getBcc();
        if (count($bcc))
        {
          $destinations['BccAddresses'] = $this->_normalizeAddresses($bcc);
        }
        unset($bcc);
        /* Content */
        $SESmessage = array();
        /* Subject */
        $SESmessage['Subject.Data'] = $message->getSubject();
        $SESmessage['Subject.Charset'] = $message->getCharset();
        switch ($message->getContentType())
        {
          /* If Message is single part */
          case 'text/plain':
            /* Text Only */
            $SESmessage['Body.Text.Data'] = $message->getBody();
            $SESmessage['Body.Text.Charset'] = $message->getCharset();
            break;
            
          case 'text/html':
            /* HTML Only */
            $SESmessage['Body.Html.Data'] = $message->getBody();
            $SESmessage['Body.Html.Charset'] = $message->getCharset();
            break;
            
          default:
            /* Multipart/Alternative */
            $parts = $message->getChildren();
            $textFound = $htmlFound = false;
            foreach ($parts as $part)
            {
              if ($part->getContentType() == 'text/plain') {
                if ($textFound)
                {
                  throw new Swift_TransportException('Multiple Text Part, Unable to send the mail through AmazonSES SendEmail API, please use SendRawEmail API ');
                }
                $SESmessage['Body.Text.Data'] = $part->getBody();
                $SESmessage['Body.Text.Charset'] = $part->getCharset();
                $textFound = true;
              }
              if ($part->getContentType() == 'text/html') {
                if ($htmlFound)
                {
                  throw new Swift_TransportException('Multiple HTML Part, Unable to send the mail through AmazonSES SendEmail API, please use SendRawEmail API ');
                }
                $SESmessage['Body.Html.Data'] = $part->getBody();
                $SESmessage['Body.Html.Charset'] = $part->getCharset();
                $htmlFound = true;
              }
            }
            break;
        }
        /* Options */
        $options = array();
        $replyTo = $message->getReplyTo();
        if (count($replyTo))
        {
          $options['ReplyToAddresses'] = array_keys($replyTo);
        }
        if ($returnPath)
        {
          $options['ReturnPath'] = $returnPath;
        }
        /* Send The mail */
        $ses->send_email($sender, $destinations, $SESmessage, $options);
      }
    }
    catch (Exception $e)
    {
      throw new Swift_TransportException('Unknown Exception In '.__CLASS__.' : '.$e->getMessage());
    }
  }
  
  public function isStarted()
  {
    return true;
  }
  
  public function registerPlugin()
  {
    return $this;
  }
  
  public function start()
  {
    return $this;
  }
  
  public function stop()
  {
    return $this;
  }
  
  protected function _normalizeAddresses($addresses) {
    $normalizedAddresses = array();
    foreach ($addresses as $address => $name)
    {
      $normalizedAddresses[] = ($name === null ? $name . '<' .$address. '>' : $address);
    }
    return $normalizedAddresses;
  }
}
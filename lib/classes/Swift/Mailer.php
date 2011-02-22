<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Swift Mailer class.
 * 
 * @package Swift
 * @author Chris Corbyn
 */
class Swift_Mailer
{
  
  /** The Transport used to send messages */
  private $_transport;
  
  /**
   * Create a new Mailer using $transport for delivery.
   * 
   * @param Swift_Transport $transport
   */
  public function __construct(Swift_Transport $transport)
  {
    $this->_transport = $transport;
  }

  /**
   * Create a new Mailer instance.
   * 
   * @param Swift_Transport $transport
   * @return Swift_Mailer
   */
  public static function newInstance(Swift_Transport $transport)
  {
    return new self($transport);
  }

  /**
   * Create a new class instance of one if the message services
   * For example 'mimepart' would create a 'message.mimepart' instance
   *
   * @param string $service
   * @return object
   */
  public function createMessage($service = 'message')
  {
    return Swift_DependencyContainer::getInstance()
      ->lookup('message.'.$service);
  }

  /**
   * Send the given Message like it would be sent in a mail client.
   * 
   * All recipients (with the exception of Bcc) will be able to see the other
   * recipients this message was sent to.
   * 
   * If you need to send to each recipient without disclosing details about the
   * other recipients see {@link batchSend()}.
   * 
   * Recipient/sender data will be retreived from the Message object.
   * 
   * The return value is the number of recipients who were accepted for
   * delivery.
   * 
   * @param Swift_Mime_Message $message
   * @param array &$failedRecipients, optional
   * @return int
   * @see batchSend()
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    $failedRecipients = (array) $failedRecipients;
    
    if (!$this->_transport->isStarted())
    {
      $this->_transport->start();
    }
    
    return $this->_transport->send($message, $failedRecipients);
  }
  
  /**
   * Send the given Message to all recipients individually.
   * 
   * This differs from {@link send()} in the way headers are presented to the
   * recipient.  The only recipient in the "To:" field will be the individual
   * recipient it was sent to.
   * 
   * If an iterator is provided, recipients will be read from the iterator
   * one-by-one, otherwise recipient data will be retreived from the Message
   * object.
   * 
   * Sender information is always read from the Message object.
   * 
   * The return value is the number of recipients who were accepted for
   * delivery.
   * 
   * @param Swift_Mime_Message $message
   * @param array &$failedRecipients, optional
   * @param Swift_Mailer_RecipientIterator $it, optional
   * @return int
   * @see send()
   */
  public function batchSend(Swift_Mime_Message $message,
    &$failedRecipients = null,
    Swift_Mailer_RecipientIterator $it = null)
  {
    $failedRecipients = (array) $failedRecipients;
    
    $sent = 0;
    $to = $message->getTo();
    $cc = $message->getCc();
    $bcc = $message->getBcc();
    
    if (!empty($cc))
    {
      $message->setCc(array());
    }
    if (!empty($bcc))
    {
      $message->setBcc(array());
    }
    
    //Use an iterator if set
    if (isset($it))
    {
      while ($it->hasNext())
      {
        $message->setTo($it->nextRecipient());
        $sent += $this->send($message, $failedRecipients);
      }
    }
    else
    {
      foreach ($to as $address => $name)
      {
        $message->setTo(array($address => $name));
        $sent += $this->send($message, $failedRecipients);
      }
    }
    
    $message->setTo($to);
    
    if (!empty($cc))
    {
      $message->setCc($cc);
    }
    if (!empty($bcc))
    {
      $message->setBcc($bcc);
    }
    
    return $sent;
  }
  
  /**
   * Register a plugin using a known unique key (e.g. myPlugin).
   * 
   * @param Swift_Events_EventListener $plugin
   * @param string $key
   */
  public function registerPlugin(Swift_Events_EventListener $plugin)
  {
    $this->_transport->registerPlugin($plugin);
  }
  
  /**
   * The Transport used to send messages.
   * @return Swift_Transport
   */
  public function getTransport()
  {
    return $this->_transport;
  }
}

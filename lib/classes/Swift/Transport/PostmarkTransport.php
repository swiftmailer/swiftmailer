<?php

/**
 * (c) 2010 Olaf van Zandwijk <olaf@vanzandwijk.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__) . '/PostmarkTransportException.php';

/**
 * Postmark helps deliver and track transactional emails for web applications.
 * This is a SwiftMailer transport implementation for Postmark.
 *
 * @package Swift
 * @subpackage Transport
 * @author  Olaf van Zandwijk
 */
class Swift_Transport_PostmarkTransport implements Swift_Transport
{
  /** The event dispatcher from the plugin API */
  private $_eventDispatcher;

  /** @var string */
  private $_apiKey = null;

  /** @var string */
  private $_tag = false;

  /** @var bool */
  private $_useHttps = false;

  /**  @var string */
  const POSTMARK_URI = 'http://api.postmarkapp.com/email';

  /**  @var string */
  const SECURE_POSTMARK_URI = 'https://api.postmarkapp.com/email';

  /** @var string */
  const RESPONSE_SUCCESS = 200;

  /**
   * Constructor
   */
  public function __construct(Swift_Events_EventDispatcher $eventDispatcher)
  {
    if(!function_exists('curl_init')) {
      throw new Swift_PostmarkTransportException("Postmark transport requires curl to function properly");
    }

    $this->_eventDispatcher = $eventDispatcher;
  }
  
  /**
   * Not used
   */
  public function isStarted()
  {
    return false;
  }
  
  /**
   * Not used
   */
  public function start()
  {
  }
  
  /**
   * Not used
   */
  public function stop()
  {
  }

  /**
   * Send the given Message
   * 
   * Recipient/sender data will be retrieved from the Message API.
   * The return value is the number of recipients who were accepted for delivery.
   * 
   * @param Swift_Mime_Message $message
   * @param string[] &$failedRecipients to collect failures by-reference
   * @return int
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    $failedRecipients = (array) $failedRecipients;

    if ($evt = $this->_eventDispatcher->createSendEvent($this, $message))
    {
      $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
      if ($evt->bubbleCancelled()) {
        return 0;
      }
    }

    $count = (
      count((array) $message->getTo())
      + count((array) $message->getCc())
      + count((array) $message->getBcc())
    );

    $postmarkMessage = $this->buildPostmarkMessage($message);
    $postmark = $this->doRequest($postmarkMessage);

    if(self::RESPONSE_SUCCESS == $postmark[0]) {
      if ($evt) {
        $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
        $evt->setFailedRecipients($failedRecipients);
        $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
      }
    } else {
      $count = 0;

      $failedRecipients = array_merge(
        $failedRecipients,
        array_keys((array) $message->getTo()),
        array_keys((array) $message->getCc()),
        array_keys((array) $message->getBcc())
      );

      if ($evt) {
        $evt->setResult(Swift_Events_SendEvent::RESULT_FAILED);
        $evt->setFailedRecipients($failedRecipients);
        $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
      }

      throw new Swift_PostmarkTransportException(sprintf("Postmark delivery failed: %s", $postmark[1]['Message']));
    }

    return $count;
  }
  
  /**
   * Register a plugin.
   *
   * @param Swift_Events_EventListener $plugin
   */
  public function registerPlugin(Swift_Events_EventListener $plugin)
  {
    $this->_eventDispatcher->bindEventListener($plugin);
  }

  /**
   * Sets the Postmark API key
   * 
   * @param string $apiKey
   */
  public function setApiKey($apiKey)
  {
    $this->_apiKey = $apiKey;

    return $this;
  }
 
  /**
   * Sets the Postmark tag for the message(s) sent with this transport
   * or remove it when $tag set to false
   * 
   * @param string $tag
   */
  public function setTag($tag)
  {
    $this->_tag = $tag;

    return $this;
  }

  /**
   * Use HTTPS instead of HTTP to communicate with Postmark
   *
   * @param bool $bool
   */
  public function useHttps($bool)
  {
    $this->_useHttps = $bool;

    return $this;
  }
 
  /**
   * Return the headers that will be included in the HTTP request to Postmark
   *
   * @return array
   */
  protected function getHttpHeaders()
  {
    return array(
      'Accept: application/json',
      'Content-Type: application/json',
      'X-Postmark-Server-Token: ' . $this->_apiKey,
    );
  }

  /**
   * Takes the Postmark message (array) and creates the actual request
   * to the Postmark server
   *
   * @param array $postmarkMessage
   * @return array
   */
  protected function doRequest($postmarkMessage)
  {
    $curl = curl_init();

    if($this->_useHttps) {
      curl_setopt($curl, CURLOPT_URL, self::SECURE_POSTMARK_URI);
    } else {
      curl_setopt($curl, CURLOPT_URL, self::POSTMARK_URI);
    }

    curl_setopt_array($curl, array(
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => $this->getHttpHeaders(),
      CURLOPT_POSTFIELDS => json_encode($postmarkMessage),
      CURLOPT_RETURNTRANSFER => true
    ));

    $response = curl_exec($curl);
    $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if(false === $response) {
      throw new Swift_PostmarkTransportException(sprintf("Postmark delivery failed: %s", curl_error($curl)));
    }

    return array($response_code, @json_decode($response, true));
  }

  /**
   * Transforms a Swift_Mime_Message into an array that is understood by
   * Postmark
   *
   * @param Swift_Mime_Message $message
   * @return array
   */
  protected function buildPostmarkMessage(Swift_Mime_Message $message)
  {
    $headers = $message->getHeaders();
    $extra_headers = array();

    $postmarkMessage = array();

    $postmarkMessage['From'] = $headers->get('From')->getFieldBody();
    $headers->remove('From');

    $postmarkMessage['ReplyTo'] = $message->getReplyTo();

    $postmarkMessage['To'] = $headers->get('To')->getFieldBody();
    $headers->remove('To');

    $postmarkMessage['Cc'] = implode(", ", array_keys((array) $message->getCc()));
    $headers->remove('Cc');

    $postmarkMessage['Bcc'] = implode(", ", array_keys((array) $message->getBcc()));
    $headers->remove('Bcc');

    $postmarkMessage['Subject'] = $headers->get('Subject')->getFieldBody();
    $headers->remove('Subject');

    $textPart = $this->getMIMEPart($message, 'text/plain');
    if (!is_null($textPart)) {
      $postmarkMessage['TextBody'] = $textPart->getBody();
    }

    $htmlPart = $this->getMIMEPart($message, 'text/html');
    if (!is_null($htmlPart)) {
      $postmarkMessage['HtmlBody'] = $htmlPart->getBody();
    }

    if(false !== $this->_tag) {
      $postmarkMessage['Tag'] = $this->_tag;
    }

    foreach ($headers as $header) {
      $extra_headers[] = array(
        'Name' => $header->getFieldName(),
        'Value' => $header->getFieldBody(),
      );
    }

    if (!empty($extra_headers)) {
      $postmarkMessage['Headers'] = $extra_headers;
    }

    return $postmarkMessage;
  }

  /**
   * Returns a specified MIME part from a message
   *
   * @param Swift_Mime_Message $message
   * @param string $mime_type
   * @return Swift_Mime_MimePart
   */
  protected function getMIMEPart(Swift_Mime_Message $message, $mime_type = 'text/plain')
  {
    $part = null;
    foreach ($message->getChildren() as $c) {
      if (false !== strpos($c->getContentType(), $mime_type)) {
        $part = $c;
        break;
      }
    }

    return $part;
  }

}

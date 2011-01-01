<?php

/*
 * (c) 2010 Olaf van Zandwijk <olaf@vanzandwijk.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Postmark helps deliver and track transactional emails for web applications.
 * This is a SwiftMailer transport implementation for Postmark.
 *
 * Usage example:
 *
 * // Create the Transport
 * $transport = Swift_PostmarkTransport::newInstance("YOUR-API-KEY");
 * $transport->setTag("Tag");
 *
 * // Create the Mailer using the created Transport
 * $mailer = Swift_Mailer::newInstance($transport);
 *
 * $message = Swift_Message::newInstance('...')->...
 *
 * // Send the message
 * $mailer->send($message);
 *
 * @package Swift
 * @subpackage Transport
 * @author  Olaf van Zandwijk
 */
class Swift_PostmarkTransport extends Swift_Transport_PostmarkTransport
{
  /**
   * Create a new PostmarkTransport, optionally specifying the Postmark API key
   *
   * @param string $apiKey
   * @param string $tag
   * @param bool $useSecure
   */
  public function __construct($apiKey = null, $tag = false, $useSecure = false)
  {
    call_user_func_array(
      array($this, 'Swift_Transport_PostmarkTransport::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.postmark')
    );

    $this->setApiKey($apiKey);
    $this->setTag($tag);
    $this->useHttps($useSecure);
  }
  
  /**
   * Create a new PostmarkTransport instance
   *
   * @param string $apiKey Your Postmark API key
   * @param string $tag The Postmark tag for the message(s) sent with this transport
   * @param bool $useSecure Use HTTPS instead of HTTP to communicate with Postmark
   * @return Swift_PostmarkTransport
   */
  public static function newInstance($apiKey = null, $tag = false, $useSecure = false)
  {
    return new self($apiKey, $tag, $useSecure);
  }
}

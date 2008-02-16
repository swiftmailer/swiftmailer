<?php

/*
 The SMTP Transport from Swift Mailer.
 
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

//@require 'Swift/Mailer/Transport.php';
//@require 'Swift/Mailer/Transport/IoBuffer.php';
//@require 'Swift/Mailer/SmtpExtensionHandler.php';
//@require 'Swift/Mime/Message.php';

/**
 * Sends Messages over SMTP.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Mailer_Transport_SmtpTransport
  implements Swift_Mailer_Transport, Swift_Mailer_Transport_SmtpExtensionHandler
{
  
  /**
   * An Input-Output buffer for sending/receiving SMTP commands and responses.
   * @var Swift_Mailer_Transport_IoBuffer
   * @access private
   */
  private $_buffer;
  
  /**
   * Connection buffer parameters.
   * @var array
   * @access private
   */
  private $_params = array(
    'protocol' => 'tcp',
    'host' => 'localhost',
    'port' => 25,
    'timeout' => 30,
    'blocking' => 1,
    'type' => Swift_Mailer_Transport_IoBuffer::TYPE_SOCKET
    );
  
  /**
   * Connection status.
   * @var boolean
   * @access private
   */
  private $_started = false;
  
  /**
   * The domain name to use in EHL0/HELO commands.
   * @var string
   * @access private
   */
  private $_domain = 'localhost';
  
  /**
   * ESMTP extension handlers.
   * @var Swift_Mailer_Transport_SmtpExtensionHandler[]
   * @access private
   */
  private $_extensionHandlers = array();
  
  /**
   * Extensions supported by the remote server.
   * @var string[]
   * @access private
   */
  private $_extensions = array();
  
  /**
   * Creates a new SmtpTransport using the given I/O buffer.
   * @param Swift_Mailer_Transport_IoBuffer $buf
   */
  public function __construct(Swift_Mailer_Transport_IoBuffer $buffer,
    array $extensionHandlers)
  {
    $this->_buffer = $buffer;
    $this->setExtensionHandlers($extensionHandlers);
  }
  
  /**
   * Test if an SMTP connection has been established.
   * @return boolean
   */
  public function isStarted()
  {
    return $this->_started;
  }
  
  /**
   * Start the SMTP connection.
   */
  public function start()
  {
    if (!$this->_started)
    {
      $this->_buffer->initialize($this->_params);
      $this->_assertResponseCode($this->_getFullResponse(0), array(220));
      $response = null;
      try
      {
        $seq = $this->_buffer->write(sprintf("EHLO %s\r\n", $this->_domain));
        $response = $this->_getFullResponse($seq);
        $this->_assertResponseCode($response, array(250));
      }
      catch (Exception $e)
      {
        $seq = $this->_buffer->write(sprintf("HELO %s\r\n", $this->_domain));
        $response = $this->_getFullResponse($seq);
        $this->_assertResponseCode($response, array(250));
      }
      
      //Determine ESMTP capabilities, and inform any extension handlers
      $extensions = $this->_getExtensions($response);
      foreach ($extensions as $extension => $params)
      {
        $handlers = $this->_getHandlersFor(array($extension));
        foreach ($handlers as $handler)
        {
          $handler->setKeywordParameters($params);
        }
      }
      
      $this->_runHandlers('afterEhlo', null);
      
      $this->_started = true;
    }
  }
  
  /**
   * Stop the SMTP connection.
   */
  public function stop()
  {
    if ($this->_started)
    {
      $seq = $this->_buffer->write("QUIT\r\n");
      try
      {
        $this->_assertResponseCode($this->_getFullResponse($seq), array(221));
      }
      catch (Exception $e)
      {//log this? 
      }
      $this->_buffer->terminate();
    }
    $this->_started = false;
  }
  
  /**
   * Send the given Message.
   * Recipient/sender data will be retreived from the Message API.
   * The return value is the number of recipients who were accepted for delivery.
   * @param Swift_Mime_Message $message
   * @return int
   */
  public function send(Swift_Mime_Message $message)
  {
    $sent = 0;
    
    if (!$reversePath = $this->_getReversePath($message))
    {
      throw new Exception('Cannot send message without a sender address');
    }
    
    $to = $message->getTo();
    $cc = $message->getCc();
    $bcc = $message->getBcc();
    //Remove Bcc headers initially
    if (!empty($bcc))
    {
      $message->setBcc(array());
    }
    
    //Send to all direct recipients
    if (!empty($to) || !empty($cc))
    {
      try
      {
        $sent += $this->_doMailTransaction(
          $message, $reversePath, array_merge(
            array_keys((array)$to), array_keys((array)$cc)
            )
          );
      }
      catch (Exception $e)
      {
        if (!empty($bcc)) //don't leave $message in a state it wasn't given in
        {
          $message->setBcc($bcc);
        }
        throw $e;
      }
    }
    
    //Send blind copies
    if (!empty($bcc))
    {
      foreach ((array) $bcc as $forwardPath => $name)
      {
        //Update the message for this recipient
        $message->setBcc(array($forwardPath => $name));
        try
        {
          $sent += $this->_doMailTransaction(
            $message, $reversePath, array($forwardPath)
            );
        }
        catch (Exception $e)
        {
           //don't leave $message in a state it wasn't given in
          $message->setBcc($bcc);
          throw $e;
        }
      }
    }
    
    //Restore Bcc headers
    if (!empty($bcc))
    {
      $message->setBcc($bcc);
    }
    
    return $sent;
  }
  
  /**
   * Set handlers for ESMTP keywords.
   * @param Swift_Mailer_Transport_SmtpExtensionHandler[] $extensionHandlers
   */
  public function setExtensionHandlers(array $extensionHandlers)
  {
    $set = array();
    foreach ($extensionHandlers as $handler)
    {
      $kw = $handler->getHandledKeyword();
      if (!isset($set[$kw]))
      {
        $set[$kw] = array();
      }
      $set[$kw][] = $handler;
    }
    $this->_extensionHandlers = $set;
  }
  
  /**
   * Get handlers for ESMTP keywords.
   * @return Swift_Mailer_Transport_SmtpExtensionHandler[]
   */
  public function getExtensionHandlers()
  {
    $handlers = array();
    foreach ($this->_extensionHandlers as $list)
    {
      $handlers = array_merge($handlers, $list);
    }
    return $handlers;
  }
  
  /**
   * Set the name of the local domain which Swift will identify itself as.
   * This should be a fully-qualified domain name and should be truly the domain
   * you're using.  If your server doesn't have a domain name, use the IP in square
   * brackets (i.e. [127.0.0.1]).
   * @param string $domain
   */
  public function setLocalDomain($domain)
  {
    $this->_domain = $domain;
  }
  
  /**
   * Get the name of the domain Swift will identify as.
   * @return string
   */
  public function getLocalDomain()
  {
    return $this->_domain;
  }
  
  /**
   * Set the connection timeout.
   * @param int $timeout seconds
   */
  public function setTimeout($timeout)
  {
    $this->_params['timeout'] = (int) $timeout;
  }
  
  /**
   * Get the connection timeout.
   * @return int
   */
  public function getTimeout()
  {
    return $this->_params['timeout'];
  }
  
  /**
   * Reset the current mail transaction.
   */
  public function reset()
  {
    $seq = $this->_buffer->write("RSET\r\n");
    $this->_assertResponseCode($this->_getFullResponse($seq), array(250));
  }
  
  // -- Extension handling methods
  
  /**
   * Get the name of the ESMTP extension this handles.
   * @return boolean
   */
  public function getHandledKeyword()
  {
  }
  
  /**
   * Set the parameters which the EHLO greeting indicated.
   * @param string[] $parameters
   */
  public function setKeywordParameters(array $parameters)
  {
  }
  
  /**
   * Set information about the connection (e.g. encryption, username/password).
   * @param array $fields
   */
  public function setConnectionFields(array $fields)
  {
  }
  
  /**
   * Runs immediately after a EHLO has been issued.
   * @param Swift_Mailer_Transport_IoBuffer $buf to read/write
   * @param int &$continue needs to be set FALSE if the next extension shouldn't run
   */
  public function afterEhlo(Swift_Mailer_Transport_IoBuffer $buf, &$continue)
  {
    $continue = self::CONTINUE_NONE;
  }
  
  /**
   * Runs when MAIL FROM is needed.
   * The $command contains the elements 'address' and 'params'.
   * This method must return $command after completion.
   * @param Swift_Mailer_Transport_IoBuffer $buf to read/write
   * @param string[] $command
   * @param int &$continue
   * @return string[]
   */
  public function atMailFrom(Swift_Mailer_Transport_IoBuffer $buf,
    array $command, &$continue)
  {
    $continue = self::CONTINUE_NONE;
    $address = $command['address'];
    $params = !empty($command['params'])
      ? ' ' . implode(' ', $command['params'])
      : '';
    $seq = $buf->write(sprintf("MAIL FROM: <%s>%s\r\n", $address, $params));
    $this->_assertResponseCode($this->_getFullResponse($seq), array(250));
    return $command;
  }
  
  /**
   * Runs when RCPT TO is needed.
   * The $command contains the elements 'address' and 'params'.
   * This method must return $command after completion.
   * @param Swift_Mailer_Transport_IoBuffer $buf to read/write
   * @param string[] $command
   * @param int &$continue
   * @return string[]
   */
  public function atRcptTo(Swift_Mailer_Transport_IoBuffer $buf,
    array $command, &$continue)
  {
    $continue = self::CONTINUE_NONE;
    $address = $command['address'];
    $params = !empty($command['params'])
      ? ' ' . implode(' ', $command['params'])
      : '';
    $seq = $buf->write(sprintf("RCPT TO: <%s>%s\r\n", $address, $params));
    $this->_assertResponseCode(
      $this->_getFullResponse($seq), array(250, 251, 252)
      );
    return $command;
  }
  
  /**
   * Runs when the DATA command is due to be sent.
   * @param Swift_Mailer_Transport_IoBuffer $buf to read/write
   * @param Swift_Mime_Message $message to send
   * @param int &$continue
   */
  public function atData(Swift_Mailer_Transport_IoBuffer $buf,
    Swift_Mime_Message $message, &$continue)
  {
    $continue = self::CONTINUE_NONE;
    $seq = $buf->write("DATA\r\n");
    $this->_assertResponseCode($this->_getFullResponse($seq), array(354));
    //Stream the message straight into the buffer
    $buf->setWriteTranslations(array("\n." => "\n.."));
    $message->toByteStream($buf);
    //End data transmission
    $buf->setWriteTranslations(array());
    $seq = $buf->write("\r\n.\r\n");
    $this->_assertResponseCode($this->_getFullResponse($seq), array(250));
  }
  
  // -- Private methods
  
  /**
   * Checks if the response code matches a given number.
   * @param string $response
   * @param int $wanted
   * @throws Exception if the assertion fails
   */
  private function _assertResponseCode($response, $wanted)
  {
    list($code, $separator, $text) = sscanf($response, '%3d%[ -]%s');
    if (!in_array($code, $wanted))
    {
      throw new Exception(
        'Expected response code ' . implode('/', $wanted) . ' but got code ' .
        '"' . $code . '", with message "' . $response . '"'
        );
    }
  }
  
  /**
   * Get the entire response of a multi-line response.
   * @param int $seq number from {@link write()}.
   * @return string
   * @access private
   */
  private function _getFullResponse($seq)
  {
    $response = '';
    do
    {
      $line = $this->_buffer->readLine($seq);
      $response .= $line;
    }
    while (null !== $line && false !== $line && ' ' != $line{3});
    return $response;
  }
  
  /**
   * Determine the best-use reverse path for this message.
   * The preferred order is: return-path, sender, from.
   * @param Swift_Mime_Message $message
   * @return string
   */
  private function _getReversePath(Swift_Mime_Message $message)
  {
    $return = $message->getReturnPath();
    $sender = $message->getSender();
    $from = $message->getFrom();
    $path = null;
    if (!empty($return))
    {
      $path = $return;
    }
    elseif (!empty($sender))
    {
      $keys = array_keys($sender);
      $path = array_shift($keys);
    }
    elseif (!empty($from))
    {
      $keys = array_keys($from);
      $path = array_shift($keys);
    }
    return $path;
  }
  
  /**
   * Parse the EHLO response to determine the capabilities of the server.
   * @param string $response
   * @return array
   * @access private
   */
  private function _getExtensions($response)
  {
    $extensions = array();
    $response = trim($response);
    $lines = explode("\r\n", $response);
    array_shift($lines);
    foreach ($lines as $line)
    {
      if (preg_match('/^[0-9]{3}[ -]([A-Z0-9]+)(.*)$/Di', $line, $matches))
      {
        $params = ltrim($matches[2], ' =');
        $extensions[strtoupper($matches[1])] = !empty($params)
          ? explode(' ', $params)
          : array();
      }
    }
    $this->_extensions = $extensions;
    return $extensions;
  }
  
  /**
   * Finds all extension handlers for the given keyword.
   * @param string[] $keywords
   * @return Swift_Mailer_Transport_SmtpExtensionHandler[]
   * @access private
   */
  private function _getHandlersFor(array $keywords)
  {
    $handlers = array();
    foreach ($this->_extensionHandlers as $kw => $list)
    {
      if (in_array($kw, $keywords))
      {
        $handlers = array_merge($handlers, $list);
      }
    }
    return $handlers;
  }
  
  /**
   * Get all extension handlers which work on this connection.
   * @return Swift_Mailer_Transport_SmtpExtensionHandler[]
   * @access private
   */
  private function _getSupportedHandlers()
  {
    $handlers = array();
    foreach (array_keys($this->_extensions) as $kw)
    {
      if (array_key_exists($kw, $this->_extensionHandlers))
      {
        $handlers[$kw] = $this->_extensionHandlers[$kw];
      }
    }
    return $handlers;
  }
  
  /**
   * Send the given email to the given recipients from the given reverse path.
   * @param Swift_Mime_Message $message
   * @param string $reversePath
   * @param string[] $recipients
   * @return int
   */
  private function _doMailTransaction($message, $reversePath, array $recipients)
  {
    $sent = 0;
    
    $this->_runHandlers(
      'atMailFrom', array('address' => $reversePath, 'params' => array()), true
      );
    
    foreach ($recipients as $forwardPath)
    {
      try
      {
        $this->_runHandlers(
          'atRcptTo', array('address' => $forwardPath, 'params' => array()), true
          );
        $sent++;
      }
      catch (Exception $e)
      {
      }
    }
    
    if ($sent > 0)
    {
      $this->_runHandlers('atData', $message, false);
    }
    else
    {
      $this->reset();
    }
    
    return $sent;
  }
  
  /**
   * Run all extension handlers' $method.
   * @param string $method
   * @param mixed $arg to use if not null
   * @param boolean $filter true if $arg will be filtered
   * @access private
   */
  private function _runHandlers($method, $arg = null, $filter = false)
  {
    $groups = $this->_getSupportedHandlers();
    $groups[] = array($this);
    do
    {
      $continue = self::CONTINUE_ALL;
      $handlers = array_shift($groups);
      do
      {
        $handler = array_shift($handlers);
        if ($arg !== null)
        {
          if (!$filter)
          {
            $handler->$method($this->_buffer, $arg, $continue);
          }
          else
          {
            $arg = $handler->$method($this->_buffer, $arg, $continue);
          }
        }
        else
        {
          $handler->$method($this->_buffer, $continue);
        }
      }
      while (!empty($handlers) && self::CONTINUE_ALL == $continue);
    }
    while (!empty($groups) && self::CONTINUE_NONE != $continue);
  }
  
  /**
   * Destructor.
   */
  public function __destruct()
  {
    $this->stop();
  }
  
}

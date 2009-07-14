<?php

/*
 The ESMTP Transport from Swift Mailer.
 
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

//@require 'Swift/Transport/AbstractSmtpTransport.php';
//@require 'Swift/Transport/EsmtpHandler.php';
//@require 'Swift/Transport/IoBuffer.php';
//@require 'Swift/Transport/SmtpAgent.php';
//@require 'Swift/TransportException.php';
//@require 'Swift/Mime/Message.php';
//@require 'Swift/Events/EventDispatcher.php';

/**
 * Sends Messages over SMTP with ESMTP support.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_EsmtpTransport
  extends Swift_Transport_AbstractSmtpTransport
  implements Swift_Transport_SmtpAgent
{
  
  /**
   * ESMTP extension handlers.
   * @var Swift_Transport_EsmtpHandler[]
   * @access private
   */
  private $_handlers = array();
  
  /**
   * ESMTP capabilities.
   * @var string[]
   * @access private
   */
  private $_capabilities = array();
  
  /**
   * Connection buffer parameters.
   * @var array
   * @access protected
   */
  private $_params = array(
    'protocol' => 'tcp',
    'host' => 'localhost',
    'port' => 25,
    'timeout' => 30,
    'blocking' => 1,
    'type' => Swift_Transport_IoBuffer::TYPE_SOCKET
    );
  
  /**
   * Creates a new EsmtpTransport using the given I/O buffer.
   * @param Swift_Transport_IoBuffer $buf
   * @param Swift_Transport_EsmtpHandler[] $extensionHandlers
   * @param Swift_Events_EventDispatcher $dispatcher
   */
  public function __construct(Swift_Transport_IoBuffer $buf,
    array $extensionHandlers, Swift_Events_EventDispatcher $dispatcher)
  {
    parent::__construct($buf, $dispatcher);
    $this->setExtensionHandlers($extensionHandlers);
  }
  
  /**
   * Set the host to connect to.
   * @param string $host
   */
  public function setHost($host)
  {
    $this->_params['host'] = $host;
    return $this;
  }
  
  /**
   * Get the host to connect to.
   * @return string
   */
  public function getHost()
  {
    return $this->_params['host'];
  }
  
  /**
   * Set the port to connect to.
   * @param int $port
   */
  public function setPort($port)
  {
    $this->_params['port'] = (int) $port;
    return $this;
  }
  
  /**
   * Get the port to connect to.
   * @return int
   */
  public function getPort()
  {
    return $this->_params['port'];
  }
  
  /**
   * Set the connection timeout.
   * @param int $timeout seconds
   */
  public function setTimeout($timeout)
  {
    $this->_params['timeout'] = (int) $timeout;
    return $this;
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
   * Set the encryption type (tls or ssl)
   * @param string $encryption
   */
  public function setEncryption($enc)
  {
    $this->_params['protocol'] = $enc;
    return $this;
  }
  
  /**
   * Get the encryption type.
   * @return string
   */
  public function getEncryption()
  {
    return $this->_params['protocol'];
  }
  
  /**
   * Set ESMTP extension handlers.
   * @param Swift_Transport_EsmtpHandler[] $handlers
   */
  public function setExtensionHandlers(array $handlers)
  {
    $assoc = array();
    foreach ($handlers as $handler)
    {
      $assoc[$handler->getHandledKeyword()] = $handler;
    }
    uasort($assoc, array($this, '_sortHandlers'));
    $this->_handlers = $assoc;
    $this->_setHandlerParams();
    return $this;
  }
  
  /**
   * Get ESMTP extension handlers.
   * @return Swift_Transport_EsmtpHandler[]
   */
  public function getExtensionHandlers()
  {
    return array_values($this->_handlers);
  }
  
  /**
   * Run a command against the buffer, expecting the given response codes.
   * If no response codes are given, the response will not be validated.
   * If codes are given, an exception will be thrown on an invalid response.
   * @param string $command
   * @param int[] $codes
   * @param string[] &$failures
   * @return string
   */
  public function executeCommand($command, $codes = array(), &$failures = null)
  {
    $failures = (array) $failures;
    $stopSignal = false;
    $response = null;
    foreach ($this->_getActiveHandlers() as $handler)
    {
      $response = $handler->onCommand(
        $this, $command, $codes, $failures, $stopSignal
        );
      if ($stopSignal)
      {
        return $response;
      }
    }
    return parent::executeCommand($command, $codes, $failures);
  }
  
  // -- Mixin invocation code
  
  /** Mixin handling method for ESMTP handlers */
  public function __call($method, $args)
  {
    foreach ($this->_handlers as $handler)
    {
      if (in_array(strtolower($method),
        array_map('strtolower', (array) $handler->exposeMixinMethods())
        ))
      {
        $return = call_user_func_array(array($handler, $method), $args);
        //Allow fluid method calls
        if (is_null($return) && substr($method, 0, 3) == 'set')
        {
          return $this;
        }
        else
        {
          return $return;
        }
      }
    }
    trigger_error('Call to undefined method ' . $method, E_USER_ERROR);
  }
  
  // -- Protected methods
  
  /** Get the params to initialize the buffer */
  protected function _getBufferParams()
  {
    return $this->_params;
  }
  
  /** Overridden to perform EHLO instead */
  protected function _doHeloCommand()
  {
    try
    {
      $response = $this->executeCommand(
        sprintf("EHLO %s\r\n", $this->_domain), array(250)
        );
      $this->_capabilities = $this->_getCapabilities($response);
      $this->_setHandlerParams();
      foreach ($this->_getActiveHandlers() as $handler)
      {
        $handler->afterEhlo($this);
      }
    }
    catch (Swift_TransportException $e)
    {
      parent::_doHeloCommand();
    }
  }
  
  /** Overridden to add Extension support */
  protected function _doMailFromCommand($address)
  {
    $handlers = $this->_getActiveHandlers();
    $params = array();
    foreach ($handlers as $handler)
    {
      $params = array_merge($params, (array) $handler->getMailParams());
    }
    $paramStr = !empty($params) ? ' ' . implode(' ', $params) : '';
    $this->executeCommand(
      sprintf("MAIL FROM: <%s>%s\r\n", $address, $paramStr), array(250)
      );
  }
  
  /** Overridden to add Extension support */
  protected function _doRcptToCommand($address)
  {
    $handlers = $this->_getActiveHandlers();
    $params = array();
    foreach ($handlers as $handler)
    {
      $params = array_merge($params, (array) $handler->getRcptParams());
    }
    $paramStr = !empty($params) ? ' ' . implode(' ', $params) : '';
    $this->executeCommand(
      sprintf("RCPT TO: <%s>%s\r\n", $address, $paramStr), array(250, 251, 252)
      );
  }
  
  // -- Private methods
  
  /** Determine ESMTP capabilities by function group */
  private function _getCapabilities($ehloResponse)
  {
    $capabilities = array();
    $ehloResponse = trim($ehloResponse);
    $lines = explode("\r\n", $ehloResponse);
    array_shift($lines);
    foreach ($lines as $line)
    {
      if (preg_match('/^[0-9]{3}[ -]([A-Z0-9-]+)((?:[ =].*)?)$/Di', $line, $matches))
      {
        $keyword = strtoupper($matches[1]);
        $paramStr = strtoupper(ltrim($matches[2], ' ='));
        $params = !empty($paramStr) ? explode(' ', $paramStr) : array();
        $capabilities[$keyword] = $params;
      }
    }
    return $capabilities;
  }
  
  /** Set parameters which are used by each extension handler */
  private function _setHandlerParams()
  {
    foreach ($this->_handlers as $keyword => $handler)
    {
      if (array_key_exists($keyword, $this->_capabilities))
      {
        $handler->setKeywordParams($this->_capabilities[$keyword]);
      }
    }
  }
  
  /** Get ESMTP handlers which are currently ok to use */
  private function _getActiveHandlers()
  {
    $handlers = array();
    foreach ($this->_handlers as $keyword => $handler)
    {
      if (array_key_exists($keyword, $this->_capabilities))
      {
        $handlers[] = $handler;
      }
    }
    return $handlers;
  }
  
  /** Custom sort for extension handler ordering */
  private function _sortHandlers($a, $b)
  {
    return $a->getPriorityOver($b->getHandledKeyword());
  }
  
}

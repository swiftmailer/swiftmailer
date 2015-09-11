<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Uses several Transports with mapping rules.
 *
 * @package    Swift
 * @subpackage Transport
 * @author     Patrick McAndrew <patrick@urg.name>
 */
class Swift_Transport_MappedTransport implements Swift_Transport
{
    /**
     * The Transports which are used in mapping.
     *
     * @var array[] (TransportName -> Swift_Transport)
     */
    protected $_transports = array();

    /**
     * The default transport name
     *
     * @var string
     */
    protected $_defaultTransportName;

    /**
     * The Mapping which are used in mapping.
     *
     * @var string[] (key -> value)
     */
    protected $_mappings = array();
    
    /**
     * Creates a new MappedTransport.
     */
    public function __construct()
    {
    }

    /**
     * Set $transports to delegate to.
     *
     * @param Swift_Transport[] $transports
     */
    public function setTransports(array $transports)
    {
        foreach($transports as $key => $value) {
           if (is_string($key) == false) {
               throw new Exception("Invalid transport array - must be key(string) => value");
           } else {
               $this->_mappings[$key] = array();
           }
        }
        
        $this->_transports = $transports;
    }
    
    /**
     * Get $transports to delegate to.
     *
     * @return Swift_Transport[]
     */
    public function getTransports()
    {
        return $this->_transports;
    }

    /**
     * Get $transports to delegate to.
     *
     * @return Swift_Transport[]
     */
    public function getTransportByName($transportName)
    {
        if (array_key_exists($transportName, $this->_transports)) {
          return $this->_transports[$transportName];
        } else {
          return null;
        }
    }

    /**
     * Set the default transport name
     *
     * @param string $transportName
     */
    public function setDefaultTransportName($transportName)
    {
        $this->_defaultTransportName = $transportName;
    }

    /**
     * Get the default transport name
     *
     * @return string
     */
    public function getDefaultTransportName()
    {
        return $this->_defaultTransportName;
    }

    /**
     * Get the default transport
     *
     * @return Swift_Transport 
     */
    public function getDefaultTransport()
    {
        return $this->getTransportByName($this->getDefaultTransportName());
    }

    /**
     * Set $mappings to delegate to.
     *
     * @param string $transport
     * @param string[] $mappings
     */
    public function setMappings($transportName, array $mappings)
    {
        $this->_mappings[$transportName] = $mappings;
    }
    
    /**
     * Get $transports to delegate to.
     *
     * @param string $transportName
     * @return string[]
     */
    public function getMappings($transportName)
    {
        return $this->_mappings[$transportName];
    }

    /**
     * Test if this Transport mechanism has started.
     *
     * @return boolean
     */
    public function isStarted()
    {
        foreach($this->_transports as $transport)
        {
           if ($transport->isStarted()) {
               return true;
           }
        }
        
        return false;
    }

    /**
     * Start this Transport mechanism.
     */
    public function start()
    {
      // nothing to do - individual transports will be started as required in the send
    }

    /**
     * Stop this Transport mechanism.
     */
    public function stop()
    {
        foreach ($this->_transports as $transport) {
            $transport->stop();
        }
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_Message $message
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $transport = $this->_getMappingTransport($message);
        if ($transport == null) {
            throw new Swift_TransportException(
                'Unable to find transport'
                );
        }
              if (!$transport->isStarted()) {
                  $transport->start();
              }
              $sent = $transport->send($message, $failedRecipients);

        return $sent;
    }

    /**
     * Register a plugin.
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        foreach ($this->_transports as $transport) {
            $transport->registerPlugin($plugin);
        }
    }

    // -- Protected methods

    /**
     * Find the transport according to mappings
     *
     * @return Swift_Transport
     */
    protected function _getMappingTransport(Swift_Mime_Message $message)
    {
       foreach ($this->getTransports() as $transportName => $transport) {
         $mapping = $this->getMappings($transportName);
         if ($mapping != null) {
           foreach ($mapping as $mappingItem)  {
              foreach ($mappingItem as $key => $mappingValue)  {
                  // call key function on the message (e.g. getFrom)
                  $messageValue = call_user_func(array($message, $key));

                  // we're currently expecting messagevalue to be:
                  // Swift_Mime_SimpleHeaderSet if getHeaders() is called
                  // array($email => name) if getFrom/getTo is called
                  // string if getSubject is called
                  if ($messageValue instanceof Swift_Mime_SimpleHeaderSet && is_array($mappingValue)) {
                     $mappingValueKey = key($mappingValue);
                     $headers = $messageValue->getAll($mappingValueKey);
                     foreach ($headers as $header) {
                       if (preg_match($this->ensureRegEx($mappingValue[$mappingValueKey]), $header->getValue())) {
                          return $transport;
                       }
                     }
                  } else if (is_array($messageValue)) {  //  email address (email=>name), in which case match the email
                       foreach ($messageValue as $messageItemKey => $messageItemValue) {
                         if (preg_match($this->ensureRegEx($mappingValue), $messageItemKey)) {
                            return $transport;
                         }
                       }
                  } else if (preg_match($this->ensureRegEx($mappingValue), $messageValue)) {
                       return $transport;
                  }   
              }
           }
         }
       }
       
       return $this->getDefaultTransport(); 
    }
    
    /**
     * Find the transport according to mappings
     *
     * @return Swift_Transport
     */
    protected function ensureRegEx($pattern)
    {
      if (!preg_match('/^\/.*\/$/', $pattern)) {
          $pattern = '/^' . str_replace('.', '\.', $pattern) . '$/i';
      }
      
      return $pattern;
    }
}

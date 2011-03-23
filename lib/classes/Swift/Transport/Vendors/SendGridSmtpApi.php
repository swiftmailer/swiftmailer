<?php
/*
 * This file is part of SwiftMailer.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * This is A vendor Specific Transport
 * If you want to send email through this vendor API, you'll need
 * to subscribe there : http://sendgrid.com/ and login with your 
 * login and password as usual SMTP auth
 */

/**
 * This Transport Implements SendGrid SMTP Interface
 * @package Swift
 * @subpackage Transport
 * @author Xavier De Cock <xdecock@gmail.com>
 */
Class Swift_Transport_Vendors_SendgridSmtpApi extends Swift_Transport_EsmtpTransport
{
  protected $_replacements = array();
  protected $_defaults = array();
  protected $_category = 'swiftMailer';
  protected $_filters = array();
  protected $_headerName = 'X-SMTPAPI';
  protected $_useXSmtpApiTo = false;
  
  /**
   * Constructor
   * @param string $host
   * @param int $port
   */
  public function __construct($host = 'smtp.sendgrid.net', $port = 25)
  {
    call_user_func_array(
      array($this, 'Swift_Transport_EsmtpTransport::__construct'),
      Swift_DependencyContainer::getInstance()
        ->createDependenciesFor('transport.smtp')
      );
    
    $this->setHost($host);
    $this->setPort($port);
  }
  
  /**
   * Instanciate a new SendGrid Transport
   * @param string $host
   * @param int $port
   * @retun Swift_Transport_Vendors_SendgridSmtpApi
   */
  public static function newInstance($host = 'smtp.sendgrid.net', $port = 25)
  {
    return new self($host, $port);
  }
  
  /**
   * Send the given Message.
   * 
   * Recipient/sender data will be retreived from the Message API.
   * The return value is the number of recipients who were accepted for delivery.
   * If the API Uses X-SMTPAPI To field, only 1 will be returned.
   * 
   * @param Swift_Mime_Message $message
   * @param string[] &$failedRecipients to collect failures by-reference
   * @return int
   */
  public function send(Swift_Mime_Message $message, &$failedRecipients = null)
  {
    $headers = $message->getHeaders();
    if ($this->_useXSmtpApiTo)
    {
      // Save To, CC, BCC
      $to = $message->getTo();
      $cc = $message->getCc();
      $bcc = $message->getBcc();
      // Drop Recipients
      $message->setTo(array('dummy@swiftmailer.org'))->setCc(array())->setBcc(array());
    }
    else 
    {
      $to = $cc = $bcc = array();
    }
    $headers->addTextHeader($this->_headerName, $this->_getApiHeaderValue($to, $cc, $bcc));
    $sent = parent::send($message, $failedRecipients);
    if ($this->_useXSmtpApiTo)
    {
      // Restore recipients
      $message->setTo($to)->setCc($cc)->setBcc($bcc);
    }
    $headers->remove($this->_headerName);
    return $this;
  }
  
  /**
   * Add replacement rules for text changing
   * This automatically enables the XSMTPAPI - To field
   * @param string $replacementKey
   * @param array $replacementValues [email=>value]
   * @param string $defaultValue default value if email is not found
   * @return Swift_Transport_Vendors_SendgridSmtpApi
   */
  public function setReplacements($replacementKey, $replacementValues, $defaultValue='')
  {
    $this->_replacements[$replacementKey] = $replacementValues;
    $this->_defaults[$replacementKey] = $defaultValue;
    $this->_useXSmtpApiTo = true;
    return $this;
  }
  
  /**
   * Deletes all replacements or for a specific replacementKey
   * 
   * @param string $replacementKey
   * @return Swift_Transport_Vendors_SendgridSmtpApi
   */
  public function resetReplacements($replacementKey=null)
  {
    if ($replacementKey !== null)
    {
      unset ($this->_replacements[$replacementKey]);
      unset ($this->_defaults[$replacementKey]);
    }
    else 
    {
      $this->_replacements = array();
    }
    return $this;
  }
  
  /**
   * Set the category
   * @param string $category
   * @return Swift_Transport_Vendors_SendgridSmtpApi
   */
  public function setCategory($category)
  {
    $this->_category = $category;
    return $this;
  }
  
  /**
   * Add filters settings
   * @see http://wiki.sendgrid.com/doku.php?id=filters
   * @param string $filterName
   * @param string $setting
   * @param mixed $value
   * @return Swift_Transport_Vendors_SendgridSmtpApi
   */
  public function addFilterSetting($filterName, $setting, $value)
  {
    if (!isset($this->_filter[$filterName]))
    {
      $this->_filters[$filterName] = array();
      $this->_filters[$filterName]['settings'] = array();
    }
    $this->_filters[$filterName][$settings] = $value;
    return $this;
  }
  
  /**
   * Reset all filters Rules, cleaning up the transport
   * @return Swift_Transport_Vendors_SendgridSmtpApi
   */
  public function resetFilters()
  {
    $this->_filters = array();
    return $this;
  }
  
  /**
   * Allows to change the headerName
   * To change it easily in case of upgrade, or other compatible implementation
   * @param string $headerName
   * @return Swift_Transport_Vendors_SendgridSmtpApi
   */
  public function setHeaderName($headerName)
  {
    $this->_headerName = $headerName;
    return $this;
  }
  
  /**
   * Force usage of X-SMTPAPI To field
   * This requires no replacements pending
   * @param boolean $use
   * @throws Swift_TransportException
   * @return Swift_Transport_Vendors_SendgridSmtpApi
   */
  public function setUseXSmtpApiTo($use)
  {
    if (!$use)
    {
      // Check we have no replacement
      if (!empty($this->_replacements))
      {
        throw new Swift_TransportException('Unable to use normal headers as replacement are pending, please call resetReplacements first');
      }
    }
    $this->_useXSmtpApiTo = $use;
    return $this;
  }

  /**
   * Returns current X-SMTPAPI To field usage status
   * @return boolean
   */
  public function getUseXSmtpApiTo()
  {
    return $this->_useXSmtpApiTo;
  }
  
  /**
   * Returns the json encoded field
   * @param string[] $to [email => name]
   * @param string[] $cc [email => name]
   * @param string[] $bcc [email => name]
   * @return string json
   */
  protected function _getApiHeaderValue($to = array(), $cc = array(), $bcc = array())
  {
    $data = array();
    // Prepare Recipients
    $recipients = array();
    // Merge To
    foreach ($to as $email => $name)
    {
      $recipients[$email] = empty($name)?$email:$name;
    }
    // Merge Cc
    foreach ($cc as $email => $name)
    {
      $recipients[$email] = empty($name)?$email:$name;
    }
    // Merge Bcc
    foreach ($bcc as $email => $name)
    {
      $recipients[$email] = empty($name)?$email:$name;
    }
    if (!empty($recipients))
    {
      // Drop the Keys to unique recipients
      $datas['to']=array_keys($recipients);
      unset ($recipients);
      // Merge replacements
      if (count($this->_replacements)) {
        $data['sub'] = array();
      }
      // Sets the <name> Not sure if it's handled, need to ask
      // $datas['sub']['<name>']=array_values($recipients);
      foreach ($this->_replacements as $replacementKey => $replacementValues)
      {
        $data['sub'][$replacementKey] = array();
        foreach ($datas['to'] as $email)
        {
          $value=$this->_defaults[$replacementKey];
          if (isset($replacementValues[$email]))
          {
            $value=$replacementValues[$email];
          }
          $data['sub'][$replacementKey][]=$value;
        }
      }
    }
    
    if (count($this->_filters))
    {
      $datas['filters'] = $this->_filters;
    }
    $data['category'] = $this->_category;
    // Encode as json
    $json = json_encode($data);
    // Add spaces so that the field can be foldd
    return preg_replace('/(["\]}])([,:])(["\[{])/', '$1$2 $3', $json);
  }
}
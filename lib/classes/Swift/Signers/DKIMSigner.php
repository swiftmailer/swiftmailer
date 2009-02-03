<?php

/*
 DKIM Signature Handler for SwiftMailer
 
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
 * DKIM Signer used to apply DKIM Signature to a message
 * @package Swift
 * @subpackage Signatures
 * @author Xavier De Cock <xdecock@gmail.com>
 */
class Swift_Signers_DKIMSigner implements Swift_Signers_HeaderSigner
{
  /**
   * PrivateKey
   * @var string
   */
  private $_privateKey;
  /**
   * DomainName
   * @var string
   */
  private $_domainName;
  /**
   * Selector
   * @var string
   */
  private $_selector;
  
  /**
   * Hash algorithm used
   * @var string
   */
  private $_hashAlgorithm='rsa-sha1';
  
  /**
   * Body canon method
   * @var string
   */
  private $_bodyCanon='simple';
  
  /**
   * Header canon method
   * @var string
   */
  private $_headerCanon='simple';
  
  /**
   * Headers not being signed
   * @var array
   */
  private $_ignoredHeaders=array();
  
  /**
   * Signer identity
   * @var unknown_type
   */
  private $_signerIdentity;

  /**
   * BodyLength
   * @var int
   */
  private $_bodyLen=0;
  /**
   * Maximum signedLen
   * @var int
   */
  private $_maxLen=PHP_INT_MAX;
  /**
   * Embbed bodyLen in signature
   * @var boolean
   */
  private $_showLen=false;
  
  /**
   * When the signature has been applied (true means time()), false means not embedded
   * @var mixed
   */
  private $_signatureTimestamp=true;
  
  /**
   * When will the signature expires false means not embedded, if sigTimestamp is auto
   * Expiration is relative, otherwhise it's absolute
   * @var int
   */
  private $_signatureExpiration=false;
  
  /**
   * Must we embed signed headers?
   * @var boolean
   */
  private $_debugHeaders=false;
  
  // work variables
  /**
   * Headers used to generate hash
   * @var array
   */
  private $_signedHeaders=array();
  
  /**
   * If debugHeaders is set store debugDatas here
   *
   * @var string
   */
  private $_debugHeadersData='';
  
  /**
   * Stores the bodyHash
   * @var string
   */
  private $_bodyHash='';
  
  /**
   * Stores the signature header
   * @var Swift_Mime_Headers_ParameterizedHeader
   */
  private $_dkimHeader;
  
  /**
   * Hash Handler
   *
   * @var hash_ressource
   */
  private $_headerHashHandler;
  
  private $_bodyHashHandler;
  
  private $_headerHash;
  private $_headerCanonData='';
  
  private $_bodyCanonEmptyCounter=0;
  private $_bodyCanonIgnoreStart=2;
  private $_bodyCanonSpace=false;
  private $_bodyCanonLastChar=null;
  private $_bodyCanonLine='';
  
  private $_bound=array();
  
  /**
   * Constructor
   *
   * @param string $privateKey
   * @param string $domainName
   * @param string $selector
   */
  public function __construct($privateKey, $domainName, $selector)
  {
    $this->_privateKey=$privateKey;
    $this->_domainName=$domainName;
    $this->_signerIdentity='@'.$domainName;
    $this->_selector=$selector;
  }
  
  public function reset()
  {
    $this->_headerHash=null;
    $this->_headerHashHandler=null;
    $this->_bodyHash=null;
    $this->_bodyHashHandler=null;
    $this->_bodyCanonIgnoreStart=2;
    $this->_bodyCanonEmptyCounter=0;
    $this->_bodyCanonLastChar=NULL;
    $this->_bodyCanonSpace=false;
  }
  
  /**
   * Writes $bytes to the end of the stream.
   *
   * Writing may not happen immediately if the stream chooses to buffer.  If
   * you want to write these bytes with immediate effect, call {@link commit()}
   * after calling write().
   *
   * This method returns the sequence ID of the write (i.e. 1 for first, 2 for
   * second, etc etc).
   *
   * @param string $bytes
   * @return int
   * @throws Swift_IoException
   */
  public function write($bytes)
  {
    $this->_canonicalizeBody($bytes);
    foreach($this->_bound as $is){
      $is->write($bytes);
    }
  }
  
  /**
   * For any bytes that are currently buffered inside the stream, force them
   * off the buffer.
   *
   * @throws Swift_IoException
   */
  public function commit()
  {
    // Nothing to do
    return;
  }
  
  /**
   * Attach $is to this stream.
   * The stream acts as an observer, receiving all data that is written.
   * All {@link write()} and {@link flushBuffers()} operations will be mirrored.
   *
   * @param Swift_InputByteStream $is
   */
  public function bind(Swift_InputByteStream $is)
  {
    // Don't have to mirror anything
    $this->_bound[]=$is;
    return;
  }
  
  /**
   * Remove an already bound stream.
   * If $is is not bound, no errors will be raised.
   * If the stream currently has any buffered data it will be written to $is
   * before unbinding occurs.
   *
   * @param Swift_InputByteStream $is
   */
  public function unbind(Swift_InputByteStream $is)
  {
    // Don't have to mirror anything
    foreach($this->_bound as $k=>$el){
      if ($el==$is){
        unset($this->_bound[$k]);
        return;
      }
    }
    return;
  }
  
  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   * @throws Swift_IoException
   */
  public function flushBuffers()
  {
    $this->reset();
  }
  
  /**
   * Set hash_algorithm, must be one of rsa-sha256 | rsa-sha1 defaults to rsa-sha256
   * @param string $hash
   * @return Swift_Signers_DKIMSigner
   */
  public function setHashAlgorithm($hash)
  {
    $this->_hashAlgorithm='rsa-sha1';
    return $this;
    // Unable to sign with rsa-sha256
    if ($hash=='rsa-sha1'){
      $this->_hashAlgorithm='rsa-sha1';
    } else {
      $this->_hashAlgorithm='rsa-sha256';
    }
    return $this;
  }
  
  /**
   * Set the body canonicalization algorithm
   * @param string $canon
   * @return Swift_Signers_DKIMSigner
   */
  public function setBodyCanon($canon)
  {
    if ($canon=='relaxed'){
      $this->_bodyCanon='relaxed';
    } else {
      $this->_bodyCanon='simple';
    }
    return $this;
  }
  
  /**
   * Set the header canonicalization algorithm
   * @param string $canon
   * @return Swift_Signers_DKIMSigner
   */
  public function setHeaderCanon($canon)
  {
    if ($canon=='relaxed'){
      $this->_headerCanon='relaxed';
    } else {
      $this->_headerCanon='simple';
    }
    return $this;
  }
  
  /**
   * Set the signer identity
   * @param string $identity
   * @return Swift_Signers_DKIMSigner
   */
  public function setSignerIdentity($identity)
  {
    $this->_signerIdentity=$identity;
    return $this;
  }
  
  /**
   * Set the length of the body to sign
   * @param mixed $len (bool or int)
   * @return Swift_Signers_DKIMSigner
   */
  public function setBodySignedLen($len)
  {
    if ($this->len===true){
      $this->_showLen=true;
      $this->_maxLen=PHP_INT_MAX;
    } elseif ($this->len===false){
      $this->_showLen=false;
      $this->_maxLen=PHP_INT_MAX;
    } else {
      $this->_showLen=true;
      $this->_maxLen=(int)$len;
    }
    return $this;
  }
  
  /**
   * Set the signature timestamp
   * @param timestamp $time
   * @return Swift_Signers_DKIMSigner
   */
  public function setSignatureTimestamp($time)
  {
    $this->_signatureTimestamp=$time;
    return $this;
  }
  
  /**
   * Set the signature expiration timestamp
   *
   * @param timestamp $time
   * @return Swift_Signers_DKIMSigner
   */
  public function setSignatureExpiration($time)
  {
    $this->_signatureExpiration=$time;
    return $this;
  }
  
  /**
   * Enable / disable the DebugHeaders
   *
   * @param boolean $debug
   * @return Swift_Signers_DKIMSigner
   */
  public function setDebugHeaders($debug)
  {
    $this->_debugHeaders=(bool)$debug;
    return $this;
  }
  
  /**
   * Start Body
   *
   */
  public function startBody(){
    // Init
    switch ($this->_hashAlgorithm){
      case 'rsa-sha256':
        $this->_bodyHashHandler=hash_init('sha256');
        break;
      case 'rsa-sha1':
        $this->_bodyHashHandler=hash_init('sha1');
        break;
    }
    $this->_bodyCanonLine='';
  }
  
  /**
   * End Body
   *
   */
  public function endBody(){
    $this->_endOfBody();
  }
  
  /**
   * Adds an ignored Header
   *
   * @param string $header_name
   * @return Swift_Signers_DKIMSigner
   */
  public function ignoreHeader($header_name)
  {
    $this->_ignoredHeaders[strtolower($header_name)]=true;
    return $this;
  }
  
  
  /**
   * Set the headers to sign
   *
   * @param Swift_Mime_HeaderSet $headers
   * @return Swift_Signers_DKIMSigner
   */
  public function setHeaders(Swift_Mime_HeaderSet $headers)
  {
    $this->_headerCanonData='';
    // Loop through Headers
    $listHeaders=$headers->listAll();
    foreach ($listHeaders as $hName)
    {
      // Check if we need to ignore Header
      if (!isset($this->_ignoredHeaders[strtolower($hName)]))
      {
        $tmp=$headers->getAll($hName);
        if ($headers->has($hName)){
          foreach ($tmp as $header){
            if($header->getFieldBody() != ''){
              $this->_addHeader($header->toString());
              $this->_signedHeaders[]=$header->getFieldName();
            }
          }
        }
      }
    }
    return $this;
  }
  
  /**
   * Add the signature to the given Headers
   *
   * @param Swift_Mime_HeaderSet $headers
   * @return Swift_Signers_DKIMSigner
   */
  public function addSignature(Swift_Mime_HeaderSet $headers)
  {
    // Prepare the DKIM-Signature
    $params=array(
      'v'=>'1',
      'a'=>$this->_hashAlgorithm,
      'bh'=>base64_encode($this->_bodyHash),
      'd'=>$this->_domainName,
      'h'=>implode(':',$this->_signedHeaders),
      'i'=>$this->_signerIdentity,
      's'=>$this->_selector
    );
    if ($this->_bodyCanon!='simple')
    {
      $params['c']=$this->_headerCanon.'/'.$this->_bodyCanon;
    }
    elseif ($this->_headerCanon!='simple')
    {
      $params['c']=$this->_headerCanon;
    }
    if ($this->_showLen)
    {
      $params['l']=$this->_bodyLen;
    }
    if ($this->_signatureTimestamp===true)
    {
      $params['t']=time();
      if ($this->_signatureExpiration!==false)
      {
        $params['x']=$params['t']+$this->_signatureExpiration;
      }
    }
    else
    {
      if ($this->_signatureTimestamp!==false)
      {
        $params['t']=$this->_signatureTimestamp;
      }
      if ($this->_signatureExpiration!==false)
      {
        $params['x']=$this->_signatureExpiration;
      }
    }
    if ($this->_debugHeaders){
      $params['z']=implode('|',$this->_debugHeadersData);
    }
    $string='';
    foreach ($params as $k=>$v)
    {
      $string.=$k.'='.$v.'; ';
    }
    $string=trim($string);
    $headers->addTextHeader('DKIM-Signature', $string);
    // Add the last DKIM-Signature
    $tmp=$headers->getAll('DKIM-Signature');
    $this->_dkimHeader=end($tmp);
    $this->_addHeader(trim($this->_dkimHeader->toString())."\r\n b=", true);
    $this->_endOfHeaders();
    $headers->addTextHeader('X-DebugHash', base64_encode($this->_headerHash));
    $this->_dkimHeader->setValue($string." b=".base64_encode($this->_getEncryptedHash()));
    return $this;
  }
  
  /* Private helpers */
  
  private function _addHeader($header, $is_sig=false)
  {
    switch ($this->_headerCanon)
    {
      case 'relaxed':
        // Prepare Header and cascade
        $exploded= explode(':',$header, 2);
        $name=strtolower(trim($exploded[0]));
        $value=str_replace("\r\n","",$exploded[1]);
        $value=preg_replace("/[ \t][ \t]+/"," ",$value);
        $header=$name.":".trim($value).($is_sig?'':"\r\n");
      case 'simple':
        // Nothing to do
    }
    $this->_addToHeaderHash($header);
  }
  
  private function _endOfHeaders()
  {
    //$this->_headerHash=hash_final($this->_headerHashHandler, true);
  }
  
  private function _canonicalizeBody($string)
  {
    $len=strlen($string);
    $canon='';
    $method=($this->_bodyCanon=="relaxed");
    for ($i=0; $i<$len; ++$i){
      if ($this->_bodyCanonIgnoreStart>0){
        --$this->_bodyCanonIgnoreStart;
        continue;
      }
      switch ($string[$i]){
        case "\r":
          $this->_bodyCanonLastChar="\r";
          break;
        case "\n":
          if ($this->_bodyCanonLastChar=="\r")
          {
            if($method)
            {
              $this->_bodyCanonSpace=false;
            }
            if ($this->_bodyCanonLine=='')
            {
              ++$this->_bodyCanonEmptyCounter;
            }
            else
            {
              $this->_bodyCanonLine='';
              $canon.="\r\n";
            }
          }
          else
          {
            // Wooops Error
            // todo handle it but should never happen
          }
          break;
        case " ":
        case "\t":
          if ($method)
          {
            $this->_bodyCanonSpace=true;
            break;
          }
        default:
          if ($this->_bodyCanonEmptyCounter>0){
            $canon.=str_repeat("\r\n",$this->_bodyCanonEmptyCounter);
            $this->_bodyCanonEmptyCounter=0;
          }
          if ($this->_bodyCanonSpace)
          {
            $this->_bodyCanonLine.=' ';
            $canon.=' ';
            $this->_bodyCanonSpace=false;
          }
          $this->_bodyCanonLine.=$string[$i];
          $canon.=$string[$i];
      }
    }
    $this->_addToBodyHash($canon);
  }
  
  private function _endOfBody()
  {
    $this->_addToBodyHash("\r\n");
    $this->_bodyHash=hash_final($this->_bodyHashHandler, true);
  }
  
  private function _addToBodyHash($string)
  {
    $len=strlen($string);
    if ($len>($new_len=($this->_maxLen-$this->_bodyLen))){
      $string=substr($string,0, $new_len);
      $len=$new_len;
    }
    hash_update($this->_bodyHashHandler, $string);
    $this->_bodyLen+=$len;
  }
  
  private function _addToHeaderHash($header)
  {
    if ($this->_debugHeaders){
      $this->_debugHeadersData[]=trim($header);
    }
    $this->_headerCanonData.=$header;
    //hash_update($this->_headerHashHandler, $header);
  }
  
  private function _getEncryptedHash()
  {
    $signature='';
   	if (openssl_sign($this->_headerCanonData, $signature, $this->_privateKey))
   	{
      return $signature;
  	}
    return '';
  }
}
?>
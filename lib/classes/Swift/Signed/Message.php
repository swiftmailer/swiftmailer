<?php
Class Swift_Signed_Message extends Swift_Message
{
  /**
   * Signature handlers
   *
   * @var Swift_Signer[]
   */
  private $_signers;
  
  /**
   * Attach a new signature handler to the message
   *
   * @param Swift_Signer $signer
   */
  public function attachSigner(Swift_Signer $signer)
  {
    $this->_signers[]=$signer;
  }
  
  /**
   * Get this message as a complete string.
   * @return string
   */
  public function toString()
  {
    // BodySigners
    foreach($this->_signers as $signer)
    {
      if ($signer instanceof Swift_Signers_BodySigner)
      {
        // Do body Signer Specific stuff here
      }
    }
    foreach($this->_signers as $signer)
    {
      if ($signer instanceof Swift_Signers_HeaderSigner)
      {
        // TODO Implement
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
    return parent::toByteStream($is);
  }
}
?>
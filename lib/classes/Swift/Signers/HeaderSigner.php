<?php
interface Swift_Signers_HeaderSigner extends Swift_Signer
{
  /**
   * Exclude an header from the signed headers
   *
   * @param string $header_name
   */
  public function ignoreHeader($header_name);
  /**
   * Set the body of the message if needed by signing algorithm
   *
   * @param Swift_OutputByteStream $os
   */
  public function setBody(Swift_OutputByteStream $os);
  /**
   * Give the headers already given
   *
   * @param Swift_Mime_SimpleHeaderSet $headers
   */
  public function setHeaders (Swift_Mime_HeaderSet $headers);
  
  /**
   * Add the header(s) to the headerSet
   *
   * @param Swift_Mime_HeaderSet $headers
   */
  public function addSignature (Swift_Mime_HeaderSet $headers);
}
?>
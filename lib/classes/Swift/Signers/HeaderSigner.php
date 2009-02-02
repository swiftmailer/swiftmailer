<?php
interface Swift_Signers_HeaderSigner extends Swift_Signer, Swift_InputByteStream
{
  /**
   * Exclude an header from the signed headers
   *
   * @param string $header_name
   * @return Swift_Signers_HeaderSigner
   */
  public function ignoreHeader($header_name);
  /**
   * Prepare the Signer to get a new Body
   * @return Swift_Signers_HeaderSigner
   */
  public function startBody();
  /**
   * Give the signal that the body has finished streaming
   * @return Swift_Signers_HeaderSigner
   */
  public function endBody();
  /**
   * Give the headers already given
   *
   * @param Swift_Mime_SimpleHeaderSet $headers
   * @return Swift_Signers_HeaderSigner
   */
  public function setHeaders (Swift_Mime_HeaderSet $headers);
  
  /**
   * Add the header(s) to the headerSet
   *
   * @param Swift_Mime_HeaderSet $headers
   * @return Swift_Signers_HeaderSigner
   */
  public function addSignature (Swift_Mime_HeaderSet $headers);
}
?>
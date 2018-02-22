<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift\Signers;

use Swift\Signer;
use Swift\InputByteStream;
use Swift\Mime\SimpleHeaderSet;

/**
 * Header Signer Interface used to apply Header-Based Signature to a message.
 *
 * @author Xavier De Cock <xdecock@gmail.com>
 */
interface HeaderSigner extends Signer, InputByteStream
{
    /**
     * Exclude an header from the signed headers.
     *
     * @param string $header_name
     *
     * @return self
     */
    public function ignoreHeader($header_name);

    /**
     * Prepare the Signer to get a new Body.
     *
     * @return self
     */
    public function startBody();

    /**
     * Give the signal that the body has finished streaming.
     *
     * @return self
     */
    public function endBody();

    /**
     * Give the headers already given.
     *
     * @param \Swift\Mime\SimpleHeaderSet $headers
     *
     * @return self
     */
    public function setHeaders(SimpleHeaderSet $headers);

    /**
     * Add the header(s) to the headerSet.
     *
     * @param \Swift\Mime\SimpleHeaderSet $headers
     *
     * @return self
     */
    public function addSignature(SimpleHeaderSet $headers);

    /**
     * Return the list of header a signer might tamper.
     *
     * @return array
     */
    public function getAlteredHeaders();
}

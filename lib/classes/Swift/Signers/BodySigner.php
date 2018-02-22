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
use Swift\Message;

/**
 * Body Signer Interface used to apply Body-Based Signature to a message.
 *
 * @author Xavier De Cock <xdecock@gmail.com>
 */
interface BodySigner extends Signer
{
    /**
     * Change the \Swift\Signed\Message to apply the singing.
     *
     * @param \Swift\Message $message
     *
     * @return self
     */
    public function signMessage(Message $message);

    /**
     * Return the list of header a signer might tamper.
     *
     * @return array
     */
    public function getAlteredHeaders();
}

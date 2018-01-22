<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2018 Christian Schmidt
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Email address encoder.
 *
 * @author Chris Corbyn
 */
interface Swift_AddressEncoder
{
    /**
     * Encodes an email address.
     *
     * @param string $address
     *
     * @return string
     *
     * @throws Swift_AddressEncoderException If the email cannot be represented in
     *                                       the encoding implemented by this class.
     */
    public function encodeString($address);
}


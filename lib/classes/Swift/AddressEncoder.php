<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2018 Christian Schmidt
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swift;

/**
 * Email address encoder.
 *
 * @author Christian Schmidt
 */
interface AddressEncoder
{
    /**
     * Encodes an email address.
     *
     * @throws \Swift\AddressEncoderException If the email cannot be represented in
     *                                       the encoding implemented by this class.
     */
    public function encodeString(string $address): string;
}

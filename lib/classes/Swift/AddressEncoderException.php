<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2018 Christian Schmidt
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * AddressEncoderException when the specified email address is in a format that
 * cannot be encoded by a given address encoder.
 *
 * @author Christian Schmidt
 */
class Swift_AddressEncoderException extends Swift_RfcComplianceException
{
    /** The address that could not be encoded */
    protected $address;

    /**
     * Create a new AddressEncoderException with $message.
     *
     * @param string $message
     */
    public function __construct($address, $message)
    {
        parent::__construct($message);
    }
}

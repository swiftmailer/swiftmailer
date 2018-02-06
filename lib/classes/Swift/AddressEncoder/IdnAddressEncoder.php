<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2018 Christian Schmidt
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An IDN email address encoder.
 *
 * Encodes the domain part of an address using IDN. This is compatible will all
 * SMTP servers.
 *
 * This encoder does not support email addresses with non-ASCII characters in
 * local-part (the substring before @). To send to such addresses, use
 * Swift_AddressEncoder_Utf8AddressEncoder together with
 * Swift_Transport_Esmtp_SmtpUtf8Handler. Your outbound SMTP server must support
 * the SMTPUTF8 extension.
 *
 * @author Christian Schmidt
 */
class Swift_AddressEncoder_IdnAddressEncoder implements Swift_AddressEncoder
{
    /**
     * Encodes the domain part of an address using IDN.
     *
     * @throws Swift_AddressEncoderException If local-part contains non-ASCII characters
     */
    public function encodeString(string $address): string
    {
        $i = strrpos($address, '@');
        if (false !== $i) {
            $local = substr($address, 0, $i);
            $domain = substr($address, $i + 1);

            if (preg_match('/[^\x00-\x7F]/', $local)) {
                throw new Swift_AddressEncoderException('Non-ASCII characters not supported in local-part', $address);
            }

            $address = sprintf('%s@%s', $local, $this->idnToAscii($domain));
        }

        return $address;
    }

    /**
     * IDN-encodes a UTF-8 string to ASCII.
     */
    protected function idnToAscii(string $string): string
    {
        if (function_exists('idn_to_ascii')) {
            return idn_to_ascii($string, 0, INTL_IDNA_VARIANT_UTS46);
        }

        if (class_exists('TrueBV\Punycode')) {
            $punycode = new \TrueBV\Punycode();

            return $punycode->encode($string);
        }

        throw new Swift_SwiftException('No IDN encoder found (install the intl extension or the true/punycode package');
    }
}

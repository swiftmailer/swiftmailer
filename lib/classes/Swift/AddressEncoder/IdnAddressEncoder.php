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
 * @author Christian Schmidt
 */
class Swift_AddressEncoder_IdnAddressEncoder implements Swift_AddressEncoder
{
    /**
     * Encodes the domain part of an address using IDN.
     *
     * @param string $address
     *
     * @return string
     *
     * @throws Swift_AddressEncoderException If local-part contains non-ASCII characters
     */
    public function encodeString($address)
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
     *
     * @param  string $string
     * @return string
     */
    protected function idnToAscii($string)
    {
        if (function_exists('idn_to_ascii')) {
            return idn_to_ascii($string, INTL_IDNA_VARIANT_UTS46);
        }

        if (class_exists('TrueBV\Punycode')) {
            $punycode = new \TrueBV\Punycode();
            return $punycode->encode($string);
        }

        throw new Swift_SwiftException('No IDN encoder found (install the intl extension or the true/punycode package');
    }
}


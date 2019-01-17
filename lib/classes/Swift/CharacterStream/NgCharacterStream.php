<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

@trigger_error(sprintf('The "%s" class is deprecated since Swiftmailer 6.2; use "%s" instead.', Swift_CharacterStream_NgCharacterStream::class, Swift_CharacterStream_CharacterStream::class), E_USER_DEPRECATED);

/**
 * A CharacterStream implementation which stores characters in an internal array.
 *
 * @author     Xavier De Cock <xdecock@gmail.com>
 */
class Swift_CharacterStream_NgCharacterStream extends Swift_CharacterStream_CharacterStream
{
}

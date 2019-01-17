<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

@trigger_error(sprintf('The "%s" class is deprecated since Swiftmailer 6.2; use "%s" instead.', Swift_Mime_ContentEncoder_NativeQpContentEncoder::class, Swift_Mime_ContentEncoder_QpContentEncoder::class), E_USER_DEPRECATED);

class Swift_Mime_ContentEncoder_NativeQpContentEncoder extends Swift_Mime_ContentEncoder_QpContentEncoder
{
}

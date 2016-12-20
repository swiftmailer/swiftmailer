Changelog
=========

5.4.5 (2016-XX-XX)
------------------

5.4.4 (2016-11-23)
------------------

 * reverted escaping command-line args to mail (PHP mail() function already does it)

5.4.3 (2016-07-08)
------------------

 * fixed SimpleHeaderSet::has()/get() when the 0 index is removed
 * removed the need to have mcrypt installed
 * fixed broken MIME header encoding with quotes/colons and non-ascii chars
 * allowed mail transport send for messages without To header
 * fixed PHP 7 support

5.4.2 (2016-05-01)
------------------

 * fixed support for IPv6 sockets
 * added auto-retry when sending messages from the memory spool
 * fixed consecutive read calls in Swift_ByteStream_FileByteStream
 * added support for iso-8859-15 encoding
 * fixed PHP mail extra params on missing reversePath
 * added methods to set custom stream context options
 * fixed charset changes in QpContentEncoderProxy
 * added return-path header to the ignoredHeaders list of DKIMSigner
 * fixed crlf for subject using mail
 * fixed add soft line break only when necessary
 * fixed escaping command-line args to mail

5.4.1 (2015-06-06)
------------------

 * made Swiftmailer exceptions confirm to PHP base exception constructor signature
 * fixed MAIL FROM & RCPT TO headers to be RFC compliant

5.4.0 (2015-03-14)
------------------

 * added the possibility to add extra certs to PKCS#7 signature
 * fix base64 encoding with streams
 * added a new RESULT_SPOOLED status for SpoolTransport
 * fixed getBody() on attachments when called more than once
 * removed dots from generated filenames in filespool

5.3.1 (2014-12-05)
------------------

 * fixed cloning of messages with attachments

5.3.0 (2014-10-04)
------------------

 * fixed cloning when using signers
 * reverted removal of Swift_Encoding
 * drop support for PHP 5.2.x

5.2.2 (2014-09-20)
------------------

 * fixed Japanese support
 * fixed the memory spool when the message changes when in the pool
 * added support for cloning messages
 * fixed PHP warning in the redirect plugin
 * changed the way to and cc-ed email are sent to only use one transaction

5.2.1 (2014-06-13)
------------------

 * SECURITY FIX: fixed CLI escaping when using sendmail as a transport

   Prior to 5.2.1, the sendmail transport (Swift_Transport_SendmailTransport)
   was vulnerable to an arbitrary shell execution if the "From" header came
   from a non-trusted source and no "Return-Path" is configured.

 * fixed parameter in DKIMSigner
 * fixed compatibility with PHP < 5.4

5.2.0 (2014-05-08)
------------------

 * fixed Swift_ByteStream_FileByteStream::read() to match to the specification
 * fixed from-charset and to-charset arguments in mbstring_convert_encoding() usages
 * fixed infinite loop in StreamBuffer
 * fixed NullTransport to return the number of ignored emails instead of 0
 * Use phpunit and mockery for unit testing (realityking)

5.1.0 (2014-03-18)
------------------

 * fixed data writing to stream when sending large messages
 * added support for libopendkim (https://github.com/xdecock/php-opendkim)
 * merged SignedMessage and Message
 * added Gmail XOAuth2 authentication
 * updated the list of known mime types
 * added NTLM authentication

5.0.3 (2013-12-03)
------------------

 * fixed double-dot bug
 * fixed DKIM signer

5.0.2 (2013-08-30)
------------------

 * handled correct exception type while reading IoBuffer output

5.0.1 (2013-06-17)
------------------

 * changed the spool to only start the transport when a mail has to be sent
 * fixed compatibility with PHP 5.2
 * fixed LICENSE file

5.0.0 (2013-04-30)
------------------

 * changed the license from LGPL to MIT

4.3.1 (2013-04-11)
------------------

 * removed usage of the native QP encoder when the charset is not UTF-8
 * fixed usage of uniqid to avoid collisions
 * made a performance improvement when tokenizing large headers
 * fixed usage of the PHP native QP encoder on PHP 5.4.7+

4.3.0 (2013-01-08)
------------------

 * made the temporary directory configurable via the TMPDIR env variable
 * added S/MIME signer and encryption support

4.2.2 (2012-10-25)
------------------

 * added the possibility to throttle messages per second in ThrottlerPlugin (mostly for Amazon SES)
 * switched mime.qpcontentencoder to automatically use the PHP native encoder on PHP 5.4.7+
 * allowed specifying a whitelist with regular expressions in RedirectingPlugin

4.2.1 (2012-07-13)
------------------

 * changed the coding standards to PSR-1/2
 * fixed issue with autoloading
 * added NativeQpContentEncoder to enhance performance (for PHP 5.3+)

4.2.0 (2012-06-29)
------------------

 * added documentation about how to use the Japanese support introduced in 4.1.8
 * added a way to override the default configuration in a lazy way
 * changed the PEAR init script to lazy-load the initialization
 * fixed a bug when calling Swift_Preferences before anything else (regression introduced in 4.1.8)

4.1.8 (2012-06-17)
------------------

 * added Japanese iso-2022-jp support
 * changed the init script to lazy-load the initialization
 * fixed docblocks (@id) which caused some problems with libraries parsing the dobclocks
 * fixed Swift_Mime_Headers_IdentificationHeader::setId() when passed an array of ids
 * fixed encoding of email addresses in headers
 * added replacements setter to the Decorator plugin

4.1.7 (2012-04-26)
------------------

 * fixed QpEncoder safeMapShareId property

4.1.6 (2012-03-23)
------------------

 * reduced the size of serialized Messages

4.1.5 (2012-01-04)
------------------

 * enforced Swift_Spool::queueMessage() to return a Boolean
 * made an optimization to the memory spool: start the transport only when required
 * prevented stream_socket_client() from generating an error and throw a Swift_TransportException instead
 * fixed a PHP warning when calling to mail() when safe_mode is off
 * many doc tweaks

4.1.4 (2011-12-16)
------------------

 * added a memory spool (Swift_MemorySpool)
 * fixed too many opened files when sending emails with attachments

4.1.3 (2011-10-27)
------------------

 * added STARTTLS support
 * added missing @return tags on fluent methods
 * added a MessageLogger plugin that logs all sent messages
 * added composer.json

4.1.2 (2011-09-13)
------------------

 * fixed wrong detection of magic_quotes_runtime
 * fixed fatal errors when no To or Subject header has been set
 * fixed charset on parameter header continuations
 * added documentation about how to install Swiftmailer from the PEAR channel
 * fixed various typos and markup problem in the documentation
 * fixed warning when cache directory does not exist
 * fixed "slashes are escaped" bug
 * changed require_once() to require() in autoload

4.1.1 (2011-07-04)
------------------

 * added missing file in PEAR package

4.1.0 (2011-06-30)
------------------

 * documentation has been converted to ReST

4.1.0 RC1 (2011-06-17)
----------------------

New features:

 * changed the Decorator Plugin to allow replacements in all headers
 * added Swift_Mime_Grammar and Swift_Validate to validate an email address
 * modified the autoloader to lazy-initialize Swiftmailer
 * removed Swift_Mailer::batchSend()
 * added NullTransport
 * added new plugins: RedirectingPlugin and ImpersonatePlugin
 * added a way to send messages asynchronously (Spool)

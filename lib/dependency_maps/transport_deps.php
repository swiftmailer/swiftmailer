<?php

\Swift\DependencyContainer::getInstance()
    ->register('transport.localdomain')
    // As SERVER_NAME can come from the user in certain configurations, check that
    // it does not contain forbidden characters (see RFC 952 and RFC 2181). Use
    // preg_replace() instead of preg_match() to prevent DoS attacks with long host names.
    ->asValue(!empty($_SERVER['SERVER_NAME']) && '' === preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $_SERVER['SERVER_NAME']) ? trim($_SERVER['SERVER_NAME'], '[]') : '127.0.0.1')

    ->register('transport.smtp')
    ->asNewInstanceOf(\Swift\Transport\EsmtpTransport::class)
    ->withDependencies([
        'transport.buffer',
        'transport.smtphandlers',
        'transport.eventdispatcher',
        'transport.localdomain',
        'address.idnaddressencoder',
    ])

    ->register('transport.sendmail')
    ->asNewInstanceOf(\Swift\Transport\SendmailTransport::class)
    ->withDependencies([
        'transport.buffer',
        'transport.eventdispatcher',
        'transport.localdomain',
    ])

    ->register('transport.loadbalanced')
    ->asNewInstanceOf(\Swift\Transport\LoadBalancedTransport::class)

    ->register('transport.failover')
    ->asNewInstanceOf(\Swift\Transport\FailoverTransport::class)

    ->register('transport.spool')
    ->asNewInstanceOf(\Swift\Transport\SpoolTransport::class)
    ->withDependencies(['transport.eventdispatcher'])

    ->register('transport.null')
    ->asNewInstanceOf(\Swift\Transport\NullTransport::class)
    ->withDependencies(['transport.eventdispatcher'])

    ->register('transport.buffer')
    ->asNewInstanceOf(\Swift\Transport\StreamBuffer::class)
    ->withDependencies(['transport.replacementfactory'])

    ->register('transport.smtphandlers')
    ->asArray()
    ->withDependencies(['transport.authhandler', 'transport.smtputf8handler'])

    ->register('transport.authhandler')

    ->asNewInstanceOf(\Swift\Transport\Esmtp\AuthHandler::class)
    ->withDependencies(['transport.authhandlers'])

    ->register('transport.authhandlers')
    ->asArray()

    ->withDependencies([
        'transport.crammd5auth',
        'transport.loginauth',
        'transport.plainauth',
        'transport.ntlmauth',
        'transport.xoauth2auth',
    ])

    ->register('transport.smtputf8handler')
    ->asNewInstanceOf(\Swift\Transport\Esmtp\SmtpUtf8Handler::class)

    ->register('transport.crammd5auth')
    ->asNewInstanceOf(\Swift\Transport\Esmtp\Auth\CramMd5Authenticator::class)

    ->register('transport.loginauth')
    ->asNewInstanceOf(\Swift\Transport\Esmtp\Auth\LoginAuthenticator::class)

    ->register('transport.plainauth')
    ->asNewInstanceOf(\Swift\Transport\Esmtp\Auth\PlainAuthenticator::class)

    ->register('transport.xoauth2auth')
    ->asNewInstanceOf(\Swift\Transport\Esmtp\Auth\XOAuth2Authenticator::class)

    ->register('transport.ntlmauth')
    ->asNewInstanceOf(\Swift\Transport\Esmtp\Auth\NTLMAuthenticator::class)

    ->register('transport.eventdispatcher')
    ->asNewInstanceOf(\Swift\Events\SimpleEventDispatcher::class)

    ->register('transport.replacementfactory')
    ->asSharedInstanceOf(\Swift\StreamFilters\StringReplacementFilterFactory::class)

    ->register('address.idnaddressencoder')
    ->asNewInstanceOf(\Swift\AddressEncoder\IdnAddressEncoder::class)

    ->register('address.utf8addressencoder')
    ->asNewInstanceOf(\Swift\AddressEncoder\Utf8AddressEncoder::class)
;

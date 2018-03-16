<?php

require __DIR__.'/../mime_types.php';

\Swift\DependencyContainer::getInstance()
    ->register('properties.charset')
    ->asValue('utf-8')

    ->register('email.validator')
    ->asSharedInstanceOf(\Egulias\EmailValidator\EmailValidator::class)

    ->register('mime.idgenerator.idright')
    // As SERVER_NAME can come from the user in certain configurations, check that
    // it does not contain forbidden characters (see RFC 952 and RFC 2181). Use
    // preg_replace() instead of preg_match() to prevent DoS attacks with long host names.
    ->asValue(!empty($_SERVER['SERVER_NAME']) && '' === preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'swift.generated')

    ->register('mime.idgenerator')
    ->asSharedInstanceOf(\Swift\Mime\IdGenerator::class)
    ->withDependencies([
        'mime.idgenerator.idright',
    ])

    ->register('mime.message')
    ->asNewInstanceOf(\Swift\Mime\SimpleMessage::class)
    ->withDependencies([
        'mime.headerset',
        'mime.qpcontentencoder',
        'cache',
        'mime.idgenerator',
        'properties.charset',
    ])

    ->register('mime.part')
    ->asNewInstanceOf(\Swift\Mime\MimePart::class)
    ->withDependencies([
        'mime.headerset',
        'mime.qpcontentencoder',
        'cache',
        'mime.idgenerator',
        'properties.charset',
    ])

    ->register('mime.attachment')
    ->asNewInstanceOf(\Swift\Mime\Attachment::class)
    ->withDependencies([
        'mime.headerset',
        'mime.base64contentencoder',
        'cache',
        'mime.idgenerator',
    ])
    ->addConstructorValue($swift_mime_types)

    ->register('mime.embeddedfile')
    ->asNewInstanceOf(\Swift\Mime\EmbeddedFile::class)
    ->withDependencies([
        'mime.headerset',
        'mime.base64contentencoder',
        'cache',
        'mime.idgenerator',
    ])
    ->addConstructorValue($swift_mime_types)

    ->register('mime.headerfactory')
    ->asNewInstanceOf(\Swift\Mime\SimpleHeaderFactory::class)
    ->withDependencies([
        'mime.qpheaderencoder',
        'mime.rfc2231encoder',
        'email.validator',
        'properties.charset',
        'address.idnaddressencoder',
    ])

    ->register('mime.headerset')
    ->asNewInstanceOf(\Swift\Mime\SimpleHeaderSet::class)
    ->withDependencies(['mime.headerfactory', 'properties.charset'])

    ->register('mime.qpheaderencoder')
    ->asNewInstanceOf(\Swift\Mime\HeaderEncoder\QpHeaderEncoder::class)
    ->withDependencies(['mime.charstream'])

    ->register('mime.base64headerencoder')
    ->asNewInstanceOf(\Swift\Mime\HeaderEncoder\Base64HeaderEncoder::class)
    ->withDependencies(['mime.charstream'])

    ->register('mime.charstream')
    ->asNewInstanceOf(\Swift\CharacterStream\NgCharacterStream::class)
    ->withDependencies(['mime.characterreaderfactory', 'properties.charset'])

    ->register('mime.bytecanonicalizer')
    ->asSharedInstanceOf(\Swift\StreamFilters\ByteArrayReplacementFilter::class)
    ->addConstructorValue([[0x0D, 0x0A], [0x0D], [0x0A]])
    ->addConstructorValue([[0x0A], [0x0A], [0x0D, 0x0A]])

    ->register('mime.characterreaderfactory')
    ->asSharedInstanceOf(\Swift\CharacterReaderFactory\SimpleCharacterReaderFactory::class)

    ->register('mime.safeqpcontentencoder')
    ->asNewInstanceOf(\Swift\Mime\ContentEncoder\QpContentEncoder::class)
    ->withDependencies(['mime.charstream', 'mime.bytecanonicalizer'])

    ->register('mime.rawcontentencoder')
    ->asNewInstanceOf(\Swift\Mime\ContentEncoder\RawContentEncoder::class)

    ->register('mime.nativeqpcontentencoder')
    ->withDependencies(['properties.charset'])
    ->asNewInstanceOf(\Swift\Mime\ContentEncoder\NativeQpContentEncoder::class)

    ->register('mime.qpcontentencoder')
    ->asNewInstanceOf(\Swift\Mime\ContentEncoder\QpContentEncoderProxy::class)
    ->withDependencies(['mime.safeqpcontentencoder', 'mime.nativeqpcontentencoder', 'properties.charset'])

    ->register('mime.7bitcontentencoder')
    ->asNewInstanceOf(\Swift\Mime\ContentEncoder\PlainContentEncoder::class)
    ->addConstructorValue('7bit')
    ->addConstructorValue(true)

    ->register('mime.8bitcontentencoder')
    ->asNewInstanceOf(\Swift\Mime\ContentEncoder\PlainContentEncoder::class)
    ->addConstructorValue('8bit')
    ->addConstructorValue(true)

    ->register('mime.base64contentencoder')
    ->asSharedInstanceOf(\Swift\Mime\ContentEncoder\Base64ContentEncoder::class)

    ->register('mime.rfc2231encoder')
    ->asNewInstanceOf(\Swift\Encoder\Rfc2231Encoder::class)
    ->withDependencies(['mime.charstream'])
;

unset($swift_mime_types);

<?php

use Swift\KeyCache\NullKeyCache;
use Swift\KeyCache\ArrayKeyCache;
use Swift\KeyCache\DiskKeyCache;
use Swift\KeyCache\SimpleKeyCacheInputStream;

\Swift\DependencyContainer::getInstance()
    ->register('cache')
    ->asAliasOf('cache.array')

    ->register('tempdir')
    ->asValue('/tmp')

    ->register('cache.null')
    ->asSharedInstanceOf(NullKeyCache::class)

    ->register('cache.array')
    ->asSharedInstanceOf(ArrayKeyCache::class)
    ->withDependencies(['cache.inputstream'])

    ->register('cache.disk')
    ->asSharedInstanceOf(DiskKeyCache::class)
    ->withDependencies(['cache.inputstream', 'tempdir'])

    ->register('cache.inputstream')
    ->asNewInstanceOf(SimpleKeyCacheInputStream::class)
;

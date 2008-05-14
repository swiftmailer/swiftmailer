<?php

Swift_DependencyContainer::getInstance()
  
  -> register('cache')
  -> asAliasOf('cache.array')
  
  -> register('cache.array')
  -> asSharedInstanceOf('Swift_KeyCache_ArrayKeyCache')
  -> withDependencies(array('cache.inputstream'))
  
  -> register('cache.inputstream')
  -> asNewInstanceOf('Swift_KeyCache_SimpleKeyCacheInputStream')
  
  ;

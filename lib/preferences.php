<?php

/****************************************************************************/
/*                                                                          */
/* YOU MAY WISH TO MODIFY OR REMOVE THE FOLLOWING LINES WHICH SET DEFAULTS  */
/*                                                                          */
/****************************************************************************/

// Sets the default charset so that setCharset() is not needed elsewhere
Swift_Preferences::getInstance()->setCharset('utf-8');

// Without these lines the default caching mechanism is "array" but this uses
// a lot of memory.
// If possible, use a disk cache to enable attaching large attachments etc

$tmp = getenv('TMPDIR') ?: (function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : false);

if ($tmp && is_writable($tmp))
{
  Swift_Preferences::getInstance()
    -> setTempDir($tmp)
    -> setCacheType('disk');
}

Swift_Preferences::getInstance()->setQPDotEscape(false);

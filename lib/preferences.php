<?php

/****************************************************************************/
/*                                                                          */
/* YOU MAY WISH TO MODIFY OR REMOVE THE FOLLOWING LINES WHICH SET DEFAULTS  */
/*                                                                          */
/****************************************************************************/

// Sets the default charset so that setCharset() is not needed elsewhere
Swift_Preferences::getInstance()->setCharset('utf-8');

// Without these lines the default caching mechanism is "array" but this uses a lot of memory.
// If possible, use a disk cache to enable attaching large attachments etc.
// You can override the default temporary directory by setting the TMPDIR environment variable.

// The @ operator in front of is_writable calls is to avoid PHP warnings
// when using open_basedir
$tmp = getenv('TMPDIR');
// @ operator suppress  warning thrown when the directory is restricted by PHP open_basedir configuration
if ($tmp && @is_writable($tmp)) {
    Swift_Preferences::getInstance()
        ->setTempDir($tmp)
        ->setCacheType('disk');
// @ operator suppress  warning thrown when the directory is restricted by PHP open_basedir configuration
} elseif (function_exists('sys_get_temp_dir') && @is_writable(sys_get_temp_dir())) {
    Swift_Preferences::getInstance()
        ->setTempDir(sys_get_temp_dir())
        ->setCacheType('disk');
}

Swift_Preferences::getInstance()->setQPDotEscape(false);

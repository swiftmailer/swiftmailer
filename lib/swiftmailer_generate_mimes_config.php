#!/usr/bin/php

<?php
define('APACHE_MIME_TYPES_URL', 'https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');
define('FREEDESKTOP_XML_URL', 'https://raw.github.com/minad/mimemagic/master/script/freedesktop.org.xml');

function generateUpToDateMimeArray()
{
    $preamble = "<?php\n\n";
    $preamble .= "/*\n";
    $preamble .= " * This file is part of SwiftMailer.\n";
    $preamble .= " * (c) 2004-2009 Chris Corbyn\n *\n";
    $preamble .= " * For the full copyright and license information, please view the LICENSE\n";
    $preamble .= " * file that was distributed with this source code.\n *\n";
    $preamble .= " * autogenerated using https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types\n";
    $preamble .= " * and https://raw.github.com/minad/mimemagic/master/script/freedesktop.org.xml\n";
    $preamble .= " */\n\n";
    $preamble .= "/*\n";
    $preamble .= " * List of MIME type automatically detected in Swift Mailer.\n";
    $preamble .= " */\n\n";
    $preamble .= "// You may add or take away what you like (lowercase required)\n\n";

    // get current mime types files
    $mime_types = @file_get_contents(APACHE_MIME_TYPES_URL);
    $mime_xml = @file_get_contents(FREEDESKTOP_XML_URL);

    // prepare valid mime types
    $valid_mime_types = [];

    // split mime type and extensions eg. "video/x-matroska        mkv mk3d mks"
    if (preg_match_all('/^#?([a-z0-9\-\+\/\.]+)[\t]+(.*)$/miu', $mime_types, $matches) !== false) {
        // collection of predefined mimetypes (bugfix for wrong resolved or missing mime types)
        $valid_mime_types_preset = [
            'php' => 'application/x-php',
            'php3' => 'application/x-php',
            'php4' => 'application/x-php',
            'php5' => 'application/x-php',
            'zip' => 'application/zip',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'css' => 'text/css',
            'js' => 'text/javascript',
            'txt' => 'text/plain',
            'aif' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'avi' => 'video/avi',
            'bmp' => 'image/bmp',
            'bz2' => 'application/x-bz2',
            'csv' => 'text/csv',
            'dmg' => 'application/x-apple-diskimage',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'eml' => 'message/rfc822',
            'aps' => 'application/postscript',
            'exe' => 'application/x-ms-dos-executable',
            'flv' => 'video/x-flv',
            'gz' => 'application/x-gzip',
            'hqx' => 'application/stuffit',
            'htm' => 'text/html',
            'html' => 'text/html',
            'jar' => 'application/x-java-archive',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'm3u' => 'audio/x-mpegurl',
            'm4a' => 'audio/mp4',
            'mdb' => 'application/x-msaccess',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mov' => 'video/quicktime',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'odg' => 'vnd.oasis.opendocument.graphics',
            'odp' => 'vnd.oasis.opendocument.presentation',
            'odt' => 'vnd.oasis.opendocument.text',
            'ods' => 'vnd.oasis.opendocument.spreadsheet',
            'ogg' => 'audio/ogg',
            'pdf' => 'application/pdf',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ps' => 'application/postscript',
            'rar' => 'application/x-rar-compressed',
            'rtf' => 'application/rtf',
            'tar' => 'application/x-tar',
            'sit' => 'application/x-stuffit',
            'svg' => 'image/svg+xml',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'ttf' => 'application/x-font-truetype',
            'vcf' => 'text/x-vcard',
            'wav' => 'audio/wav',
            'wma' => 'audio/x-ms-wma',
            'wmv' => 'audio/x-ms-wmv',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xml' => 'application/xml',
        ];

        // wrap array for generating file
        foreach ($valid_mime_types_preset as $extension => $mime_type) {
            // generate array for mimetype to extension resolver (only first match)
            $valid_mime_types[$extension] = "'{$extension}' => '{$mime_type}'";
        }

        // collect extensions
        $valid_extensions = [];

        // all extensions from second match
        foreach ($matches[2] as $i => $extensions) {
            // explode multiple extensions from string
            $extensions = explode(' ', strtolower($extensions));

            // force array for foreach
            if (!is_array($extensions)) {
                $extensions = [$extensions];
            }

            foreach ($extensions as $extension) {
                // get mime type
                $mime_type = $matches[1][$i];

                // check if string length lower than 10
                if (strlen($extension) < 10) {
                    // add extension
                    $valid_extensions[] = $extension;

                    if (!isset($valid_mime_types[$mime_type])) {
                        // generate array for mimetype to extension resolver (only first match)
                        $valid_mime_types[$extension] = "'{$extension}' => '{$mime_type}'";
                    }
                }
            }
        }
    }

    $xml = simplexml_load_string($mime_xml);

    foreach ($xml as $node) {
        // check if there is no pattern
        if (!isset($node->glob['pattern'])) {
            continue;
        }

        // get all matching extensions from match
        foreach ((array) $node->glob['pattern'] as $extension) {
            // skip none glob extensions
            if (strpos($extension, '.') === false) {
                continue;
            }

            // remove get only last part
            $extension = explode('.', strtolower($extension));
            $extension = end($extension);

            // maximum length in database column
            if (strlen($extension) <= 9) {
                $valid_extensions[] = $extension;
            }
        }

        if (isset($node->glob['pattern'][0])) {
            // mime type
            $mime_type = strtolower((string) $node['type']);

            // get first extension
            $extension = strtolower(trim($node->glob['ddpattern'][0], '*.'));

            // skip none glob extensions and check if string length between 1 and 10
            if (strpos($extension, '.') !== false || strlen($extension) < 1 || strlen($extension) > 9) {
                continue;
            }

            // check if string length lower than 10
            if (!isset($valid_mime_types[$mime_type])) {
                // generate array for mimetype to extension resolver (only first match)
                $valid_mime_types[$extension] = "'{$extension}' => '{$mime_type}'";
            }
        }
    }

    // full list of valid extensions only
    $valid_mime_types = array_unique($valid_mime_types);
    ksort($valid_mime_types);

    // combine mime types and extensions array
    $output = "$preamble\$swift_mime_types = array(\n    ".implode($valid_mime_types, ",\n    ")."\n);";

    // write mime_types.php config file
    @file_put_contents('./mime_types.php', $output);
}

generateUpToDateMimeArray();

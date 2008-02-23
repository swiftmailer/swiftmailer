<?php
set_time_limit(0);
require_once('PEAR/PackageFileManager.php');
require_once('PEAR/PackageFileManager2.php');
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$packagedir = dirname(dirname(__FILE__));
$notes = '
 - fixed these bugs reported at sourceforge
 [ 1544977 ] Command line arguments split by spaces (fix included)
 [ 1545927 ] HTMLSmartyConverter logo not in package
 [ 1573807 ] CHM Convertor does not strip full path for all TOC entries
 [ 1575145 ] \'-q on\' quiet mode not fully quiet
 [ 1581487 ] subdirectory include another subdirectory doc error.
 - fixed these bugs reported in PEAR:
  Bug #8520: Broken XML when XML_Beautifier is installed
  Bug #8533: require() broken in a lot of places - cannot run!
  Bug #8539: Parser error in create_examples.php
  Bug #8746: XML:DocBook generates warning with function/parameters without @param tag
  Bug #8773: PDF Convertor fails due to being unable to find fonts
  Bug #8914: returning non varibale reference ParserData.inc#500

  The peardoc2 converter no longer uses funky PHP source highlighting, leaving
that to the peardoc build instead.
';
$version = '1.3.1';
$options = array(
'baseinstalldir' => 'PhpDocumentor',
'version' => $version,
'packagedirectory' => $packagedir,
'filelistgenerator' => 'cvs',
'notes' => $notes,
'package' => 'PhpDocumentor',
'dir_roles' => array(
    'Documentation' => 'doc',
    'Documentation/tests' => 'test',
    'docbuilder' => 'data',
    'HTML_TreeMenu-1.1.2' => 'data',
    'tutorials' => 'doc',
    ),
'simpleoutput' => true,
'exceptions' =>
    array(
        'index.html' => 'data',
        'README' => 'doc',
        'ChangeLog' => 'doc',
        'LICENSE' => 'doc',
        'poweredbyphpdoc.gif' => 'data',
        'INSTALL' => 'doc',
        'FAQ' => 'doc',
        'Authors' => 'doc',
        'Release-1.2.0beta1' => 'doc',
        'Release-1.2.0beta2' => 'doc',
        'Release-1.2.0beta3' => 'doc',
        'Release-1.2.0rc1' => 'doc',
        'Release-1.2.0rc2' => 'doc',
        'Release-1.2.0' => 'doc',
        'Release-1.2.1' => 'doc',
        'Release-1.2.2' => 'doc',
        'Release-1.2.3' => 'doc',
        'Release-1.2.3.1' => 'doc',
        'Release-1.3.0' => 'doc',
        'Release-1.3.1' => 'doc',
        'pear-phpdoc' => 'script',
        'pear-phpdoc.bat' => 'script',
        'HTML_TreeMenu-1.1.2/TreeMenu.php' => 'php',
        'phpDocumentor/Smarty-2.6.0/libs/debug.tpl' => 'php',
        'new_phpdoc.php' => 'data',
        'phpdoc.php' => 'data',
        ),
'ignore' =>
    array('package.xml',
          'package2.xml',
          '*templates/PEAR/*',
          'publicweb-PEAR-1.2.1.patch.txt',
          ),
'installexceptions' => array('pear-phpdoc' => '/', 'pear-phpdoc.bat' => '/', 'scripts/makedoc.sh' => '/'),
);
$pfm2 = PEAR_PackageFileManager2::importOptions(dirname(dirname(__FILE__))
    . DIRECTORY_SEPARATOR . 'package.xml', array_merge($options, array('packagefile' => 'package.xml')));
$pfm2->setReleaseVersion($version);
$pfm2->setReleaseStability('stable');
$pfm2->setLicense('LGPL', 'http://www.opensource.org/licenses/lgpl-license.php');
$pfm2->setNotes($notes);
$pfm2->clearDeps();
$pfm2->setPhpDep('4.2.0');
$pfm2->setPearinstallerDep('1.4.6');
$pfm2->addPackageDepWithChannel('optional', 'XML_Beautifier', 'pear.php.net', '1.1');
$pfm2->addReplacement('pear-phpdoc', 'pear-config', '@PHP-BIN@', 'php_bin');
$pfm2->addReplacement('pear-phpdoc.bat', 'pear-config', '@PHP-BIN@', 'php_bin');
$pfm2->addReplacement('pear-phpdoc.bat', 'pear-config', '@BIN-DIR@', 'bin_dir');
$pfm2->addReplacement('pear-phpdoc.bat', 'pear-config', '@PEAR-DIR@', 'php_dir');
$pfm2->addReplacement('pear-phpdoc.bat', 'pear-config', '@DATA-DIR@', 'data_dir');
$pfm2->addReplacement('docbuilder/includes/utilities.php', 'pear-config', '@DATA-DIR@', 'data_dir');
$pfm2->addReplacement('docbuilder/builder.php', 'pear-config', '@DATA-DIR@', 'data_dir');
$pfm2->addReplacement('docbuilder/file_dialog.php', 'pear-config', '@DATA-DIR@', 'data_dir');
$pfm2->addReplacement('docbuilder/file_dialog.php', 'pear-config', '@WEB-DIR@', 'data_dir');
$pfm2->addReplacement('docbuilder/actions.php', 'pear-config', '@WEB-DIR@', 'data_dir');
$pfm2->addReplacement('docbuilder/top.php', 'pear-config', '@DATA-DIR@', 'data_dir');
$pfm2->addReplacement('docbuilder/config.php', 'pear-config', '@DATA-DIR@', 'data_dir');
$pfm2->addReplacement('docbuilder/config.php', 'pear-config', '@WEB-DIR@', 'data_dir');
$pfm2->addReplacement('phpDocumentor/Setup.inc.php', 'pear-config', '@DATA-DIR@', 'data_dir');
$pfm2->addReplacement('phpDocumentor/Converter.inc', 'pear-config', '@DATA-DIR@', 'data_dir');
$pfm2->addReplacement('phpDocumentor/common.inc.php', 'package-info', '@VER@', 'version');
$pfm2->addReplacement('phpDocumentor/common.inc.php', 'pear-config', '@PEAR-DIR@', 'php_dir');
$pfm2->addReplacement('phpDocumentor/IntermediateParser.inc', 'package-info', '@VER@', 'version');
$pfm2->addReplacement('phpDocumentor/IntermediateParser.inc', 'pear-config', '@PEAR-DIR@', 'php_dir');
$pfm2->addReplacement('user/pear-makedocs.ini', 'pear-config', '@PEAR-DIR@', 'php_dir');
$pfm2->addReplacement('user/pear-makedocs.ini', 'pear-config', '@DOC-DIR@', 'doc_dir');
$pfm2->addReplacement('user/pear-makedocs.ini', 'package-info', '@VER@', 'version');
$pfm2->addRole('inc', 'php');
$pfm2->addRole('sh', 'script');
$pfm2->addUnixEol('pear-phpdoc');
$pfm2->addUnixEol('phpdoc');
$pfm2->addWindowsEol('pear-phpdoc.bat');
$pfm2->addWindowsEol('phpdoc.bat');
$pfm2->generateContents();
$pfm2->setPackageType('php');
$pfm2->addRelease();
$pfm2->setOsInstallCondition('windows');
// these next two files are only used if the archive is extracted as-is
// without installing via "pear install blah"
$pfm2->addIgnoreToRelease("phpdoc");
$pfm2->addIgnoreToRelease('phpdoc.bat');
$pfm2->addIgnoreToRelease('user/makedocs.ini');
$pfm2->addIgnoreToRelease('scripts/makedoc.sh');
$pfm2->addInstallAs('pear-phpdoc', 'phpdoc');
$pfm2->addInstallAs('pear-phpdoc.bat', 'phpdoc.bat');
$pfm2->addInstallAs('user/pear-makedocs.ini', 'user/makedocs.ini');
$pfm2->addRelease();
// these next two files are only used if the archive is extracted as-is
// without installing via "pear install blah"
$pfm2->addIgnoreToRelease("phpdoc");
$pfm2->addIgnoreToRelease('phpdoc.bat');
$pfm2->addIgnoreToRelease('user/makedocs.ini');
$pfm2->addIgnoreToRelease('pear-phpdoc.bat');
$pfm2->addInstallAs('pear-phpdoc', 'phpdoc');
$pfm2->addInstallAs('user/pear-makedocs.ini', 'user/makedocs.ini');
if (isset($_GET['make']) || (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'make')) {
    $pfm2->writePackageFile();
} else {
    $pfm2->debugPackageFile();
}
if (!isset($_GET['make']) && !(isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'make')) {
    echo '<a href="' . $_SERVER['PHP_SELF'] . '?make=1">Make this file</a>';
}
?>

<?php

/*
//  the following packages are presumed to be present in the base install:

PACKAGE        VERSION STATE
Archive_Tar    1.1     stable
Console_Getopt 1.2     stable
DB             1.6.1   stable
Mail           1.1.2   stable
Net_SMTP       1.2.5   stable
Net_Socket     1.0.1   stable
PEAR           1.3.1   stable
PHPUnit        0.6.2   stable
XML_Parser     1.0.1   stable
XML_RPC        1.1.0   stable
*/

set_time_limit(0);
require_once 'PEAR/PackageFileManager.php';

// Modify short description. Try to keep under 80 chars width
$shortDesc = <<<EOD
Unit testing, mock objects and web testing framework for PHP.
EOD;

// Modify long description. Try to keep under 80 chars width
$longDesc = <<<EOD
The heart of SimpleTest is a testing framework built around test case classes.
These are written as extensions of base test case classes, each extended with
methods that actually contain test code. Top level test scripts then invoke
the run()  methods on every one of these test cases in order. Each test
method is written to invoke various assertions that the developer expects to
be true such as assertEqual(). If the expectation is correct, then a
successful result is dispatched to the observing test reporter, but any
failure triggers an alert and a description of the mismatch.

These tools are designed for the developer. Tests are written in the PHP
language itself more or less as the application itself is built. The advantage
of using PHP itself as the testing language is that there are no new languages
to learn, testing can start straight away, and the developer can test any part
of the code. Basically, all parts that can be accessed by the application code
can also be accessed by the test code if they are in the same language. 
EOD;

$packagexml = new PEAR_PackageFileManager;
$e = $packagexml->setOptions(array(
    'baseinstalldir' => 'simpletest',
    'version' => '1.0.0',
    'license' => 'The Open Group Test Suite License',
    'packagedirectory' => '/var/www/html/tmp/simpletest',
    'state' => 'stable',
    'package' => 'simpletest',
    'simpleoutput' => true,
    'summary' => $shortDesc,
    'description' => $longDesc,
    'filelistgenerator' => 'file', // generate from cvs, use file for directory
    'notes' => 'See the CHANGELOG for full list of changes',
    'dir_roles' => array(
        'extensions' => 'php',
        'test' => 'test',
        ),
    'ignore' => array(
        'packages/',  
        'tutorials/',
        'ui/',
        'docs/',        
        '*CVS*',
        'TODO',        
        ), 
    'roles' => array(
        'php' => 'php',
        'html' => 'php',
        '*' => 'php',
         ),
    'exceptions' => array(
        'VERSION' => 'doc',
        'HELP_MY_TESTS_DONT_WORK_ANYMORE' => 'doc',
        'LICENSE' => 'doc',
        'README' => 'doc',
        ),
    )
);
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    die();
}

$e = $packagexml->addMaintainer('lastcraft', 'lead', 'Marcus Baker', 'marcus@lastcraft.com');
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addMaintainer('tswicegood', 'developer', 'Travis Swicegood', 'tswicegood@users.sourceforge.net');
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addMaintainer('jsweat', 'helper', 'Jason Sweat', 'jsweat_php@yahoo.com');
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addMaintainer('pp11', 'helper', 'Perrick Penet', 'perrick@noparking.net');
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addMaintainer('shpikat', 'helper', 'Constantine Shpikat', 'shpikat@users.sourceforge.net');
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

$e = $packagexml->addMaintainer('demianturner', 'helper', 'Demian Turner', 'demian@phpkitchen.com');
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    exit;
}

// note use of {@link debugPackageFile()} - this is VERY important
if (isset($_GET['make']) || (isset($_SERVER['argv'][2]) &&
      $_SERVER['argv'][2] == 'make')) {
    $e = $packagexml->writePackageFile();
} else {
    $e = $packagexml->debugPackageFile();
}
if (is_a($e, 'PEAR_Error')) {
    echo $e->getMessage();
    die();
}
?>
Installing the Library
======================

Installing Swift Mailer is trivial. Usually it's just a case of uploading the
extracted source files to your web server.

Installing with Composer
------------------------

If you use Composer to manage your project dependencies, you can install
Swiftmailer like this:

.. code-block:: bash

    $ php composer.phar require swiftmailer/swiftmailer @stable

Installing from PEAR
--------------------

If you want to install Swift Mailer globally on your machine, the easiest
installation method is using the PEAR channel.

To install the Swift Mailer PEAR package:

* Run the command ``pear channel-discover pear.swiftmailer.org``.

* Then, run the command ``pear install swift/swift``.

Installing from a Package
-------------------------

Most users will download a package from the Swift Mailer website and install
Swift Mailer using this.

If you downloaded Swift Mailer as a ``.tar.gz`` or
``.zip`` file installation is as simple as extracting the archive
and uploading it to your web server.

Extracting the Library
~~~~~~~~~~~~~~~~~~~~~~

You extract the archive by using your favorite unarchiving tool such as
``tar`` or 7-Zip.

You will need to have access to a program that can open uncompress the
archive. On Windows computers, 7-Zip will work. On Mac and Linux systems you
can use ``tar`` on the command line.

To extract your downloaded package:

* Use the "extract" facility of your archiving software.

The source code will be placed into a directory with the same name as the
archive (e.g. Swift-4.0.0-b1).

The following example shows the process on Mac OS X and Linux systems using
the ``tar`` command.

.. code-block:: bash

    $ ls
    Swift-4.0.0-dev.tar.gz
    $ tar xvzf Swift-4.0.0-dev.tar.gz 
    Swift-4.0.0-dev/
    Swift-4.0.0-dev/lib/
    Swift-4.0.0-dev/lib/classes/
    Swift-4.0.0-dev/lib/classes/Swift/
    Swift-4.0.0-dev/lib/classes/Swift/ByteStream/
    Swift-4.0.0-dev/lib/classes/Swift/CharacterReader/
    Swift-4.0.0-dev/lib/classes/Swift/CharacterReaderFactory/
    Swift-4.0.0-dev/lib/classes/Swift/CharacterStream/
    Swift-4.0.0-dev/lib/classes/Swift/Encoder/

      ... etc etc ...

    Swift-4.0.0-dev/tests/unit/Swift/Transport/LoadBalancedTransportTest.php
    Swift-4.0.0-dev/tests/unit/Swift/Transport/SendmailTransportTest.php
    Swift-4.0.0-dev/tests/unit/Swift/Transport/StreamBufferTest.php
    $ cd Swift-4.0.0-dev
    $ ls
    CHANGES LICENSE ...
    $

Installing from Git
-------------------

It's possible to download and install Swift Mailer directly from github.com if
you want to keep up-to-date with ease.

Swift Mailer's source code is kept in a git repository at github.com so you
can get the source directly from the repository.

.. note::

    You do not need to have git installed to use Swift Mailer from github. If
    you don't have git installed, go to `github`_ and click the "Download"
    button.

Cloning the Repository
~~~~~~~~~~~~~~~~~~~~~~

The repository can be cloned from git://github.com/swiftmailer/swiftmailer.git
using the ``git clone`` command.

You will need to have ``git`` installed before you can use the
``git clone`` command.

To clone the repository:

* Open your favorite terminal environment (command line).

* Move to the directory you want to clone to.

* Run the command ``git clone git://github.com/swiftmailer/swiftmailer.git
  swiftmailer``.

The source code will be downloaded into a directory called "swiftmailer".

The example shows the process on a UNIX-like system such as Linux, BSD or Mac
OS X.

.. code-block:: bash

    $ cd source_code/
    $ git clone git://github.com/swiftmailer/swiftmailer.git swiftmailer
    Initialized empty Git repository in /Users/chris/source_code/swiftmailer/.git/
    remote: Counting objects: 6815, done.
    remote: Compressing objects: 100% (2761/2761), done.
    remote: Total 6815 (delta 3641), reused 6326 (delta 3286)
    Receiving objects: 100% (6815/6815), 4.35 MiB | 162 KiB/s, done.
    Resolving deltas: 100% (3641/3641), done.
    Checking out files: 100% (1847/1847), done.
    $ cd swiftmailer/
    $ ls
    CHANGES LICENSE ...
    $

Uploading to your Host
----------------------

You only need to upload the "lib/" directory to your web host for production
use. All other files and directories are support files not needed in
production.

You will need FTP, ``rsync`` or similar software installed in order to upload 
the "lib/" directory to your web host.

To upload Swift Mailer:

* Open your FTP program, or a command line if you prefer rsync/scp.

* Upload the "lib/" directory to your hosting account.

The files needed to use Swift Mailer should now be accessible to PHP on your
host.

The following example shows show you can upload the files using
``rsync`` on Linux or OS X.

.. note::

    You do not need to place the files inside your web root. They only need to 
    be in a place where your PHP scripts can "include" them.

    .. code-block:: bash

        $ rsync -rvz lib d11wtq@swiftmailer.org:swiftmailer
        building file list ... done
        created directory swiftmailer
        lib/
        lib/mime_types.php
        lib/preferences.php
        lib/swift_required.php
        lib/classes/
        lib/classes/Swift/
        lib/classes/Swift/Attachment.php
        lib/classes/Swift/CharacterReader.php
          ... etc etc ...
        lib/dependency_maps/
        lib/dependency_maps/cache_deps.php
        lib/dependency_maps/mime_deps.php
        lib/dependency_maps/transport_deps.php

        sent 151692 bytes  received 2974 bytes  5836.45 bytes/sec
        total size is 401405  speedup is 2.60
        $

.. _`github`: http://github.com/swiftmailer/swiftmailer

Troubleshooting
---------------

Swift Mailer does not work when used with function overloading as implemented
by ``mbstring`` (``mbstring.func_overload`` set to ``2``). A workaround is to
temporarily change the internal encoding to ``ASCII`` when sending an email:

.. code-block:: php

    if (function_exists('mb_internal_encoding') && ((int) ini_get('mbstring.func_overload')) & 2)
    {
      $mbEncoding = mb_internal_encoding();
      mb_internal_encoding('ASCII');
    }

    // Create your message and send it with Swift Mailer

    if (isset($mbEncoding))
    {
      mb_internal_encoding($mbEncoding);
    }

Including Swift Mailer (Autoloading)
====================================

Swift Mailer uses an autoloader so the only file you need to include is the
``lib/swift_required.php`` file.

To use Swift Mailer's autoloader:

* Put Swift Mailer somewhere accessible to your PHP scripts (this does not
  need to be in the web root).

* Include, or require the ``lib/swift_required.php`` file.

* Follow the remainder of the documentation for using the available
  components.

.. note::

    While Swift Mailer's autoloader is designed to play nicely with other
    autoloaders, sometimes you may have a need to avoid using Swift Mailer's
    autoloader and use your own instead. Include the ``swift_init.php``
    instead of the ``swift_required.php`` if you need to do this. The very
    minimum include is the ``swift_init.php`` file since Swift Mailer will not
    work without the dependency injection this file sets up:

    .. code-block:: php

        require_once '/path/to/swift-mailer/lib/swift_required.php';

        /* rest of code goes here */

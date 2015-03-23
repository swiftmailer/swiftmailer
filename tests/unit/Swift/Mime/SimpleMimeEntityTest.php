<?php

use Egulias\EmailValidator\EmailValidator;

class Swift_Mime_SimpleMimeEntityTest extends Swift_Mime_AbstractMimeEntityTest
{
    // -- Private helpers

    protected function createEntity($headerFactory, $encoder, $cache)
    {
        return new Swift_Mime_SimpleMimeEntity($headerFactory, $encoder, $cache, new EmailValidator());
    }
}

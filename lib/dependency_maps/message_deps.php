<?php

use Swift\Message;
use Swift\MimePart;

\Swift\DependencyContainer::getInstance()
    ->register('message.message')
    ->asNewInstanceOf(Message::class)

    ->register('message.mimepart')
    ->asNewInstanceOf(MimePart::class)
;

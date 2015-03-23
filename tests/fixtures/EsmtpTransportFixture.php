<?php

class EsmtpTransportFixture extends Swift_Transport_EsmtpTransport
{
    /** This is so Mockery doesn't throw a fit. */
    private function sortHandlers($a, $b)
    {
        return 1;
    }
}

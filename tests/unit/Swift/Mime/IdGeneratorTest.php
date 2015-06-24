<?php

use Egulias\EmailValidator\EmailValidator;

class Swift_Mime_IdGeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $emailValidator;
    protected $originalServerName;

    public function setUp()
    {
        $this->emailValidator = new EmailValidator();
        $this->originalServerName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
        unset($_SERVER['SERVER_NAME']);
    }

    public function tearDown()
    {
        // Restore super-global variable.
        if (isset($this->originalServerName)) {
            $_SERVER['SERVER_NAME'] = $this->originalServerName;
        } else {
            unset($_SERVER['SERVER_NAME']);
        }
    }

    public function testIdGeneratorServerName()
    {
        $_SERVER['SERVER_NAME'] = 'example.com';
        $idGenerator = new Swift_Mime_IdGenerator($this->emailValidator);
        $this->assertEquals('example.com', $idGenerator->getIdRight());
    }

    public function testIdGeneratorInvalidServerName()
    {
        $_SERVER['SERVER_NAME'] = 'not a valid hostname';
        $idGenerator = new Swift_Mime_IdGenerator($this->emailValidator);
        $this->assertEquals('swift.generated', $idGenerator->getIdRight());
    }

    public function testIdGeneratorFallback()
    {
        $idGenerator = new Swift_Mime_IdGenerator($this->emailValidator);
        $this->assertEquals('swift.generated', $idGenerator->getIdRight());
    }

    public function testIdGeneratorExplicit()
    {
        $idGenerator = new Swift_Mime_IdGenerator($this->emailValidator, 'example.net');
        $this->assertEquals('example.net', $idGenerator->getIdRight());
    }

    public function testIdGeneratorSetRightId()
    {
        $idGenerator = new Swift_Mime_IdGenerator($this->emailValidator, 'example.net');
        $this->assertEquals('example.net', $idGenerator->getIdRight());

        $idGenerator->setIdRight('example.com');
        $this->assertEquals('example.com', $idGenerator->getIdRight());
    }

    public function testIdGenerateId()
    {
        $idGenerator = new Swift_Mime_IdGenerator($this->emailValidator, 'example.net');

        $id = $idGenerator->generateId();
        $this->assertTrue($this->emailValidator->isValid($id));
        $this->assertEquals(1, preg_match('/^.{32}@example.net$/', $id));

        $anotherId = $idGenerator->generateId();
        $this->assertNotEquals($id, $anotherId);
    }
}

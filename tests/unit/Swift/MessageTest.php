<?php

require_once 'Swift/Mime/MimeEntity.php';
require_once 'Swift/Mime/SimpleMessage.php';
require_once 'Swift/Mime/SimpleMessageTest.php';
require_once 'Swift/Mime/Header.php';
require_once 'Swift/Mime/ParameterizedHeader.php';
require_once 'Swift/Mime/Grammar.php';

class Swift_MessageTest extends Swift_Mime_SimpleMessageTest
{
    public function testCloning()
    {
        $message1 = new \Swift_Message('subj', 'body', 'ctype');
        $message2 = new \Swift_Message('subj', 'body', 'ctype');
        $message1_clone = clone $message1;

        $this->_recursiveObjectCloningCheck($message1, $message2, $message1_clone);
    }

    // -- Private helpers
    protected function _recursiveObjectCloningCheck($obj1, $obj2, $obj1_clone)
    {
        $obj1_properties = (array)$obj1;
        $obj2_properties = (array)$obj2;
        $obj1_clone_properties = (array)$obj1_clone;

        foreach ($obj1_properties as $property => $value) {

            if (is_object($value)) {
                $obj1_value = $obj1_properties[$property];
                $obj2_value = $obj2_properties[$property];
                $obj1_clone_value = $obj1_clone_properties[$property];

                if ($obj1_value !== $obj2_value) {
                    // two separetely instanciated objects property not referencing same object
                    $this->assertFalse(
                        // but object's clone does - not everything copied
                        $obj1_value === $obj1_clone_value,
                        "Property `$property` cloning error: source and cloned objects property is referencing same object"
                    );
                }
                else {
                    // two separetely instanciated objects have same reference
                    $this->assertFalse(
                        // but object's clone doesn't - overdone making copies
                        $obj1_value !== $obj1_clone_value,
                        "Property `$property` not properly cloned: it should reference same object as cloning source (overdone copping)"
                    );
                }
                // recurse
                $this->_recursiveObjectCloningCheck($obj1_value, $obj2_value, $obj1_clone_value);
            }
        }
    }

}

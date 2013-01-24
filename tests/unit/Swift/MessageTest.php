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
        $message1 = new Swift_Message('subj', 'body', 'ctype');
        $message2 = new Swift_Message('subj', 'body', 'ctype');
        $message1_clone = clone $message1;

        $this->_recursiveObjectCloningCheck($message1, $message2, $message1_clone);
    }

    public function testBodySwap()
    {
        $message1 = new Swift_Message('Test');
        $html = Swift_MimePart::newInstance('<html></html>', 'text/html');
        $html->getHeaders()->addTextHeader('X-Test-Remove', 'Test-Value');
        $html->getHeaders()->addTextHeader('X-Test-Alter', 'Test-Value');
        $message1->attach($html);
        $source = $message1->toString();
        $message2 = clone $message1;
        $message2->setSubject('Message2');
        foreach ($message2->getChildren() as $child) {
            $child->setBody('Test');
            $child->getHeaders()->removeAll('X-Test-Remove');
            $child->getHeaders()->get('X-Test-Alter')->setValue('Altered');
        }
        $final = $message1->toString();
        if ($source != $final) {
            $this->fail("Difference altough object cloned \n [".$source."]\n[".$final."]\n");
        }
        $final = $message2->toString();
        if ($final == $source) {
            $this->fail('Two body matches altough they should differ'."\n [".$source."]\n[".$final."]\n");
        }
        $id_1 = $message1->getId();
        $id_2 = $message2->getId();
        $this->assertNotIdentical($id_1, $id_2, 'Message Ids are the same');
    }

    // -- Private helpers
    protected $_stack = array();

    protected function _recursiveObjectCloningCheck($obj1, $obj2, $obj1_clone)
    {
        $obj1_properties = (array)$obj1;
        $obj2_properties = (array)$obj2;
        $obj1_clone_properties = (array)$obj1_clone;

        foreach ($obj1_properties as $property => $value)
        {
            // collect and format information from where the property is
            $property_parts = explode("\x0", $property);
            $property_name = array_pop($property_parts);
            $property_origin = array_pop($property_parts);
            $this->_stack[] = array('property' => $property_name, 'origin' => $property_origin, 'parent_type' => gettype($obj1));

            $stack = '';
            foreach ($this->_stack as $depth => $entry) {
                $string = ($entry['parent_type'] == 'object') ? "->{$entry['property']}" : "[{$entry['property']}]";
                $string .= ($entry['origin'] && $entry['origin'] != '*') ? " (from: {$entry['origin']})" : '';
                $stack .= $string;
            }

            $obj1_value = $obj1_properties[$property];
            $obj2_value = $obj2_properties[$property];
            $obj1_clone_value = $obj1_clone_properties[$property];

            if (is_object($value)) {

                if ($obj1_value !== $obj2_value) {
                    // two separately instantiated objects property not referencing same object
                    $this->assertFalse(
                        // but object's clone does - not everything copied
                        $obj1_value === $obj1_clone_value,
                        "Property \n$stack: Cloning error: source and cloned objects property is referencing same object"
                    );
                }
                else {
                    // two separately instantiated objects have same reference
                    $this->assertFalse(
                        // but object's clone doesn't - overdone making copies
                        $obj1_value !== $obj1_clone_value,
                        "Property \n$stack: Not properly cloned: it should reference same object as cloning source (overdone copying)"
                    );
                }
                // recurse
                $this->_recursiveObjectCloningCheck($obj1_value, $obj2_value, $obj1_clone_value);
            }
            elseif (is_array($value)) {
                // look for objects in arrays
                $this->_recursiveObjectCloningCheck($obj1_value, $obj2_value, $obj1_clone_value);
            }

            array_pop($this->_stack);
        }
    }

}

<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Mailer/ArrayRecipientIterator.php';

class Swift_Mailer_ArrayRecipientIteratorTest
  extends Swift_Tests_SwiftUnitTestCase
{

  public function testHasNextReturnsFalseForEmptyArray()
  {
    $it = new Swift_Mailer_ArrayRecipientIterator(array());
    $this->assertFalse($it->hasNext());
  }
  
  public function testHasNextReturnsTrueIfItemsLeft()
  {
    $it = new Swift_Mailer_ArrayRecipientIterator(array('foo@bar' => 'Foo'));
    $this->assertTrue($it->hasNext());
  }
  
  public function testReadingToEndOfListCausesHasNextToReturnFalse()
  {
    $it = new Swift_Mailer_ArrayRecipientIterator(array('foo@bar' => 'Foo'));
    $this->assertTrue($it->hasNext());
    $it->nextRecipient();
    $this->assertFalse($it->hasNext());
  }
  
  public function testReturnedValueHasPreservedKeyValuePair()
  {
    $it = new Swift_Mailer_ArrayRecipientIterator(array('foo@bar' => 'Foo'));
    $this->assertEqual(array('foo@bar' => 'Foo'), $it->nextRecipient());
  }
  
  public function testIteratorMovesNextAfterEachIteration()
  {
    $it = new Swift_Mailer_ArrayRecipientIterator(array(
      'foo@bar' => 'Foo',
      'zip@button' => 'Zip thing',
      'test@test' => null
      ));
    $this->assertEqual(array('foo@bar' => 'Foo'), $it->nextRecipient());
    $this->assertEqual(array('zip@button' => 'Zip thing'), $it->nextRecipient());
    $this->assertEqual(array('test@test' => null), $it->nextRecipient());
  }
  
}

<?php

require_once 'Swift/Tests/SwiftUnitTestCase.php';
require_once 'Swift/Plugins/DecoratorPlugin.php';
require_once 'Swift/Events/SendEvent.php';
require_once 'Swift/Mime/Message.php';
require_once 'Swift/Mime/MimeEntity.php';

Mock::generate('Swift_Mime_Message', 'Swift_Mime_MockMessage');
Mock::generate('Swift_Mime_MimeEntity', 'Swift_Mime_MockMimeEntity');
Mock::generate('Swift_Events_SendEvent', 'Swift_Events_MockSendEvent');

class Swift_Plugins_DecoratorPluginTest extends Swift_Tests_SwiftUnitTestCase
{

  public function testMessageBodyHasReplacements()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getTo', array('zip@button.tld' => 'Zipathon'));
    $message->setReturnValue('getFrom', array('chris.corbyn@swiftmailer.org' => 'Chris'));
    $message->setReturnValue('getBodyAsString', 'Hello {name}, you are customer #{id}');
    $message->expectAt(0, 'setBodyAsString', array('Hello Zip, you are customer #456'));
    $message->expectMinimumCallCount('setBodyAsString', 1);
    
    $plugin = new Swift_Plugins_DecoratorPlugin(
      array(
        'foo@bar.tld' => array('{name}' => 'Foo', '{id}' => '123'),
        'zip@button.tld' => array('{name}' => 'Zip', '{id}' => '456')
        )
      );
      
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getMessage', $message);
    
    $plugin->beforeSendPerformed($evt);
    $plugin->sendPerformed($evt);
  }
  
  public function testReplacementsCanBeAppliedToSameMessageMultipleTimes()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValueAt(0, 'getTo', array('zip@button.tld' => 'Zipathon'));
    $message->setReturnValue('getFrom', array('chris.corbyn@swiftmailer.org' => 'Chris'));
    $message->setReturnValue('getBodyAsString', 'Hello {name}, you are customer #{id}');
    $message->expectAt(0, 'setBodyAsString', array('Hello Zip, you are customer #456'));
    $message->setReturnValueAt(1, 'getTo', array('foo@bar.tld' => 'Foo'));
    $message->expectAt(1, 'setBodyAsString', array('Hello {name}, you are customer #{id}'));
    $message->expectAt(2, 'setBodyAsString', array('Hello Foo, you are customer #123'));
    $message->expectMinimumCallCount('setBodyAsString', 3);
    
    $plugin = new Swift_Plugins_DecoratorPlugin(
      array(
        'foo@bar.tld' => array('{name}' => 'Foo', '{id}' => '123'),
        'zip@button.tld' => array('{name}' => 'Zip', '{id}' => '456')
        )
      );
      
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getMessage', $message);
    
    $plugin->beforeSendPerformed($evt);
    $plugin->sendPerformed($evt);
    $plugin->beforeSendPerformed($evt);
    $plugin->sendPerformed($evt);
  }
  
  public function testReplacementsCanBeMadeInSubject()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getSubject', 'A message for {name}!');
    $message->setReturnValue('getTo', array('zip@button.tld' => 'Zipathon'));
    $message->setReturnValue('getFrom', array('chris.corbyn@swiftmailer.org' => 'Chris'));
    $message->setReturnValue('getBodyAsString', 'Hello {name}, you are customer #{id}');
    $message->expectAt(0, 'setBodyAsString', array('Hello Zip, you are customer #456'));
    $message->expectAt(0, 'setSubject', array('A message for Zip!'));
    $message->expectMinimumCallCount('setBodyAsString', 1);
    $message->expectMinimumCallCount('setSubject', 1);
    
    $plugin = new Swift_Plugins_DecoratorPlugin(
      array(
        'foo@bar.tld' => array('{name}' => 'Foo', '{id}' => '123'),
        'zip@button.tld' => array('{name}' => 'Zip', '{id}' => '456')
        )
      );
      
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getMessage', $message);
    
    $plugin->beforeSendPerformed($evt);
    $plugin->sendPerformed($evt);
  }
  
  public function testReplacementsAreMadeOnSubparts()
  {
    $message = new Swift_Mime_MockMessage();
    $message->setReturnValue('getSubject', 'A message for {name}!');
    $message->setReturnValue('getTo', array('zip@button.tld' => 'Zipathon'));
    $message->setReturnValue('getFrom', array('chris.corbyn@swiftmailer.org' => 'Chris'));
    $message->setReturnValue('getBodyAsString', 'Hello {name}, you are customer #{id}');
    $message->expectAt(0, 'setBodyAsString', array('Hello Zip, you are customer #456'));
    $message->expectAt(0, 'setSubject', array('A message for Zip!'));
    
    $part1 = new Swift_Mime_MockMimeEntity();
    $part1->setReturnValue('getContentType', 'text/plain');
    $part1->setReturnValue('getBodyAsString', 'Your name is {name}?');
    $part1->setReturnValue('getId', 'foo123@bar');
    $part1->expectAt(0, 'setBodyAsString', array('Your name is Zip?'));
    $part1->expectMinimumCallCount('setBodyAsString', 1);
    
    $part2 = new Swift_Mime_MockMimeEntity();
    $part2->setReturnValue('getContentType', 'text/html');
    $part2->setReturnValue('getBodyAsString', 'Your <em>name</em> is {name}?');
    $part2->setReturnValue('getId', 'foo123@bar');
    $part2->expectAt(0, 'setBodyAsString', array('Your <em>name</em> is Zip?'));
    $part2->expectMinimumCallCount('setBodyAsString', 1);
    
    $message->setReturnValue('getChildren', array($part1, $part2));
    
    $message->expectMinimumCallCount('setBodyAsString', 1);
    $message->expectMinimumCallCount('setSubject', 1);
    
    $plugin = new Swift_Plugins_DecoratorPlugin(
      array(
        'foo@bar.tld' => array('{name}' => 'Foo', '{id}' => '123'),
        'zip@button.tld' => array('{name}' => 'Zip', '{id}' => '456')
        )
      );
      
    $evt = new Swift_Events_MockSendEvent();
    $evt->setReturnValue('getMessage', $message);
    
    $plugin->beforeSendPerformed($evt);
    $plugin->sendPerformed($evt);
  }
  
}

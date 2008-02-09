<?php

require_once 'Swift/AbstractSwiftUnitTestCase.php';
require_once 'Swift/Mime/Header/AddressHeader.php';
require_once 'Swift/Mime/HeaderEncoder.php';

Mock::generate('Swift_Mime_HeaderEncoder', 'Swift_Mime_MockHeaderEncoder');

class Swift_Mime_Header_AddressHeaderTest
  extends Swift_AbstractSwiftUnitTestCase
{
  
  private $_charset = 'utf-8';
  
  public function testEmptyGroupCanBeDefined()
  {
    /* -- RFC 2822, 3.4.
     address         =       mailbox / group
     
     .....
     
     group           =       display-name ":" [mailbox-list / CFWS] ";"
                        [CFWS]
     */
    
    $header = $this->_getHeader('To', new Swift_Mime_MockHeaderEncoder());
    $header->defineGroup('undisclosed-recipients');
    $this->assertEqual(array(), $header->getGroup('undisclosed-recipients'));
    $this->assertEqual('undisclosed-recipients:;', $header->getFieldBody());
  }
  
  public function testAddressGroupCanBeDefined()
  {
    $header = $this->_getHeader('Cc', new Swift_Mime_MockHeaderEncoder());
    $header->defineGroup('Admin Dept',
      array('tim@swiftmailer.org' => 'Tim Fletcher',
      'andrew@swiftmailer.org' => 'Andrew Rose')
      );
    $this->assertEqual(array('tim@swiftmailer.org' => 'Tim Fletcher',
      'andrew@swiftmailer.org' => 'Andrew Rose'),
      $header->getGroup('Admin Dept')
      );
    $this->assertEqual(
      'Admin Dept:Tim Fletcher <tim@swiftmailer.org>, ' .
      'Andrew Rose <andrew@swiftmailer.org>;',
      $header->getFieldBody()
      );
  }
  
  public function testValueIncludesGroupAsItemInAddressList()
  {
    $header = $this->_getHeader('Cc', new Swift_Mime_MockHeaderEncoder());
    $header->setAddresses(array('chris@swiftmailer.org'));
    $header->defineGroup('Admin Dept',
      array('tim@swiftmailer.org' => 'Tim Fletcher',
      'andrew@swiftmailer.org' => 'Andrew Rose')
      );
    $this->assertEqual(
      'Admin Dept:Tim Fletcher <tim@swiftmailer.org>, ' .
      'Andrew Rose <andrew@swiftmailer.org>;, chris@swiftmailer.org',
      $header->getFieldBody()
      );
  }
  
  public function testAddressesFromGroupAreMerged()
  {
    $header = $this->_getHeader('Cc', new Swift_Mime_MockHeaderEncoder());
    $header->setAddresses(array('chris@swiftmailer.org'));
    $header->defineGroup('Admin Dept',
      array('tim@swiftmailer.org' => 'Tim Fletcher',
      'andrew@swiftmailer.org' => 'Andrew Rose')
      );
    $this->assertEqual(
      array('chris@swiftmailer.org', 'tim@swiftmailer.org', 'andrew@swiftmailer.org'),
      $header->getAddresses()
      );
  }
  
  public function testNameAddressesFromGroupAreMerged()
  {
    $header = $this->_getHeader('Cc', new Swift_Mime_MockHeaderEncoder());
    $header->setAddresses(array('chris@swiftmailer.org'));
    $header->defineGroup('Admin Dept',
      array('tim@swiftmailer.org' => 'Tim Fletcher',
      'andrew@swiftmailer.org' => 'Andrew Rose')
      );
    $this->assertEqual(
      array(
        'chris@swiftmailer.org' => null,
        'tim@swiftmailer.org' => 'Tim Fletcher',
        'andrew@swiftmailer.org' => 'Andrew Rose'
        ),
      $header->getNameAddresses()
      );
  }
  
  public function testNameAddressStringsFromGroupAreMerged()
  {
    $header = $this->_getHeader('Cc', new Swift_Mime_MockHeaderEncoder());
    $header->setAddresses(array('chris@swiftmailer.org'));
    $header->defineGroup('Admin Dept',
      array('tim@swiftmailer.org' => 'Tim Fletcher',
      'andrew@swiftmailer.org' => 'Andrew Rose')
      );
    $this->assertEqual(
      array(
        'chris@swiftmailer.org',
        'Tim Fletcher <tim@swiftmailer.org>',
        'Andrew Rose <andrew@swiftmailer.org>'
        ),
      $header->getNameAddressStrings()
      );
  }
  
  public function testAddressesInGroupsDoNotAppearElsewhere()
  {
    $header = $this->_getHeader('To', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array('chris@swiftmailer.org' => 'Chris Corbyn',
        'fred@swiftmailer.org' => 'Fred'));
    $header->defineGroup('Admin Dept',
      array('tim@swiftmailer.org' => 'Tim Fletcher',
      'andrew@swiftmailer.org' => 'Andrew Rose',
      'chris@swiftmailer.org')
      );
    $this->assertEqual(
      'Admin Dept:Tim Fletcher <tim@swiftmailer.org>, ' .
      'Andrew Rose <andrew@swiftmailer.org>, ' .
      'chris@swiftmailer.org;, ' .
      'Fred <fred@swiftmailer.org>',
      $header->getFieldBody()
      );
  }
  
  public function testRemoveAddressesRemovesFromGroups()
  {
    $header = $this->_getHeader('To', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array('fred@swiftmailer.org' => 'Fred'));
    $header->defineGroup('Admin Dept',
      array('tim@swiftmailer.org' => 'Tim Fletcher',
      'andrew@swiftmailer.org' => 'Andrew Rose',
      'chris@swiftmailer.org')
      );
    $header->removeAddresses(
      array('tim@swiftmailer.org', 'fred@swiftmailer.org')
      );
    $this->assertEqual(array('andrew@swiftmailer.org', 'chris@swiftmailer.org'),
      $header->getAddresses()
      );
  }
  
  public function testAddressesCanBeHiddenFromDisplay()
  {
    $header = $this->_getHeader('To', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
        'chris@swiftmailer.org' => 'Chris Corbyn',
        'fred@swiftmailer.org' => 'Fred'
        ));
    $header->setHiddenAddresses('fred@swiftmailer.org');
    $this->assertEqual('Chris Corbyn <chris@swiftmailer.org>',
      $header->getFieldBody()
      );
  }
  
  public function testHiddenAddressesAreStillReturnedByGetAddresses()
  {
    $header = $this->_getHeader('To', new Swift_Mime_MockHeaderEncoder());
    $header->setNameAddresses(array(
        'chris@swiftmailer.org' => 'Chris Corbyn',
        'fred@swiftmailer.org' => 'Fred'
        ));
    $header->setHiddenAddresses('fred@swiftmailer.org');
    $this->assertEqual(
      array('chris@swiftmailer.org', 'fred@swiftmailer.org'),
      $header->getAddresses()
      );
  }
  
  public function testHiddenAddressesInGroups()
  {
    $header = $this->_getHeader('To', new Swift_Mime_MockHeaderEncoder());
    $header->defineGroup('undisclosed-recipients',
      array('tim@swiftmailer.org' => 'Tim Fletcher',
      'andrew@swiftmailer.org' => 'Andrew Rose',
      'chris@swiftmailer.org')
      );
    $header->setHiddenAddresses(
      array('tim@swiftmailer.org', 'andrew@swiftmailer.org', 'chris@swiftmailer.org')
      );
    $this->assertEqual(
      array('tim@swiftmailer.org', 'andrew@swiftmailer.org', 'chris@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual('undisclosed-recipients:;', $header->getFieldBody());
  }
  
  public function testDefininingEntireGroupAsHidden()
  {
    /* -- RFC 2822, 3.4.
     Because the list of mailboxes can be empty, using the group construct
     is also a simple way to communicate to recipients that the message
     was sent to one or more named sets of recipients, without actually
     providing the individual mailbox address for each of those
     recipients.
     */
    
    $header = $this->_getHeader('To', new Swift_Mime_MockHeaderEncoder());
    $header->defineGroup('undisclosed-recipients',
      array('tim@swiftmailer.org' => 'Tim Fletcher',
      'andrew@swiftmailer.org' => 'Andrew Rose',
      'chris@swiftmailer.org'),
      true
      );
    $this->assertEqual(
      array('tim@swiftmailer.org', 'andrew@swiftmailer.org', 'chris@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual('undisclosed-recipients:;', $header->getFieldBody());
  }
  
  public function testRemoveGroup()
  {
    $header = $this->_getHeader('To', new Swift_Mime_MockHeaderEncoder());
    $header->setAddresses('fred@swiftmailer.org');
    $header->defineGroup('undisclosed-recipients',
      array('tim@swiftmailer.org' => 'Tim Fletcher',
      'andrew@swiftmailer.org' => 'Andrew Rose',
      'chris@swiftmailer.org')
      );
    $header->removeGroup('undisclosed-recipients');
    $this->assertEqual(
      array('fred@swiftmailer.org'),
      $header->getAddresses()
      );
    $this->assertEqual('fred@swiftmailer.org', $header->getFieldBody());
  }
  
  public function testGroupNamedIsFormattedIfNeeded()
  {
    $header = $this->_getHeader('To', new Swift_Mime_MockHeaderEncoder());
    $header->defineGroup('Users, Admins', array('xyz@abc.com'));
    $this->assertEqual('"Users\\, Admins":xyz@abc.com;', $header->getFieldBody());
  }
  
  public function testGroupNameIsEncodedIfNeeded()
  {
    $encoder = new Swift_Mime_MockHeaderEncoder();
    $encoder->setReturnValue('getName', 'Q');
    $encoder->expectOnce('encodeString',
      array('R' . pack('C', 0x8F) . 'bots', '*', '*')
      );
    $encoder->setReturnValue('encodeString', 'R=8Fbots');
    
    $header = $this->_getHeader('To', $encoder);
    $header->defineGroup('R' . pack('C', 0x8F) . 'bots', array('xyz@abc.com'));
    $this->assertEqual('=?utf-8?Q?R=8Fbots?=:xyz@abc.com;', $header->getFieldBody());
  }
  
  //TODO: toString() isn't tested!
  
  // -- Private methods
  
  private function _getHeader($name, $encoder)
  {
    $header = new Swift_Mime_Header_AddressHeader($name, $encoder);
    $header->setCharset($this->_charset);
    return $header;
  }
  
}

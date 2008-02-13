<?php

class Swift_KeyCache_DiskKeyCacheAcceptanceTest extends UnitTestCase
{
  
  public function skip()
  {
    $this->skipUnless(
      SWIFT_TMP_DIR, '%s: SWIFT_TMP_DIR needs to be set in tests/config.php first'
      );
  }
  
  public function testNothing()
  {
    $this->assertFalse(true, 'Nothing');
  }
  
}

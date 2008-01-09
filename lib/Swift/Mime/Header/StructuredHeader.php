<?php

/*
 A Structured Mime Header in Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

require_once dirname(__FILE__) . '/UnstructuredHeader.php';
require_once dirname(__FILE__) . '/../HeaderEncoder.php';

/**
 * A Structured MIME Header.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_StructuredHeader
  extends Swift_Mime_Header_UnstructuredHeader
{
  
  /**
   * Tokens defined in RFC 2822 (and some related RFCs).
   * @var string[]
   * @access protected
   */
  protected $rfc2822Tokens = array();
  
  /**
   * Creates a new StructuredHeader with the given $name and $id.
   * @param string $name
   * @param mixed $id, optional as string or string[]
   * @param string $charset, optional
   * @param Swift_Mime_HeaderEncoder $encoder, optional
   */
  public function __construct($name, $value = null, $charset = null,
    Swift_Mime_HeaderEncoder $encoder = null)
  {
    parent::__construct($name, $value, $charset, $encoder);
    
    //Refer to RFC 2822 for ABNF
    $noWsCtl = '[\x01-\x08\x0B\x0C\x0E-\x19\x7F]';
    
    $text = '[\x00-\x08\x0B\x0C\x0E-\x7F]';
    
    $quotedPair = '(?:\\\\' . $text . ')';
    
    $atext = '[a-zA-Z0-9!#\$%&\'\*\+\-\/=\?\^_`\{\}\|~]';
    $dotAtomText = '(?:' . $atext . '+' . '(\.' . $atext . '+)*?)';
    
    $qtext = '(?:' . $noWsCtl . '|[\x21\x23-\x5B\x5D-\x7E])';
    $noFoldQuote = '(?:"(?:' . $qtext . '|' . $quotedPair . ')*?")';
    
    $dtext = '(?:' . $noWsCtl . '|[\x21-\x5A\x5E-\x7E])';
    $noFoldLiteral = '(?:\[(?:' . $dtext . '|' . $quotedPair . ')*?\])';
    
    $idLeft = '(?:' . $dotAtomText . '|' . $noFoldQuote . ')';
    $idRight = '(?:' . $dotAtomText . '|' . $noFoldLiteral . ')';
    
    $WSP = '[ \t]';
    $CRLF = '(?:\r\n)';
    
    $FWS = '(?:' . $WSP . '*' . $CRLF . ')?' . $WSP;
    
    $ctext = '(?:' . $noWsCtl . '|[\x21-\x27\x2A-\x5B\x5D-\x7E])';
    //TODO: Make this RFC2822 compliant (support comment nesting -- e.g. add |comment)
    $ccontent = '(?:' . $ctext . '|' . $quotedPair . ')';
    $comment = '(?:\((?:' . $FWS . '|' . $ccontent. ')*?' . $FWS . '?\))';
    
    $CFWS = '(?:(?:' . $FWS . '?' . $comment . ')*?(?:(?:' . $FWS . '?' . $comment . ')|' . $FWS . '))';
    
    $msgId = '(?:(?:' . $CFWS . ')?<' . $idLeft . '@' . $idRight . '>(?:' . $CFWS . ')?)';
    
    //Save ABNF converted to PCRE as property for shared reference
    $this->rfc2822Tokens['NO-WS-CTL'] = $noWsCtl;
    $this->rfc2822Tokens['text'] = $text;
    $this->rfc2822Tokens['quoted-pair'] = $quotedPair;
    $this->rfc2822Tokens['atext'] = $atext;
    $this->rfc2822Tokens['ctext'] = $ctext;
    $this->rfc2822Tokens['dtext'] = $dtext;
    $this->rfc2822Tokens['qtext'] = $qtext;
    $this->rfc2822Tokens['no-fold-quote'] = $noFoldQuote;
    $this->rfc2822Tokens['no-fold-literal'] = $noFoldLiteral;
    $this->rfc2822Tokens['id-left'] = $idLeft;
    $this->rfc2822Tokens['id-right'] = $idRight;
    $this->rfc2822Tokens['WSP'] = $WSP;
    $this->rfc2822Tokens['CRLF'] = $CRLF;
    $this->rfc2822Tokens['FWS'] = $FWS;
    $this->rfc2822Tokens['comment'] = $comment;
    $this->rfc2822Tokens['ccontent'] = $ccontent;
    $this->rfc2822Tokens['CFWS'] = $CFWS;
    $this->rfc2822Tokens['msg-id'] = $msgId;
  }
  
}

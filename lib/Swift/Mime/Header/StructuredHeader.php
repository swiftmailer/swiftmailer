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
    
    $WSP = '[ \t]';
    $CRLF = '(?:\r\n)';
    
    $FWS = '(?:(?:' . $WSP . '*' . $CRLF . ')?' . $WSP . ')';
    
    $text = '[\x00-\x08\x0B\x0C\x0E-\x7F]';
    
    $quotedPair = '(?:\\\\' . $text . ')';
    
    $ctext = '(?:' . $noWsCtl . '|[\x21-\x27\x2A-\x5B\x5D-\x7E])';
    
    //Uses recursive PCRE (?1) -- could be a weak point??
    $ccontent = '(?:' . $ctext . '|' . $quotedPair . '|(?1))';
    $comment = '(\((?:' . $FWS . '|' . $ccontent. ')*?' . $FWS . '?\))';
    
    $CFWS = '(?:(?:' . $FWS . '?' . $comment . ')*?(?:(?:' . $FWS . '?' . $comment . ')|' . $FWS . '))';
    
    $qtext = '(?:' . $noWsCtl . '|[\x21\x23-\x5B\x5D-\x7E])';
    $qcontent = '(?:' . $qtext . '|' . $quotedPair . ')';
    
    $quotedString = '(?:' . $CFWS . '?"' . '(' . $FWS . '?' . $qcontent . ')*?' . $FWS . '?"' . $CFWS . '?)';
    
    $atext = '[a-zA-Z0-9!#\$%&\'\*\+\-\/=\?\^_`\{\}\|~]';
    $atom = '(?:' . $CFWS . '?' . $atext . '+?' . $CFWS . '?)';
    $dotAtomText = '(?:' . $atext . '+' . '(\.' . $atext . '+)*?)';
    $dotAtom = '(?:' . $CFWS . '?' . $dotAtomText . '+?' . $CFWS . '?)';
    
    $word = '(?:' . $atom . '|' . $quotedString . ')';
    $phrase = '(?:' . $word . '+?)';
    
    $noFoldQuote = '(?:"(?:' . $qtext . '|' . $quotedPair . ')*?")';
    
    $dtext = '(?:' . $noWsCtl . '|[\x21-\x5A\x5E-\x7E])';
    $noFoldLiteral = '(?:\[(?:' . $dtext . '|' . $quotedPair . ')*?\])';
    
    $idLeft = '(?:' . $dotAtomText . '|' . $noFoldQuote . ')';
    $idRight = '(?:' . $dotAtomText . '|' . $noFoldLiteral . ')';
    
    $msgId = '(?:' . $CFWS . '?<' . $idLeft . '@' . $idRight . '>' . $CFWS . '?)';
    
    $displayName = $phrase;
    
    $localPart = '(?:' . $dotAtom . '|' . $quotedString . ')';
    
    $dcontent = '(?:' . $dtext . '|' . $quotedPair . ')';
    
    $domainLiteral = '(?:' . $CFWS . '?\[(' . $FWS . '?' . $dcontent . ')*?' . $FWS . '?\]' . $CFWS . '?)';
    
    $domain = '(?:' . $dotAtom . '|' . $domainLiteral . ')';
    
    $addrSpec = '(?:' . $localPart . '@' . $domain . ')';
    $angleAddr = '(?:' . $CFWS . '?<' . $addrSpec . '>' . $CFWS . '?)';
    
    $nameAddr = '(?:'. $displayName . '?' . $angleAddr . ')';
    
    $mailbox = '(?:' . $nameAddr . '|' . $addrSpec . ')';
    
    $mailboxList = '(?:' . $mailbox . '(?:,' . $mailbox . ')*?)';
    
    //Save ABNF converted to PCRE as property for shared reference
    $this->rfc2822Tokens['NO-WS-CTL'] = $noWsCtl;
    $this->rfc2822Tokens['WSP'] = $WSP;
    $this->rfc2822Tokens['CRLF'] = $CRLF;
    $this->rfc2822Tokens['FWS'] = $FWS;
    $this->rfc2822Tokens['text'] = $text;
    $this->rfc2822Tokens['quoted-pair'] = $quotedPair;
    $this->rfc2822Tokens['ctext'] = $ctext;
    $this->rfc2822Tokens['comment'] = $comment;
    $this->rfc2822Tokens['ccontent'] = $ccontent;
    $this->rfc2822Tokens['CFWS'] = $CFWS;
    $this->rfc2822Tokens['qtext'] = $qtext;
    $this->rfc2822Tokens['qcontent'] = $qcontent;
    $this->rfc2822Tokens['quoted-string'] = $quotedString;
    $this->rfc2822Tokens['atext'] = $atext;
    $this->rfc2822Tokens['atom'] = $atom;
    $this->rfc2822Tokens['dot-atom-text'] = $dotAtomText;
    $this->rfc2822Tokens['dot-atom'] = $dotAtom;
    $this->rfc2822Tokens['word'] = $word;
    $this->rfc2822Tokens['phrase'] = $phrase;
    $this->rfc2822Tokens['dtext'] = $dtext;
    $this->rfc2822Tokens['no-fold-quote'] = $noFoldQuote;
    $this->rfc2822Tokens['no-fold-literal'] = $noFoldLiteral;
    $this->rfc2822Tokens['id-left'] = $idLeft;
    $this->rfc2822Tokens['id-right'] = $idRight;
    $this->rfc2822Tokens['msg-id'] = $msgId;
    
    $this->rfc2822Tokens['display-name'] = $displayName;
    $this->rfc2822Tokens['angle-addr'] = $angleAddr;
    $this->rfc2822Tokens['local-part'] = $localPart;
    $this->rfc2822Tokens['dcontent'] = $dcontent;
    $this->rfc2822Tokens['domain-literal'] = $domainLiteral;
    $this->rfc2822Tokens['domain'] = $domain;
    $this->rfc2822Tokens['addr-spec'] = $addrSpec;
    $this->rfc2822Tokens['name-addr'] = $nameAddr;
    $this->rfc2822Tokens['mailbox'] = $mailbox;
    $this->rfc2822Tokens['mailbox-list'] = $mailboxList;
  }
  
}

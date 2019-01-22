<?php
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;

/*
 * This file is part of SwiftMailer.
 * (c) 2019 André Renaut
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A class to encapsulate Egulias Email Validator
 *
 *
 * For the record (wikipedia) :
 *     the format of email addresses is : local-part@domain-part
 *         the local  part may be up to 64 characters long and the domain may have a maximum of 255 characters.
 *         the domain part is a list of dot-separated DNS labels, each label being limited to a length of 63 characters
 *     this rule is only applied by function rfc822 (so email gets validated by either two others) !
 *
 * @author André Renaut
 */
class Swift_EmailValidator
{
    private $rfc2822_validator = null;

    private $rfc653x_validator = null;
    private $rfc653x_rules     = null;

    public $rfc     = null;
    public $message = null;

    function __construct()
    {
    }

    /**
     * @param string $email
     */
    public function isValid( $email )
    {
        switch( true )
        {
            case ( $this->rfc822(  $email ) ) :
            break;
            case ( $this->rfc2822( $email ) ) :
            break;
            case (( defined('SWIFT_ADDRESSENCODER' )   &&
                  ( SWIFT_ADDRESSENCODER == 'utf8' ) ) &&
                  ( $this->rfc653x( $email )       ) ) :
            break;
            default :
                return $this->rfc = false;
            break;
        }
        return true;
    }

    /**
    *    rfc822 - Standard for the Format of ARPA Internet Text Messages
    * 
    *    For the record (php.net) : 
    *    This validates e-mail addresses against the syntax in RFC 822, 
    *    with the exceptions that comments and whitespace folding
    *    and dotless domain names are not supported.
    *
    * @param string $email
    */
    private function rfc822( $email )
    {
         $this->rfc = 'rfc822';
         return filter_var( $email, FILTER_VALIDATE_EMAIL );
    }

    /**
    *    rfc2822 - Internet Message Format
    *
    *   inspired by deprecated Swift_Mime_Grammar
    *
    * @param string $email
    */
    protected function rfc2822( $email )
    {
        $this->rfc = 'rfc822';
        return $this->isValid_rfc2822( $email );
    }

    /**
    *    rfc5321 - Simple Mail Transfer Protocol
    *    rfc5322 - Internet Message Format
    */

    // nothing here

    /**
    *    rfc6530 - Overview and Framework for Internationalized Email
    *    rfc6531 - SMTP Extension for Internationalized Email
    *    rfc6532 - Internationalized Email Headers
    *
    * @param string $email
    */
    protected function rfc653x( $email )
    {
        $this->rfc = 'rfc653x';
        if ( !isset( $this->rfc653x_validator ) ) 
        {
            $this->rfc653x_validator = new EmailValidator;
            $this->rfc653x_rules     = new RFCValidation();
        }
        return $this->rfc653x_validator->isValid( $email, $this->rfc653x_rules );
    }

    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\\
    //*   Defines the grammar to use for validation,
    //*   implements the RFC 2822 (and friends) ABNF grammar definitions.

    /**
    *
    * @param string $email
    */
    private function isValid_rfc2822( $email )
    {
        if ( !isset( $this->rfc2822_validator ) ) $this->initialize_rfc2822();
        return ( preg_match( '/^' . $this->rfc2822_validator . '$/D', $email ) );
    }

    protected function initialize_rfc2822()
    {
        /*** Refer to RFC 2822 for ABNF grammar ***/
        
        //All basic building blocks
        $g['NO-WS-CTL'] = '[\x01-\x08\x0B\x0C\x0E-\x19\x7F]';
        $g['WSP'] = '[ \t]';
        $g['CRLF'] = '(?:\r\n)';
        $g['FWS'] = '(?:(?:' . $g['WSP'] . '*' . $g['CRLF'] . ')?' . $g['WSP'] . ')';
        $g['text'] = '[\x00-\x08\x0B\x0C\x0E-\x7F]';
        $g['quoted-pair'] = '(?:\\\\' . $g['text'] . ')';
        $g['ctext'] = '(?:' . $g['NO-WS-CTL'] . '|[\x21-\x27\x2A-\x5B\x5D-\x7E])';
        //Uses recursive PCRE (?1) -- 
        $g['ccontent'] = '(?:' . $g['ctext'] . '|' . $g['quoted-pair'] . '|(?1))';
        $g['comment'] = '(\((?:' . $g['FWS'] . '|' . $g['ccontent']. ')*' . $g['FWS'] . '?\))';
        $g['CFWS'] = '(?:(?:' . $g['FWS'] . '?' . $g['comment'] . ')*(?:(?:' . $g['FWS'] . '?' . $g['comment'] . ')|' . $g['FWS'] . '))';
        $g['qtext'] = '(?:' . $g['NO-WS-CTL'] . '|[\x21\x23-\x5B\x5D-\x7E])';
        $g['qcontent'] = '(?:' . $g['qtext'] . '|' . $g['quoted-pair'] . ')';
        $g['quoted-string'] = '(?:' . $g['CFWS'] . '?"' . '(' . $g['FWS'] . '?' . $g['qcontent'] . ')*' . $g['FWS'] . '?"' . $g['CFWS'] . '?)';
        $g['atext'] = '[a-zA-Z0-9!#\$%&\'\*\+\-\/=\?\^_`\{\}\|~]';
        $g['atom'] = '(?:' . $g['CFWS'] . '?' . $g['atext'] . '+' . $g['CFWS'] . '?)';
        $g['dot-atom-text'] = '(?:' . $g['atext'] . '+' . '(\.' . $g['atext'] . '+)*)';
        $g['dot-atom'] = '(?:' . $g['CFWS'] . '?' . $g['dot-atom-text'] . '+' . $g['CFWS'] . '?)';
        $g['word'] = '(?:' . $g['atom'] . '|' . $g['quoted-string'] . ')';
        $g['phrase'] = '(?:' . $g['word'] . '+?)';
        $g['no-fold-quote'] = '(?:"(?:' . $g['qtext'] . '|' . $g['quoted-pair'] . ')*")';
        $g['dtext'] = '(?:' . $g['NO-WS-CTL'] . '|[\x21-\x5A\x5E-\x7E])';
        $g['no-fold-literal'] = '(?:\[(?:' . $g['dtext'] . '|' . $g['quoted-pair'] . ')*\])';

        //Message IDs
        $g['id-left'] = '(?:' . $g['dot-atom-text'] . '|' . $g['no-fold-quote'] . ')';
        $g['id-right'] = '(?:' . $g['dot-atom-text'] . '|' . $g['no-fold-literal'] . ')';

        //Addresses, mailboxes and paths
        $g['local-part'] = '(?:' . $g['dot-atom'] . '|' . $g['quoted-string'] . ')';
        $g['dcontent'] = '(?:' . $g['dtext'] . '|' . $g['quoted-pair'] . ')';
        $g['domain-literal'] = '(?:' . $g['CFWS'] . '?\[(' . $g['FWS'] . '?' . $g['dcontent'] . ')*?' . $g['FWS'] . '?\]' . $g['CFWS'] . '?)';
        $g['domain'] = '(?:' . $g['dot-atom'] . '|' . $g['domain-literal'] . ')';

        $this->rfc2822_validator = '(?:' . $g['local-part'] . '@' . $g['domain'] . ')';

        unset( $g );
    }
}
<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Defines the grammar to use for validation, implements the RFC 2822 (and friends) ABNF grammar definitions.
 *
 * @author     Fabien Potencier
 * @author     Chris Corbyn
 */
class Swift_Mime_Grammar
{
    /**
     * Special characters used in the syntax which need to be escaped.
     *
     * @var string[]
     */
    private static $specials = array();

    /**
     * Tokens defined in RFC 2822 (and some related RFCs).
     *
     * @var string[]
     */
    private static $grammar = array();

    /**
     * Initialize some RFC 2822 (and friends) ABNF grammar definitions.
     */
    public function __construct()
    {
        $this->init();
    }

    public function __wakeup()
    {
        $this->init();
    }

    protected function init()
    {
        if (count(self::$specials) > 0) {
            return;
        }

        self::$specials = array(
            '(', ')', '<', '>', '[', ']',
            ':', ';', '@', ',', '.', '"',
            );

        /*** Refer to RFC 2822 for ABNF grammar ***/

        // All basic building blocks
        self::$grammar['NO-WS-CTL'] = '[\x01-\x08\x0B\x0C\x0E-\x19\x7F]';
        self::$grammar['WSP'] = '[ \t]';
        self::$grammar['CRLF'] = '(?:\r\n)';
        self::$grammar['FWS'] = '(?:(?:'.self::$grammar['WSP'].'*'.
                self::$grammar['CRLF'].')?'.self::$grammar['WSP'].')';
        self::$grammar['text'] = '[\x00-\x08\x0B\x0C\x0E-\x7F]';
        self::$grammar['quoted-pair'] = '(?:\\\\'.self::$grammar['text'].')';
        self::$grammar['ctext'] = '(?:'.self::$grammar['NO-WS-CTL'].
                '|[\x21-\x27\x2A-\x5B\x5D-\x7E])';
        // Uses recursive PCRE (?1) -- could be a weak point??
        self::$grammar['ccontent'] = '(?:'.self::$grammar['ctext'].'|'.
                self::$grammar['quoted-pair'].'|(?1))';
        self::$grammar['comment'] = '(\((?:'.self::$grammar['FWS'].'|'.
                self::$grammar['ccontent'].')*'.self::$grammar['FWS'].'?\))';
        self::$grammar['CFWS'] = '(?:(?:'.self::$grammar['FWS'].'?'.
                self::$grammar['comment'].')*(?:(?:'.self::$grammar['FWS'].'?'.
                self::$grammar['comment'].')|'.self::$grammar['FWS'].'))';
        self::$grammar['qtext'] = '(?:'.self::$grammar['NO-WS-CTL'].
                '|[\x21\x23-\x5B\x5D-\x7E])';
        self::$grammar['qcontent'] = '(?:'.self::$grammar['qtext'].'|'.
                self::$grammar['quoted-pair'].')';
        self::$grammar['quoted-string'] = '(?:'.self::$grammar['CFWS'].'?"'.
                '('.self::$grammar['FWS'].'?'.self::$grammar['qcontent'].')*'.
                self::$grammar['FWS'].'?"'.self::$grammar['CFWS'].'?)';
        self::$grammar['atext'] = '[a-zA-Z0-9!#\$%&\'\*\+\-\/=\?\^_`\{\}\|~]';
        self::$grammar['atom'] = '(?:'.self::$grammar['CFWS'].'?'.
                self::$grammar['atext'].'+'.self::$grammar['CFWS'].'?)';
        self::$grammar['dot-atom-text'] = '(?:'.self::$grammar['atext'].'+'.
                '(\.'.self::$grammar['atext'].'+)*)';
        self::$grammar['dot-atom'] = '(?:'.self::$grammar['CFWS'].'?'.
                self::$grammar['dot-atom-text'].'+'.self::$grammar['CFWS'].'?)';
        self::$grammar['word'] = '(?:'.self::$grammar['atom'].'|'.
                self::$grammar['quoted-string'].')';
        self::$grammar['phrase'] = '(?:'.self::$grammar['word'].'+?)';
        self::$grammar['no-fold-quote'] = '(?:"(?:'.self::$grammar['qtext'].
                '|'.self::$grammar['quoted-pair'].')*")';
        self::$grammar['dtext'] = '(?:'.self::$grammar['NO-WS-CTL'].
                '|[\x21-\x5A\x5E-\x7E])';
        self::$grammar['no-fold-literal'] = '(?:\[(?:'.self::$grammar['dtext'].
                '|'.self::$grammar['quoted-pair'].')*\])';

        // Message IDs
        self::$grammar['id-left'] = '(?:'.self::$grammar['dot-atom-text'].'|'.
                self::$grammar['no-fold-quote'].')';
        self::$grammar['id-right'] = '(?:'.self::$grammar['dot-atom-text'].'|'.
                self::$grammar['no-fold-literal'].')';

        // Addresses, mailboxes and paths
        self::$grammar['local-part'] = '(?:'.self::$grammar['dot-atom'].'|'.
                self::$grammar['quoted-string'].')';
        self::$grammar['dcontent'] = '(?:'.self::$grammar['dtext'].'|'.
                self::$grammar['quoted-pair'].')';
        self::$grammar['domain-literal'] = '(?:'.self::$grammar['CFWS'].'?\[('.
                self::$grammar['FWS'].'?'.self::$grammar['dcontent'].')*?'.
                self::$grammar['FWS'].'?\]'.self::$grammar['CFWS'].'?)';
        self::$grammar['domain'] = '(?:'.self::$grammar['dot-atom'].'|'.
                self::$grammar['domain-literal'].')';
        self::$grammar['addr-spec'] = '(?:'.self::$grammar['local-part'].'@'.
                self::$grammar['domain'].')';
    }

    /**
     * Get the grammar defined for $name token.
     *
     * @param string $name exactly as written in the RFC
     *
     * @return string
     */
    public function getDefinition($name)
    {
        if (array_key_exists($name, self::$grammar)) {
            return self::$grammar[$name];
        } else {
            throw new Swift_RfcComplianceException(
                "No such grammar '".$name."' defined."
                );
        }
    }

    /**
     * Returns the tokens defined in RFC 2822 (and some related RFCs).
     *
     * @return array
     */
    public function getGrammarDefinitions()
    {
        return self::$grammar;
    }

    /**
     * Returns the current special characters used in the syntax which need to be escaped.
     *
     * @return array
     */
    public function getSpecials()
    {
        return self::$specials;
    }

    /**
     * Escape special characters in a string (convert to quoted-pairs).
     *
     * @param string   $token
     * @param string[] $include additional chars to escape
     * @param string[] $exclude chars from escaping
     *
     * @return string
     */
    public function escapeSpecials($token, $include = array(), $exclude = array())
    {
        foreach (array_merge(array('\\'), array_diff(self::$specials, $exclude), $include) as $char) {
            $token = str_replace($char, '\\'.$char, $token);
        }

        return $token;
    }
}

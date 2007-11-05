<?php
// $Id: page_request.php,v 1.1 2005/04/29 23:15:51 lastcraft Exp $

class PageRequest {
    var $_parsed;
    
    function PageRequest($raw) {
        $statements = explode('&', $raw);
        $this->_parsed = array();
        foreach ($statements as $statement) {
            if (strpos($statement, '=') === false) {
                continue;
            }
            $this->_parseStatement($statement);
        }
    }
    
    /** @access private */
    function _parseStatement($statement) {
        list($key, $value) = explode('=', $statement);
        $key = urldecode($key);
        if (preg_match('/(.*)\[\]$/', $key, $matches)) {
            $key = $matches[1];
            if (! isset($this->_parsed[$key])) {
                $this->_parsed[$key] = array();
            }
            $this->_addValue($key, $value);
        } elseif (isset($this->_parsed[$key])) {
            $this->_addValue($key, $value);
        } else {
            $this->_setValue($key, $value);
        }
    }
    
    /** @access private */
    function _addValue($key, $value) {
        if (! is_array($this->_parsed[$key])) {
            $this->_parsed[$key] = array($this->_parsed[$key]);
        }
        $this->_parsed[$key][] = urldecode($value);
    }
    
    /** @access private */
    function _setValue($key, $value) {
        $this->_parsed[$key] = urldecode($value);
    }
    
    function getAll() {
        return $this->_parsed;
    }
    
    function get() {
        $request = &new PageRequest($_SERVER['QUERY_STRING']);
        return $request->getAll();
    }
    
    function post() {
        global $HTTP_RAW_POST_DATA;
        $request = &new PageRequest($HTTP_RAW_POST_DATA);
        return $request->getAll();
    }
}
?>
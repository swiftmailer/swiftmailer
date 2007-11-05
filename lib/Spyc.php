<?php
/**
   * Spyc -- A Simple PHP YAML Class
   * @version 0.3
   * @author Chris Wanstrath <chris@ozmm.org>
   * @author Vlad Andersen <vlad@oneiros.ru>
   * @link http://spyc.sourceforge.net/
   * @copyright Copyright 2005-2006 Chris Wanstrath
   * @license http://www.opensource.org/licenses/mit-license.php MIT License
   * @package Spyc
   */
/**
   * The Simple PHP YAML Class.
   *
   * This class can be used to read a YAML file and convert its contents
   * into a PHP array.  It currently supports a very limited subsection of
   * the YAML spec.
   *
   * Usage:
   * <code>
   *   $parser = new Spyc;
   *   $array  = $parser->load($file);
   * </code>
   * @package Spyc
   */
class Spyc {

  /**#@+
  * @access private
  * @var mixed
  */
  private $_haveRefs;
  private $_allNodes;
  private $_allParent;
  private $_lastIndent;
  private $_lastNode;
  private $_inBlock;
  private $_isInline;
  private $_dumpIndent;
  private $_dumpWordWrap;
  private $_containsGroupAnchor = false;
  private $_containsGroupAlias = false;
  private $path;
  private $result;
  private $LiteralBlockMarkers = array ('>', '|');
  private $LiteralPlaceHolder = '___YAML_Literal_Block___';
  private $SavedGroups = array();

  /**#@+
  * @access public
  * @var mixed
  */
  public $_nodeId;

  /**
     * Load YAML into a PHP array statically
     *
     * The load method, when supplied with a YAML stream (string or file),
     * will do its best to convert YAML in a file into a PHP array.  Pretty
     * simple.
     *  Usage:
     *  <code>
     *   $array = Spyc::YAMLLoad('lucky.yaml');
     *   print_r($array);
     *  </code>
     * @access public
     * @return array
     * @param string $input Path of YAML file or string containing YAML
     */
  public static function YAMLLoad($input) {
    $Spyc = new Spyc;
    return $Spyc->load($input);
  }

  /**
     * Dump YAML from PHP array statically
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.  Pretty simple.  Feel free to
     * save the returned string as nothing.yaml and pass it around.
     *
     * Oh, and you can decide how big the indent is and what the wordwrap
     * for folding is.  Pretty cool -- just pass in 'false' for either if
     * you want to use the default.
     *
     * Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
     * you can turn off wordwrap by passing in 0.
     *
     * @access public
     * @return string
     * @param array $array PHP array
     * @param int $indent Pass in false to use the default, which is 2
     * @param int $wordwrap Pass in 0 for no wordwrap, false for default (40)
     */
  public static function YAMLDump($array,$indent = false,$wordwrap = false) {
    $spyc = new Spyc;
    return $spyc->dump($array,$indent,$wordwrap);
  }


  /**
     * Dump PHP array to YAML
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.  Pretty simple.  Feel free to
     * save the returned string as tasteful.yaml and pass it around.
     *
     * Oh, and you can decide how big the indent is and what the wordwrap
     * for folding is.  Pretty cool -- just pass in 'false' for either if
     * you want to use the default.
     *
     * Indent's default is 2 spaces, wordwrap's default is 40 characters.  And
     * you can turn off wordwrap by passing in 0.
     *
     * @access public
     * @return string
     * @param array $array PHP array
     * @param int $indent Pass in false to use the default, which is 2
     * @param int $wordwrap Pass in 0 for no wordwrap, false for default (40)
     */
  public function dump($array,$indent = false,$wordwrap = false) {
    // Dumps to some very clean YAML.  We'll have to add some more features
    // and options soon.  And better support for folding.

    // New features and options.
    if ($indent === false or !is_numeric($indent)) {
      $this->_dumpIndent = 2;
    } else {
      $this->_dumpIndent = $indent;
    }

    if ($wordwrap === false or !is_numeric($wordwrap)) {
      $this->_dumpWordWrap = 40;
    } else {
      $this->_dumpWordWrap = $wordwrap;
    }

    // New YAML document
    $string = "---\n";

    // Start at the base of the array and move through it.
    foreach ($array as $key => $value) {
      $string .= $this->_yamlize($key,$value,0);
    }
    return $string;
  }

  /**
     * Attempts to convert a key / value array item to YAML
     * @access private
     * @return string
     * @param $key The name of the key
     * @param $value The value of the item
     * @param $indent The indent of the current node
     */
  private function _yamlize($key,$value,$indent) {
    if (is_array($value)) {
      // It has children.  What to do?
      // Make it the right kind of item
      $string = $this->_dumpNode($key,NULL,$indent);
      // Add the indent
      $indent += $this->_dumpIndent;
      // Yamlize the array
      $string .= $this->_yamlizeArray($value,$indent);
    } elseif (!is_array($value)) {
      // It doesn't have children.  Yip.
      $string = $this->_dumpNode($key,$value,$indent);
    }
    return $string;
  }

  /**
     * Attempts to convert an array to YAML
     * @access private
     * @return string
     * @param $array The array you want to convert
     * @param $indent The indent of the current level
     */
  private function _yamlizeArray($array,$indent) {
    if (is_array($array)) {
      $string = '';
      foreach ($array as $key => $value) {
        $string .= $this->_yamlize($key,$value,$indent);
      }
      return $string;
    } else {
      return false;
    }
  }

  /**
     * Returns YAML from a key and a value
     * @access private
     * @return string
     * @param $key The name of the key
     * @param $value The value of the item
     * @param $indent The indent of the current node
     */
  private function _dumpNode($key,$value,$indent) {
    // do some folding here, for blocks
    if (strpos($value,"\n") !== false || strpos($value,": ") !== false || strpos($value,"- ") !== false) {
      $value = $this->_doLiteralBlock($value,$indent);
    } else {
      $value  = $this->_doFolding($value,$indent);
    }

    if (is_bool($value)) {
      $value = ($value) ? "true" : "false";
    }

    $spaces = str_repeat(' ',$indent);

    if (is_int($key)) {
      // It's a sequence
      $string = $spaces.'- '.$value."\n";
    } else {
      // It's mapped
      $string = $spaces.$key.': '.$value."\n";
    }
    return $string;
  }

  /**
     * Creates a literal block for dumping
     * @access private
     * @return string
     * @param $value
     * @param $indent int The value of the indent
     */
  private function _doLiteralBlock($value,$indent) {
    $exploded = explode("\n",$value);
    $newValue = '|';
    $indent  += $this->_dumpIndent;
    $spaces   = str_repeat(' ',$indent);
    foreach ($exploded as $line) {
      $newValue .= "\n" . $spaces . trim($line);
    }
    return $newValue;
  }

  /**
     * Folds a string of text, if necessary
     * @access private
     * @return string
     * @param $value The string you wish to fold
     */
  private function _doFolding($value,$indent) {
    // Don't do anything if wordwrap is set to 0
    if ($this->_dumpWordWrap === 0) {
      return $value;
    }

    if (strlen($value) > $this->_dumpWordWrap) {
      $indent += $this->_dumpIndent;
      $indent = str_repeat(' ',$indent);
      $wrapped = wordwrap($value,$this->_dumpWordWrap,"\n$indent");
      $value   = ">\n".$indent.$wrapped;
    }
    return $value;
  }

/* LOADING FUNCTIONS */

  private function load($input) {
    $Source = $this->loadFromSource($input);
    if (empty ($Source)) return array();
    $this->path = array();
    $this->result = array();


    for ($i = 0; $i < count($Source); $i++) {
      $line = $Source[$i];
      
      $lineIndent = $this->_getIndent($line);
      $this->path = $this->getParentPathByIndent($lineIndent);
      $line = $this->stripIndent($line, $lineIndent);
      if ($this->isComment($line)) continue;

      if ($literalBlockStyle = $this->startsLiteralBlock($line)) {
        $line = rtrim ($line, $literalBlockStyle . "\n");
        $literalBlock = '';
        $line .= $this->LiteralPlaceHolder;

        while (++$i < count($Source) && $this->literalBlockContinues($Source[$i], $lineIndent)) {
          $literalBlock = $this->addLiteralLine($literalBlock, $Source[$i], $literalBlockStyle);
        }
        $i--;
      }
      $lineArray = $this->_parseLine($line);
      if ($literalBlockStyle)
      $lineArray = $this->revertLiteralPlaceHolder ($lineArray, $literalBlock);

      $this->addArray($lineArray, $lineIndent);
    }
    return $this->result;
  }

  private function loadFromSource ($input) {
    if (!empty($input) && strpos($input, "\n") === false && file_exists($input))
    return file($input);

    $foo = explode("\n",$input);
    foreach ($foo as $k => $_) {
      $foo[$k] = trim ($_, "\r");
    }
    return $foo;
  }

  /**
     * Finds and returns the indentation of a YAML line
     * @access private
     * @return int
     * @param string $line A line from the YAML file
     */
  private function _getIndent($line) {
    if (!preg_match('/^ +/',$line,$match)) return 0;
    if (!empty($match[0])) return strlen ($match[0]);
    return 0;
  }

  /**
     * Parses YAML code and returns an array for a node
     * @access private
     * @return array
     * @param string $line A line from the YAML file
     */
  private function _parseLine($line) {
    if (!$line) return array();
    $line = trim($line);
    if (!$line) return array();
    $array = array();

    if ($group = $this->nodeContainsGroup($line)) {
      $this->addGroup($line, $group);
      $line = $this->stripGroup ($line, $group);
    }

    if ($this->startsMappedSequence($line))
      return $this->returnMappedSequence($line);

    if ($this->startsMappedValue($line))
      return $this->returnMappedValue($line);

    if ($this->isArrayElement($line))
     return $this->returnArrayElement($line);

    if ($this->isPlainArray($line))
     return $this->returnPlainArray($line); 
     
     
    return $this->returnKeyValuePair($line);

  }

  /**
     * Finds the type of the passed value, returns the value as the new type.
     * @access private
     * @param string $value
     * @return mixed
     */
  private function _toType($value) {

    if (strpos($value, '#') !== false)
      $value = trim(preg_replace('/#(.+)$/','',$value));

    if (preg_match('/^("(.*)"|\'(.*)\')/',$value,$matches)) {
      $value = (string)preg_replace('/(\'\'|\\\\\')/',"'",end($matches));
      $value = preg_replace('/\\\\"/','"',$value);
    } elseif (preg_match('/^\\[(.+)\\]$/',$value,$matches)) {
      // Inline Sequence

      // Take out strings sequences and mappings
      $explode = $this->_inlineEscape($matches[1]);

      // Propagate value array
      $value  = array();
      foreach ($explode as $v) {
        $value[] = $this->_toType($v);
      }
    } elseif (strpos($value,': ')!==false && !preg_match('/^{(.+)/',$value)) {
      // It's a map
      $array = explode(': ',$value);
      $key   = trim($array[0]);
      array_shift($array);
      $value = trim(implode(': ',$array));
      $value = $this->_toType($value);
      $value = array($key => $value);
    } elseif (preg_match("/{(.+)}$/",$value,$matches)) {
      // Inline Mapping

      // Take out strings sequences and mappings
      $explode = $this->_inlineEscape($matches[1]);

      // Propogate value array
      $array = array();
      foreach ($explode as $v) {
        $SubArr = $this->_toType($v);
        if (empty($SubArr)) continue;
        if (is_array ($SubArr)) {
          $array[key($SubArr)] = $SubArr[key($SubArr)]; continue;
        }
        $array[] = $SubArr;
      }
      $value = $array;
    } elseif (strtolower($value) == 'null' or $value == '' or $value == '~') {
      $value = null;
    } elseif (preg_match ('/^[0-9]+$/', $value)) {
      $value = (int)$value;
    } elseif (in_array(strtolower($value),
    array('true', 'on', '+', 'yes', 'y'))) {
      $value = true;
    } elseif (in_array(strtolower($value),
    array('false', 'off', '-', 'no', 'n'))) {
      $value = false;
    } elseif (is_numeric($value)) {
      $value = (float)$value;
    } else {
      // Just a normal string, right?

    }


    //  print_r ($value);
    return $value;
  }

  /**
     * Used in inlines to check for more inlines or quoted strings
     * @access private
     * @return array
     */
  private function _inlineEscape($inline) {
    // There's gotta be a cleaner way to do this...
    // While pure sequences seem to be nesting just fine,
    // pure mappings and mappings with sequences inside can't go very
    // deep.  This needs to be fixed.

    $saved_strings = array();

    // Check for strings
    $regex = '/(?:(")|(?:\'))((?(1)[^"]+|[^\']+))(?(1)"|\')/';
    if (preg_match_all($regex,$inline,$strings)) {
      $saved_strings = $strings[0];
      $inline  = preg_replace($regex,'YAMLString',$inline);
    }
    unset($regex);

    // Check for sequences
    if (preg_match_all('/\[(.+)\]/U',$inline,$seqs)) {
      $inline = preg_replace('/\[(.+)\]/U','YAMLSeq',$inline);
      $seqs   = $seqs[0];
    }

    // Check for mappings
    if (preg_match_all('/{(.+)}/U',$inline,$maps)) {
      $inline = preg_replace('/{(.+)}/U','YAMLMap',$inline);
      $maps   = $maps[0];
    }

    $explode = explode(', ',$inline);


    // Re-add the sequences
    if (!empty($seqs)) {
      $i = 0;
      foreach ($explode as $key => $value) {
        if (strpos($value,'YAMLSeq') !== false) {
          $explode[$key] = str_replace('YAMLSeq',$seqs[$i],$value);
          ++$i;
        }
      }
    }

    // Re-add the mappings
    if (!empty($maps)) {
      $i = 0;
      foreach ($explode as $key => $value) {
        if (strpos($value,'YAMLMap') !== false) {
          $explode[$key] = str_replace('YAMLMap',$maps[$i],$value);
          ++$i;
        }
      }
    }


    // Re-add the strings
    if (!empty($saved_strings)) {
      $i = 0;
      foreach ($explode as $key => $value) {
        while (strpos($value,'YAMLString') !== false) {
          $explode[$key] = preg_replace('/YAMLString/',$saved_strings[$i],$value, 1);
          ++$i;
          $value = $explode[$key];
        }
      }
    }

    return $explode;
  }

  private function literalBlockContinues ($line, $lineIndent) {
    if (!trim($line)) return true;
    if ($this->_getIndent($line) > $lineIndent) return true;
    return false;
  }

  private function addArrayInline ($array, $indent) {
      $CommonGroupPath = $this->path;
      if (empty ($array)) return false;
      
      foreach ($array as $k => $_) {
        $this->addArray(array($k => $_), $indent);
        $this->path = $CommonGroupPath;
      }
      return true;
  }
  
  private function addArray ($array, $indent) {

    if (count ($array) > 1)
      return $this->addArrayInline ($array, $indent);
    
    $key = key ($array);
    
    if (!isset ($array[$key])) return false;
    if ($array[$key] === array()) { $array[$key] = ''; };
    $value = $array[$key];

    // Unfolding inner array tree as defined in $this->_arrpath.
    //$_arr = $this->result; $_tree[0] = $_arr; $i = 1;

    $tempPath = Spyc::flatten ($this->path);
    eval ('$_arr = $this->result' . $tempPath . ';');


    if ($this->_containsGroupAlias) {
      do {
        if (!isset($this->SavedGroups[$this->_containsGroupAlias])) { echo "Bad group name: $this->_containsGroupAlias."; break; }
        $groupPath = $this->SavedGroups[$this->_containsGroupAlias];
        eval ('$value = $this->result' . Spyc::flatten ($groupPath) . ';');
      } while (false);
      $this->_containsGroupAlias = false;
    }


    // Adding string or numeric key to the innermost level or $this->arr.
    if ($key)
    $_arr[$key] = $value;
    else {
      if (!is_array ($_arr)) { $_arr = array ($value); $key = 0; }
      else { $_arr[] = $value; end ($_arr); $key = key ($_arr); }

    }

    $this->path[$indent] = $key;

    eval ('$this->result' . $tempPath . ' = $_arr;');

    if ($this->_containsGroupAnchor) {
      $this->SavedGroups[$this->_containsGroupAnchor] = $this->path;
      $this->_containsGroupAnchor = false;
    }


  }


  private function flatten ($array) {
    $tempPath = array();
    if (!empty ($array)) {
      foreach ($array as $_) {
        if (!is_int($_)) $_ = "'$_'";
        $tempPath[] = "[$_]";
      }
    }
    //end ($tempPath); $latestKey = key($tempPath);
    $tempPath = implode ('', $tempPath);
    return $tempPath;
  }



  private function startsLiteralBlock ($line) {
    $lastChar = substr (trim($line), -1);
    if (in_array ($lastChar, $this->LiteralBlockMarkers))
    return $lastChar;
    return false;
  }

  private function addLiteralLine ($literalBlock, $line, $literalBlockStyle) {
    $line = $this->stripIndent($line);
    $line = str_replace ("\r\n", "\n", $line);

    if ($literalBlockStyle == '|') {
      return $literalBlock . $line;
    }
    if (strlen($line) == 0) return $literalBlock . "\n";

   // echo "|$line|";
    if ($line != "\n")
      $line = trim ($line, "\r\n ") . " ";

    return $literalBlock . $line;
  }

  private function revertLiteralPlaceHolder ($lineArray, $literalBlock) {

    foreach ($lineArray as $k => $_) {
      if (substr($_, -1 * strlen ($this->LiteralPlaceHolder)) == $this->LiteralPlaceHolder)
      $lineArray[$k] = rtrim ($literalBlock, " \r\n");
    }
    return $lineArray;
  }

  private function stripIndent ($line, $indent = -1) {
    if ($indent == -1) $indent = $this->_getIndent($line);
    return substr ($line, $indent);
  }

  private function getParentPathByIndent ($indent) {

    if ($indent == 0) return array();

    $linePath = $this->path;
    do {
      end($linePath); $lastIndentInParentPath = key($linePath);
      if ($indent <= $lastIndentInParentPath) array_pop ($linePath);
    } while ($indent <= $lastIndentInParentPath);
    return $linePath;
  }


  private function clearBiggerPathValues ($indent) {


    if ($indent == 0) $this->path = array();
    if (empty ($this->path)) return true;

    foreach ($this->path as $k => $_) {
      if ($k > $indent) unset ($this->path[$k]);
    }

    return true;
  }


  private function isComment ($line) {
    if (preg_match('/^#/', $line)) return true;
    if (trim($line, " \r\n\t") == '---') return true;
    return false;
  }

  private function isArrayElement ($line) {
    if (!$line) return false;
    if ($line[0] != '-') return false;
    if (strlen ($line) > 3)
      if (substr($line,0,3) == '---') return false;
    
    return true;
  }

  private function isHashElement ($line) {
    if (!preg_match('/^(.+?):/', $line, $matches)) return false;
    $allegedKey = $matches[1];
    if ($allegedKey) return true;
    //if (substr_count($allegedKey, )
    return false;
  }

  private function isLiteral ($line) {
    if ($this->isArrayElement($line)) return false;
    if ($this->isHashElement($line)) return false;
    return true;
  }


  private function startsMappedSequence ($line) {
    if (preg_match('/^-(.*):$/',$line)) return true;
  }

  private function returnMappedSequence ($line) {
    $array = array();
    $key         = trim(substr(substr($line,1),0,-1));
    $array[$key] = '';
    return $array;
  }

  private function returnMappedValue ($line) {
    $array = array();
    $key         = trim(substr($line,0,-1));
    $array[$key] = '';
    return $array;
  }

  private function startsMappedValue ($line) {
    if (preg_match('/^(.*):$/',$line)) return true;
  }
  
  private function isPlainArray ($line) {
    if (preg_match('/^\[(.*)\]$/', $line)) return true;
    return false;
  }
  
  private function returnPlainArray ($line) {
    return $this->_toType($line); 
  }  

  private function returnKeyValuePair ($line) {

    $array = array();

    if (preg_match('/^(.+):/',$line,$key)) {
      // It's a key/value pair most likely
      // If the key is in double quotes pull it out
      if (preg_match('/^(["\'](.*)["\'](\s)*:)/',$line,$matches)) {
        $value = trim(str_replace($matches[1],'',$line));
        $key   = $matches[2];
      } else {
        // Do some guesswork as to the key and the value
        $explode = explode(':',$line);
        $key     = trim($explode[0]);
        array_shift($explode);
        $value   = trim(implode(':',$explode));
      }

      // Set the type of the value.  Int, string, etc
      $value = $this->_toType($value);
      if (empty($key)) {
        $array[]     = $value;
      } else {
        $array[$key] = $value;
      }
    }

    return $array;

  }


  private function returnArrayElement ($line) {
     if (strlen($line) <= 1) return array(array()); // Weird %)
     $array = array();
     $value   = trim(substr($line,1));
     $value   = $this->_toType($value);
     $array[] = $value;
     return $array;
  }


  private function nodeContainsGroup ($line) {    
    $symbolsForReference = 'A-z0-9_\-';
    if (strpos($line, '&') === false && strpos($line, '*') === false) return false; // Please die fast ;-)
    if (preg_match('/^(&['.$symbolsForReference.']+)/', $line, $matches)) return $matches[1];
    if (preg_match('/^(\*['.$symbolsForReference.']+)/', $line, $matches)) return $matches[1];
    if (preg_match('/(&['.$symbolsForReference.']+$)/', $line, $matches)) return $matches[1];
    if (preg_match('/(\*['.$symbolsForReference.']+$)/', $line, $matches)) return $matches[1];
    return false;

  }

  private function addGroup ($line, $group) {
    if (substr ($group, 0, 1) == '&') $this->_containsGroupAnchor = substr ($group, 1);
    if (substr ($group, 0, 1) == '*') $this->_containsGroupAlias = substr ($group, 1);
    //print_r ($this->path);
  }

  private function stripGroup ($line, $group) {
    $line = trim(str_replace($group, '', $line));
    return $line;
  }


}
?>

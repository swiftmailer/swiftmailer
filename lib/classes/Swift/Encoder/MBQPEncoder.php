<?php

/*
 The Quoted Printable encoder in Swift Mailer.

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

//@require 'Swift/Encoder.php';
//@require 'Swift/CharacterStream.php';

/**
 * Works with the bqp_encode pecl extension, works in respect for the charset
 * @package Swift
 * @subpackage Encoder
 * @author Chris Corbyn
 */
class Swift_Encoder_MBQPEncoder implements Swift_Encoder
{
	private $charset="utf-8";
	/**
	 * @param $charset
	 */
	public function charsetChanged($charset) {
		$this->charset=$charset;
	}

	/**
	 * @param $string
	 * @param $firstLineOffset
	 * @param $maxLineLength
	 */
	public function encodeString($string, $firstLineOffset = 0,
    $maxLineLength = 0) {
		if ($firstLineOffset>0) {
			return substr(mb_convert_encoding(str_repeat(" ", $firstLineOffset).$string, "Quoted-Printable", $this->charset),$firstLineOffset);
		}
		return mb_convert_encoding($string, "Quoted-Printable", $this->charset);
	}
}

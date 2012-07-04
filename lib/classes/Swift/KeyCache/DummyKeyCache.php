<?php

/*
 A dummy KeyCache used to exclude cache layer from problems

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

/**
 * A basic KeyCache backed by an array.
 * @package Swift
 * @subpackage KeyCache
 * @author Xavier De Cock <xdecock@gmail.com>
 */
class Swift_KeyCache_DummyKeyCache implements Swift_KeyCache
{
    /**
     * Set a string into the cache under $itemKey for the namespace $nsKey.
     * @param string $nsKey
     * @param string $itemKey
     * @param string $string
     * @param int    $mode
     * @see MODE_WRITE, MODE_APPEND
     */
    public function setString($nsKey, $itemKey, $string, $mode)
    {
    }

    /**
     * Set a ByteStream into the cache under $itemKey for the namespace $nsKey.
     * @param string                 $nsKey
     * @param string                 $itemKey
     * @param Swift_OutputByteStream $os
     * @param int                    $mode
     * @see MODE_WRITE, MODE_APPEND
     */
    public function importFromByteStream($nsKey, $itemKey, Swift_OutputByteStream $os, $mode)
    {
    }

    /**
     * Provides a ByteStream which when written to, writes data to $itemKey.
     * NOTE: The stream will always write in append mode.
     * @param  string                $nsKey
     * @param  string                $itemKey
     * @return Swift_InputByteStream
     */
    public function getInputByteStream($nsKey, $itemKey, Swift_InputByteStream $writeThrough = null)
    {
        return false;
    }

    /**
     * Get data back out of the cache as a string.
     * @param  string $nsKey
     * @param  string $itemKey
     * @return string
     */
    public function getString($nsKey, $itemKey)
    {
        return false;
    }

    /**
     * Get data back out of the cache as a ByteStream.
     * @param string                $nsKey
     * @param string                $itemKey
     * @param Swift_InputByteStream $is      to write the data to
     */
    public function exportToByteStream($nsKey, $itemKey, Swift_InputByteStream $is)
    {
        return false;
    }

    /**
     * Check if the given $itemKey exists in the namespace $nsKey.
     * @param  string  $nsKey
     * @param  string  $itemKey
     * @return boolean
     */
    public function hasKey($nsKey, $itemKey)
    {
        return false;
    }

    /**
     * Clear data for $itemKey in the namespace $nsKey if it exists.
     * @param string $nsKey
     * @param string $itemKey
     */
    public function clearKey($nsKey, $itemKey)
    {
    }

    /**
     * Clear all data in the namespace $nsKey if it exists.
     * @param string $nsKey
     */
    public function clearAll($nsKey)
    {
    }
}

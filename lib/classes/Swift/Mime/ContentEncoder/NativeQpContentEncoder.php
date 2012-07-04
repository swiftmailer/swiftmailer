<?php

class Swift_Mime_ContentEncoder_NativeQpContentEncoder implements Swift_Mime_ContentEncoder
{
    /**
     * Notify this observer that the entity's charset has changed.
     * @param string $charset
     */
    public function charsetChanged($charset)
    {
        if ($charset !== 'utf-8') {
            throw new RuntimeException(
                sprintf('Charset "%s" not supported. NativeQpContentEncoder only supports "utf-8"', $charset));
        }
    }

    /**
     * Encode $in to $out.
     * @param Swift_OutputByteStream $os              to read from
     * @param Swift_InputByteStream  $is              to write to
     * @param int                    $firstLineOffset
     * @param int                    $maxLineLength   - 0 indicates the default length for this encoding
     */
    public function encodeByteStream(
        Swift_OutputByteStream $os, Swift_InputByteStream $is, $firstLineOffset = 0,
        $maxLineLength = 0)
    {
        $string = '';

        while (false !== $bytes = $os->read(8192)) {
            $string .= $bytes;
        }

        $is->write($this->encodeString($string));
    }

    /**
     * Get the MIME name of this content encoding scheme.
     * @return string
     */
    public function getName()
    {
        return 'quoted-printable';
    }

    /**
     * Encode a given string to produce an encoded string.
     * @param  string $string
     * @param  int    $firstLineOffset if first line needs to be shorter
     * @param  int    $maxLineLength   - 0 indicates the default length for this encoding
     * @return string
     */
    public function encodeString($string, $firstLineOffset = 0,
                               $maxLineLength = 0)
    {
        return quoted_printable_encode($string);
    }
}

<?php

/**
 * A binary safe string comparison.
 *
 * @author Chris Corbyn
 */
class IdenticalBinaryConstraint extends \PHPUnit\Framework\Constraint\Constraint
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    public function matches($other): bool
    {
        $aHex = $this->asHexString($this->value);
        $bHex = $this->asHexString($other);

        return $aHex === $bHex;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'identical binary';
    }

    /**
     * Get the given string of bytes as a stirng of Hexadecimal sequences.
     *
     * @param string $binary
     *
     * @return string
     */
    private function asHexString($binary)
    {
        $hex = '';

        $bytes = unpack('H*', $binary);

        foreach ($bytes as &$byte) {
            $byte = strtoupper($byte);
        }

        return implode('', $bytes);
    }
}

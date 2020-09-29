<?php

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * A base test case with some custom expectations.
 *
 * @author Rouven Weßling
 */
class SwiftMailerTestCase extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;

    public static function regExp($pattern)
    {
        if (!\is_string($pattern)) {
            throw PHPUnit\Util\InvalidArgumentHelper::factory(1, 'string');
        }

        return new \PHPUnit\Framework\Constraint\RegularExpression($pattern);
    }

    public function assertIdenticalBinary($expected, $actual, $message = '')
    {
        $constraint = new IdenticalBinaryConstraint($expected);
        self::assertThat($actual, $constraint, $message);
    }

    protected function getMockery($class)
    {
        return \Mockery::mock($class);
    }
}

<?php

declare(strict_types=1);

namespace HamcrestMockeryTest;

use ArrayAccess;
use Generator;
use Hamcrest\Matcher;
use Hamcrest\StringDescription;
use Hamcrest\Text\StringStartsWith;
use HamcrestMockery\Bridge;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Exception\InvalidCountException;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test the Bridge Class
 *
 * @covers HamcrestMockery\Bridge
 * @covers hmb
 */
class BridgeTest extends TestCase
{
    use MockeryPHPUnitIntegration {
        mockeryAssertPostConditions as protected mockeryAssertPostConditionsTrait;
    }

    /** @var MockInterface|null|ArrayAccess  */
    private $mockedArrayAccess = null;

    public function testBridge(): void
    {
        $mockedMatcher = mock(Matcher::class);
        $mockedMatcher->allows()->describeTo(self::isInstanceOf(StringDescription::class));
        $mockedMatcher->allows()->matches('Test')->andReturns(true);
        $mockedMatcher->allows()->matches('Test2')->andReturns(false);
        $mockedDescription = mock(StringDescription::class);
        $mockedDescription->allows()->appendDescriptionOf($mockedMatcher)->andReturnSelf()->once();
        $mockedDescription->allows()->__toString()->andReturn('hi')->once();
        $mockedMatcher->allows()->describeMismatch('Test2', $mockedDescription)->once();
        $subject = new Bridge($mockedMatcher);
        $this->assertSame('', (string) $subject);
        $subject->setDescription($mockedDescription);

        $test = 'Test';
        $subject->match($test);
        $test2 = 'Test2';
        $subject->match($test2);

        $this->assertSame('hi', (string) $subject);
    }

    public function testOperability(): void
    {
        $this->mockedArrayAccess = mock(ArrayAccess::class);
        $this->mockedArrayAccess->allows()
            ->offsetGet(hmb(StringStartsWith::startsWith('Hello')))->andReturns(true);
        $this->mockedArrayAccess->allows()
            ->offsetGet(hmb(StringStartsWith::startsWith('Nope')))->twice()->andReturns(true);

        $this->assertTrue($this->mockedArrayAccess->offsetGet('Hello World'));

        $this->mockedArrayAccess->offsetGet('Nope');
    }

    protected function mockeryAssertPostConditions(): void
    {
        if ($this->mockedArrayAccess !== null) {
            $template = 'Method offsetGet(a string starting with "Nope")'.
                ' from %s should be called exactly 2 times but called 1 times.';
            $message = sprintf($template, get_class($this->mockedArrayAccess));
            try {
                $this->mockeryAssertPostConditionsTrait();
            } catch (InvalidCountException $t) {
                if (!$this->isSame($message, $t->getMessage())) {
                    throw $t;
                }
            }
        } else {
            $this->mockeryAssertPostConditionsTrait();
        }
    }

    private function isSame(string $message, string $getMessage): bool
    {
        $message = str_replace(["\n", "\r", "\t"], '', $message);
        $getMessage = str_replace(["\n", "\r", "\t"], '', $getMessage);
        return $message === $getMessage;
    }
}

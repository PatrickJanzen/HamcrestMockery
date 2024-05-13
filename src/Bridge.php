<?php

declare(strict_types=1);

namespace HamcrestMockery;

use Hamcrest\Matcher;
use Hamcrest\StringDescription;
use Mockery\Matcher\MatcherInterface;

class Bridge implements MatcherInterface
{
    private Matcher $hamcrestMatcher;
    private StringDescription $description;

    public function __construct(Matcher $hamcrestMatcher)
    {
        $this->hamcrestMatcher = $hamcrestMatcher;
        $this->setDescription(new StringDescription());
    }

    /** @inheritdoc */
    public function match(&$actual): bool
    {
        $match = $this->hamcrestMatcher->matches($actual);
        if (!$match) {
            $this->hamcrestMatcher->describeMismatch($actual, $this->description);
        }
        return $match;
    }

    public function setDescription(StringDescription $description): void
    {
        $this->description = $description;
        $this->description->appendDescriptionOf($this->hamcrestMatcher);
    }

    public function __toString(): string
    {
        return $this->description->__toString();
    }
}

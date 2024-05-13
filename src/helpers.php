<?php

declare(strict_types=1);

use Hamcrest\Matcher;
use HamcrestMockery\Bridge;

if (! function_exists('hmb')) {
    function hmb(Matcher $hamcrestMatcher): Bridge
    {
        return new Bridge($hamcrestMatcher);
    }
}

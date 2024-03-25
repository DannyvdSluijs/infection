<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Operator;

use Infection\Mutator\Operator\NullSafeMethodCall;
use Infection\Tests\Mutator\BaseMutatorTestCase;
use const PHP_VERSION_ID;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(NullSafeMethodCall::class)]
final class NullSafeMethodCallTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped('Null Safe operator is available only in PHP 8 or higher');
        }

        $this->doTest($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'Mutate nullsafe method call' => [
            <<<'PHP'
                <?php

                $class?->getName();
                PHP
            ,
            <<<'PHP'
                <?php

                $class->getName();
                PHP,
        ];

        yield 'Mutate nullsafe method call only' => [
            <<<'PHP'
                <?php

                $class?->getName()?->property;
                PHP
            ,
            <<<'PHP'
                <?php

                $class->getName()?->property;
                PHP,
        ];

        yield 'Mutate chain of nullsafe method calls' => [
            <<<'PHP'
                <?php

                $class?->getObject()?->getName();
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $class->getObject()?->getName();
                    PHP,
                <<<'PHP'
                    <?php

                    $class?->getObject()->getName();
                    PHP,
            ],
        ];

        yield 'Mutate nullsafe applied right when class has been instantiated' => [
            <<<'PHP'
                <?php

                (new SomeClass())?->methodCall();
                PHP,
            <<<'PHP'
                <?php

                (new SomeClass())->methodCall();
                PHP,
        ];

        yield 'Mutate nullsafe with dynamic method name' => [
            <<<'PHP'
                <?php

                $class?->{$methodCall}();
                PHP,
            <<<'PHP'
                <?php

                $class->{$methodCall}();
                PHP,
        ];
    }
}

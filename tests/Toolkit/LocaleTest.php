<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Toolkit\Locale
 */
class LocaleTest extends TestCase
{
    protected $locale = [];
    protected $localeSuffix;

    public function setUp(): void
    {
        $constants = [
            LC_ALL, LC_COLLATE, LC_CTYPE, LC_MONETARY,
            LC_NUMERIC, LC_TIME, LC_MESSAGES
        ];

        // make a backup of the current locale
        foreach ($constants as $constant) {
            $this->locale[$constant] = setlocale($constant, '0');
        }

        // test which locale suffix the system supports
        setlocale(LC_ALL, 'de_DE.' . $this->localeSuffix);
        if (setlocale(LC_ALL, '0') === 'de_DE.' . $this->localeSuffix) {
            $this->localeSuffix = 'utf8';
        } else {
            $this->localeSuffix = 'UTF-8';
        }

        // now set a baseline
        setlocale(LC_ALL, 'C');
    }

    public function tearDown(): void
    {
        Locale::set($this->locale);
        $this->locale = [];
    }

    /**
     * @covers ::export
     */
    public function testExport()
    {
        // valid array
        $this->assertSame([
            'LC_ALL'     => 'test1',
            'LC_NUMERIC' => 'test2'
        ], Locale::export([
            LC_ALL     => 'test1',
            LC_NUMERIC => 'test2'
        ]));

        // with prepared string key
        $this->assertSame([
            'LC_TEST' => 'test'
        ], Locale::export([
            'LC_TEST' => 'test'
        ]));

        // unknown key
        $this->assertSame([
            1234 => 'test'
        ], Locale::export([
            1234 => 'test'
        ]));
    }

    /**
     * @covers ::normalize
     */
    public function testNormalize()
    {
        // empty array
        $this->assertSame([], Locale::normalize([]));

        // array with different key types
        $this->assertSame([
            LC_ALL     => 'test1',
            LC_NUMERIC => 'test2',
            'TEST'     => 'test3'
        ], Locale::normalize([
            'LC_ALL'   => 'test1',
            LC_NUMERIC => 'test2',
            'TEST'     => 'test3'
        ]));

        // single string
        $this->assertSame([
            LC_ALL => 'test'
        ], Locale::normalize('test'));

        // invalid argument
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Locale must be string or array');
        Locale::normalize(123);
    }

    /**
     * @covers ::set
     */
    public function testSetString()
    {
        $this->assertSame('C', setlocale(LC_ALL, '0'));

        Locale::set('de_DE.' . $this->localeSuffix);
        $this->assertSame('de_DE.' . $this->localeSuffix, setlocale(LC_ALL, '0'));
    }

    /**
     * @covers ::set
     */
    public function testSetArray1()
    {
        $this->assertSame('C', setlocale(LC_ALL, '0'));

        Locale::set([
            'LC_ALL'   => 'de_AT.' . $this->localeSuffix,
            'LC_CTYPE' => 'de_DE.' . $this->localeSuffix,
            LC_NUMERIC => 'de_CH.' . $this->localeSuffix
        ]);
        $this->assertSame('de_DE.' . $this->localeSuffix, setlocale(LC_CTYPE, '0'));
        $this->assertSame('de_CH.' . $this->localeSuffix, setlocale(LC_NUMERIC, '0'));
        $this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_COLLATE, '0'));
    }

    /**
     * @covers ::set
     */
    public function testSetArray2()
    {
        $this->assertSame('C', setlocale(LC_ALL, '0'));

        Locale::set([
            'LC_CTYPE' => 'de_DE.' . $this->localeSuffix,
            LC_NUMERIC => 'de_CH.' . $this->localeSuffix,
            'LC_ALL'   => 'de_AT.' . $this->localeSuffix
        ]);
        $this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_CTYPE, '0'));
        $this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_NUMERIC, '0'));
        $this->assertSame('de_AT.' . $this->localeSuffix, setlocale(LC_COLLATE, '0'));
    }
}

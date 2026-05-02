<?php

namespace Sportic\Omniresult\MyraceGr\Tests\Utility;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sportic\Omniresult\MyraceGr\Utility\CategoryParse;

class CategoryParseTest extends TestCase
{
    /**
     * @param $string
     * @param $expected
     * @return void
     */
    #[DataProvider('provider_parse')]
    public function test_parse($string, $expected)
    {
        self::assertSame($expected, CategoryParse::parse($string));
    }

    public static function provider_parse(): array
    {
        return [
            ['', []],
            ['Male', ['gender' => 'Male']],
            ['Female', ['gender' => 'Female']],
            ['Mixed', ['gender' => '']],
            ['Male - 1981', ['gender' => 'male', 'yob'=> '1981']],
            ['Male - 1994 - ROU', ['gender' => 'male', 'yob'=> '1994', 'country' => 'ROU']],
            ['Male - 2005 (M)', ['gender' => 'male', 'yob'=> '2005', 'category' => 'M']],
            ['Male - 2005 (M) - ROU', ['gender' => 'male', 'yob'=> '2005', 'category' => 'M', 'country' => 'ROU']],
            ['Female - 1995 (F) - ROU', ['gender' => 'female', 'yob'=> '1995', 'category' => 'F', 'country' => 'ROU']],
        ];
    }
}

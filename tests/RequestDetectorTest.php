<?php

namespace Sportic\Omniresult\MyraceGr\Tests;

use Sportic\Omniresult\MyraceGr\RequestDetector;

/**
 * Class RequestDetectorTest
 * @package Sportic\Omniresult\MyraceGr\Tests
 */
class RequestDetectorTest extends AbstractTest
{
    /**
     * @param string $url
     * @param bool $valid
     * @param string $action
     * @param array $params
     * @dataProvider detectProvider
     */
    public function testDetect($url, $valid, $action, $params)
    {
        $result = RequestDetector::detect($url);

        self::assertSame($valid, $result->isValid());
        self::assertSame($action, $result->getAction());
        self::assertSame($params, $result->getParams());
    }

    /**
     * @return array
     */
    public function detectProvider()
    {
        return [
            [
                'https://www.myrace.gr/en/event/5896/results.html',
                true,
                'event',
                ['eventId' => '5896']
            ],
            [
                'https://www.myrace.gr/en/race/7654/results.html',
                true,
                'results',
                ['raceId' => '7654']
            ],
            [
                'https://www.myrace.gr/en/bibcard/2199045/results.html',
                true,
                'result',
                ['bibcardId' => '2199045']
            ],
        ];
    }
}

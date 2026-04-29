<?php

namespace Sportic\Omniresult\MyraceGr\Tests\Parsers;

use Sportic\Omniresult\Common\Models\Race;
use Sportic\Omniresult\MyraceGr\Scrapers\EventPage as PageScraper;
use Sportic\Omniresult\MyraceGr\Parsers\EventPage as PageParser;

/**
 * Class EventPageTest
 * @package Sportic\Omniresult\MyraceGr\Tests\Parsers
 */
class EventPageTest extends AbstractPageTest
{
    public function testGenerateContentRaces()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['eventId' => '5896']);

        $parametersParsed = static::initParserFromFixtures(
            new PageParser(),
            $scraper,
            'EventPage/event_page'
        );

        /** @var Race[] $races */
        $races = $parametersParsed->getRecords();

        self::assertCount(3, $races);
        self::assertInstanceOf(Race::class, $races[0]);

        self::assertSame('7654', $races[0]->getId());
        self::assertSame('21.1 km Half Marathon', $races[0]->getName());

        self::assertSame('7655', $races[1]->getId());
        self::assertSame('10 km Run', $races[1]->getName());

        self::assertSame('7656', $races[2]->getId());
        self::assertSame('5 km Run', $races[2]->getName());
    }
}

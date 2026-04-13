<?php

namespace Sportic\Omniresult\MyraceGr\Tests\Parsers;

use Sportic\Omniresult\Common\Models\Result;
use Sportic\Omniresult\MyraceGr\Scrapers\ResultsPage as PageScraper;
use Sportic\Omniresult\MyraceGr\Parsers\ResultsPage as PageParser;

/**
 * Class ResultsPageTest
 * @package Sportic\Omniresult\MyraceGr\Tests\Parsers
 */
class ResultsPageTest extends AbstractPageTest
{
    public function testGenerateContentResults()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['raceId' => '7654']);

        $parametersParsed = static::initParserFromFixturesJson(
            new PageParser(),
            $scraper,
            'ResultsPage/race_page'
        );

        /** @var Result[] $results */
        $results = $parametersParsed->getRecords();

        self::assertCount(10, $results);
        self::assertInstanceOf(Result::class, $results[0]);

        // Verify first result
        $firstResult = $results[0];
        self::assertEquals('1', $firstResult->getPosGen());
        self::assertSame('3211', $firstResult->getBib());
        self::assertSame('2199045', $firstResult->getId());
        self::assertSame('POP DRAGOS LUCA', $firstResult->getFullName());
        self::assertSame('male', $firstResult->getGender());
        self::assertSame('M', $firstResult->getCategory());
        self::assertSame('ROU', $firstResult->getCountry());
        self::assertSame('01:05:43', $firstResult->getTime());

        // Verify splits
        $splits = $firstResult->getSplits();
        self::assertCount(5, $splits);

        // Verify second result
        $secondResult = $results[1];
        self::assertEquals('2', $secondResult->getPosGen());
        self::assertSame('2603', $secondResult->getBib());
        self::assertSame('BARMPAGIANNIS ATHANASIOS', $secondResult->getFullName());
        self::assertEquals('1', $secondResult->getPosCategory());
        self::assertEquals('2', $secondResult->getPosGender());
    }

    public function testGenerateContentPagination()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['raceId' => '7654']);

        $parametersParsed = static::initParserFromFixturesJson(
            new PageParser(),
            $scraper,
            'ResultsPage/race_page'
        );

        $pagination = $parametersParsed['pagination'];

        self::assertSame(2068, $pagination['total']);
        self::assertSame(2068, $pagination['filtered']);
    }
}

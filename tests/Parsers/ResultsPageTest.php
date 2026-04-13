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

    public function testGenerateContentFemaleAthlete()
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

        // Index 6 is the first female in the fixture (AA=7)
        $femaleResult = $results[6];

        self::assertSame('PAPADOPOULOU MARIA', $femaleResult->getFullName());
        self::assertSame('1050', $femaleResult->getBib());
        self::assertSame('2200100', $femaleResult->getId());
        self::assertSame('female', $femaleResult->getGender());
        self::assertSame('F35', $femaleResult->getCategory());
        self::assertSame('GRC', $femaleResult->getCountry());
        self::assertEquals('7', $femaleResult->getPosGen());
        self::assertEquals('1', $femaleResult->getPosGender());
        self::assertEquals('1', $femaleResult->getPosCategory());
        self::assertSame('01:18:22', $femaleResult->getTime());
    }

    public function testScraperUrlDefaults()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['raceId' => '7654']);

        self::assertSame(1, $scraper->getPage());
        self::assertSame(50, $scraper->getPerPage());
        self::assertSame(0, $scraper->getDisplayStart());
        self::assertSame(50, $scraper->getDisplayLength());
        self::assertStringContainsString('iDisplayStart=0', $scraper->getCrawlerUri());
        self::assertStringContainsString('iDisplayLength=50', $scraper->getCrawlerUri());
    }

    public function testScraperUrlWithPageAndPerPage()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['raceId' => '7654', 'page' => 3, 'perPage' => 20]);

        self::assertSame(3, $scraper->getPage());
        self::assertSame(20, $scraper->getPerPage());
        self::assertSame(40, $scraper->getDisplayStart()); // (3-1)*20
        self::assertSame(20, $scraper->getDisplayLength());
        self::assertStringContainsString('iDisplayStart=40', $scraper->getCrawlerUri());
        self::assertStringContainsString('iDisplayLength=20', $scraper->getCrawlerUri());
    }

    public function testScraperUrlBackwardCompatDisplayLength()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['raceId' => '7654', 'displayLength' => 100]);

        self::assertSame(100, $scraper->getPerPage());
        self::assertSame(100, $scraper->getDisplayLength());
        self::assertStringContainsString('iDisplayLength=100', $scraper->getCrawlerUri());
    }

    public function testScraperUrlBackwardCompatDisplayStart()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['raceId' => '7654', 'displayStart' => 200, 'displayLength' => 100]);

        self::assertSame(200, $scraper->getDisplayStart());
        self::assertStringContainsString('iDisplayStart=200', $scraper->getCrawlerUri());
    }

    public function testPaginationIncludesPageInfo()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['raceId' => '7654']);

        $parametersParsed = static::initParserFromFixturesJson(
            new PageParser(),
            $scraper,
            'ResultsPage/race_page'
        );

        $pagination = $parametersParsed['pagination'];

        self::assertSame(1, $pagination['page']);
        self::assertSame(50, $pagination['perPage']);
        self::assertSame(42, $pagination['pages']); // ceil(2068/50) = 42
    }

    public function testPaginationWithCustomPerPage()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['raceId' => '7654', 'perPage' => 10]);

        $parametersParsed = static::initParserFromFixturesJson(
            new PageParser(),
            $scraper,
            'ResultsPage/race_page'
        );

        $pagination = $parametersParsed['pagination'];

        self::assertSame(1, $pagination['page']);
        self::assertSame(10, $pagination['perPage']);
        self::assertSame(207, $pagination['pages']); // ceil(2068/10) = 207
    }

    public function testPaginationWithCustomPage()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['raceId' => '7654', 'page' => 5, 'perPage' => 25]);

        $parametersParsed = static::initParserFromFixturesJson(
            new PageParser(),
            $scraper,
            'ResultsPage/race_page'
        );

        $pagination = $parametersParsed['pagination'];

        self::assertSame(5, $pagination['page']);
        self::assertSame(25, $pagination['perPage']);
        self::assertSame(83, $pagination['pages']); // ceil(2068/25) = 83 (82.72 → 83)
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

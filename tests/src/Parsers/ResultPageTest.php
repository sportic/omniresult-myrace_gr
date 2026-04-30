<?php

namespace Sportic\Omniresult\MyraceGr\Tests\Parsers;

use Sportic\Omniresult\Common\Models\Result;
use Sportic\Omniresult\MyraceGr\Scrapers\ResultPage as PageScraper;
use Sportic\Omniresult\MyraceGr\Parsers\ResultPage as PageParser;

/**
 * Class ResultPageTest
 * @package Sportic\Omniresult\MyraceGr\Tests\Parsers
 */
class ResultPageTest extends AbstractPageTest
{
    public function testGenerateContentResult()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['bibcardId' => '2199045']);

        $parametersParsed = static::initParserFromFixtures(
            new PageParser(),
            $scraper,
            'ResultPage/result_page'
        );

        /** @var Result $result */
        $result = $parametersParsed->getRecord();

        self::assertInstanceOf(Result::class, $result);
        self::assertSame('POP DRAGOS LUCA', $result->getFullName());
        self::assertSame('3211', $result->getBib());
        self::assertSame('male', $result->getGender());
        self::assertSame('M', $result->getCategory());
        self::assertSame('ROU', $result->getCountry());
        self::assertEquals('1', $result->getPosGen());
        self::assertSame('01:05:43', $result->getTime());
    }

    public function testGenerateContentResultSplits()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['bibcardId' => '2199045']);

        $parametersParsed = static::initParserFromFixtures(
            new PageParser(),
            $scraper,
            'ResultPage/result_page'
        );

        /** @var Result $result */
        $result = $parametersParsed->getRecord();

        $splits = $result->getSplits();
        self::assertCount(6, $splits);
    }

    public function testGenerateContentNewResult()
    {
        $scraper = new PageScraper();
        $scraper->initialize(['bibcardId' => '1590509']);

        $parametersParsed = static::initParserFromFixtures(
            new PageParser(),
            $scraper,
            'ResultPage/new_result_page'
        );

        /** @var Result $result */
        $result = $parametersParsed->getRecord();

        self::assertInstanceOf(Result::class, $result);
        self::assertSame('VLAD ALEXANDRU BOBEI', $result->getFullName());
        self::assertSame('8328', $result->getBib());
        self::assertSame('male', $result->getGender());
        self::assertSame('Romania (ROU)', $result->getCountry());
        self::assertEquals('303', $result->getPosGen());
        self::assertSame('00:52:09', $result->getTime());
        self::assertSame('00:52:50', $result->getTimeGross());

        $splits = $result->getSplits();
        self::assertCount(4, $splits);
        self::assertSame('Start', $splits[0]->getName());
        self::assertSame('00:00:00', $splits[0]->getTime());
        self::assertSame('00:00:41', $splits[0]->getTimeGross());
        self::assertSame('Finish', $splits[3]->getName());
        self::assertSame('00:52:09', $splits[3]->getTime());
        self::assertSame('00:52:50', $splits[3]->getTimeGross());
    }
}

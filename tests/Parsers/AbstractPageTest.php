<?php

namespace Sportic\Omniresult\MyraceGr\Tests\Parsers;

use Sportic\Omniresult\Common\Content\GenericContent;
use Sportic\Omniresult\Common\Content\ListContent;
use Sportic\Omniresult\Common\Content\RecordContent;
use Sportic\Omniresult\MyraceGr\Parsers\AbstractParser;
use Sportic\Omniresult\MyraceGr\Scrapers\AbstractScraper;
use Sportic\Omniresult\MyraceGr\Tests\AbstractTest;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class AbstractPageTest
 * @package Sportic\Omniresult\MyraceGr\Tests\Parsers
 */
abstract class AbstractPageTest extends AbstractTest
{
    /**
     * @param AbstractParser $parser
     * @param AbstractScraper $scraper
     * @param string $fixturePath
     * @return GenericContent|ListContent|RecordContent
     */
    public static function initParserFromFixtures($parser, $scraper, $fixturePath)
    {
        $crawler = new Crawler(null, $scraper->getCrawlerUri());
        $crawler->addContent(
            file_get_contents(
                TEST_FIXTURE_PATH . DS . 'Parsers' . DS . $fixturePath . '.html'
            ),
            'text/html;charset=utf-8'
        );

        $parser->setScraper($scraper);
        $parser->setCrawler($crawler);

        return $parser->getContent();
    }

    /**
     * @param AbstractParser $parser
     * @param AbstractScraper $scraper
     * @param string $fixturePath
     * @return GenericContent|ListContent|RecordContent
     */
    public static function initParserFromFixturesJson($parser, $scraper, $fixturePath)
    {
        $response = new Response(
            file_get_contents(
                TEST_FIXTURE_PATH . DS . 'Parsers' . DS . $fixturePath . '.json'
            )
        );

        $parser->initialize(['response' => $response]);

        return $parser->getContent();
    }
}

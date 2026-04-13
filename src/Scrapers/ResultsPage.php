<?php

namespace Sportic\Omniresult\MyraceGr\Scrapers;

use Sportic\Omniresult\MyraceGr\Parsers\ResultsPage as Parser;

/**
 * Class ResultsPage
 * @package Sportic\Omniresult\MyraceGr\Scrapers
 *
 * @method Parser execute()
 */
class ResultsPage extends AbstractScraper
{
    /**
     * @return mixed
     */
    public function getRaceId()
    {
        return $this->getParameter('raceId');
    }

    /**
     * @return int
     */
    public function getDisplayStart()
    {
        return $this->getParameter('displayStart', 0);
    }

    /**
     * @return int
     */
    public function getDisplayLength()
    {
        return $this->getParameter('displayLength', 10000);
    }

    /**
     * @inheritdoc
     */
    protected function generateCrawler()
    {
        $client = $this->getClient();
        $crawler = $client->request(
            'GET',
            $this->getCrawlerUri()
        );

        return $crawler;
    }

    /**
     * @return array
     */
    protected function generateParserData()
    {
        $this->getRequest();

        return [
            'response' => $this->getClient()->getResponse(),
        ];
    }

    /**
     * @return string
     */
    public function getCrawlerUri()
    {
        return $this->getCrawlerUriHost()
            . '/ajax/jsonCacheResultsarch.aspx'
            . '?fr=' . $this->getRaceId()
            . '&fg=-1&fa=-1&fn=-1&fch=-1&fc=-1&fgr=-1'
            . '&lang=2'
            . '&sEcho=1'
            . '&iDisplayStart=' . $this->getDisplayStart()
            . '&iDisplayLength=' . $this->getDisplayLength();
    }
}

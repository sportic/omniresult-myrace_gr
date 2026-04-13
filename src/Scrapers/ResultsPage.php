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
    public function getPage()
    {
        return (int)$this->getParameter('page', 1);
    }

    /**
     * Results per page passed to the myrace.gr API as iDisplayLength.
     * Respects explicit 'perPage' parameter, falls back to legacy 'displayLength',
     * then defaults to 50.
     *
     * @return int
     */
    public function getPerPage()
    {
        $perPage = $this->getParameter('perPage');
        if ($perPage !== null) {
            return (int)$perPage;
        }
        $displayLength = $this->getParameter('displayLength');
        if ($displayLength !== null) {
            return (int)$displayLength;
        }
        return 50;
    }

    /**
     * @return int
     */
    public function getDisplayStart()
    {
        $explicit = $this->getParameter('displayStart');
        if ($explicit !== null) {
            return (int)$explicit;
        }
        return ($this->getPage() - 1) * $this->getPerPage();
    }

    /**
     * @return int
     */
    public function getDisplayLength()
    {
        return $this->getPerPage();
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
            'page'     => $this->getPage(),
            'perPage'  => $this->getPerPage(),
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

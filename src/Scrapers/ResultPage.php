<?php

namespace Sportic\Omniresult\MyraceGr\Scrapers;

use Sportic\Omniresult\MyraceGr\Parsers\ResultPage as Parser;

/**
 * Class ResultPage
 * @package Sportic\Omniresult\MyraceGr\Scrapers
 *
 * @method Parser execute()
 */
class ResultPage extends AbstractScraper
{
    /**
     * @return mixed
     */
    public function getBibcardId()
    {
        return $this->getParameter('bibcardId');
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
     * @return string
     */
    public function getCrawlerUri()
    {
        return $this->getCrawlerUriHost()
            . '/en/bibcard/'
            . $this->getBibcardId()
            . '/results.html';
    }
}

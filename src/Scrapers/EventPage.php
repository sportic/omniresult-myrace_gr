<?php

namespace Sportic\Omniresult\MyraceGr\Scrapers;

use Sportic\Omniresult\MyraceGr\Parsers\EventPage as Parser;

/**
 * Class EventPage
 * @package Sportic\Omniresult\MyraceGr\Scrapers
 *
 * @method Parser execute()
 */
class EventPage extends AbstractScraper
{
    /**
     * @return mixed
     */
    public function getEventId()
    {
        return $this->getParameter('eventId');
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
            . '/en/event/'
            . $this->getEventId()
            . '/results.html';
    }
}

<?php

namespace Sportic\Omniresult\MyraceGr\Scrapers;

/**
 * Class AbstractScraper
 * @package Sportic\Omniresult\MyraceGr\Scrapers
 */
abstract class AbstractScraper extends \Sportic\Omniresult\Common\Scrapers\AbstractScraper
{
    /**
     * @return string
     */
    abstract public function getCrawlerUri();

    /**
     * @return string
     */
    protected function getCrawlerUriHost()
    {
        return 'https://www.myrace.gr';
    }
}

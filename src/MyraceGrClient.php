<?php

namespace Sportic\Omniresult\MyraceGr;

use Sportic\Omniresult\Common\RequestDetector\HasDetectorTrait;
use Sportic\Omniresult\Common\TimingClient;
use Sportic\Omniresult\MyraceGr\Scrapers\EventPage;
use Sportic\Omniresult\MyraceGr\Scrapers\ResultPage;
use Sportic\Omniresult\MyraceGr\Scrapers\ResultsPage;

/**
 * Class MyraceGrClient
 * @package Sportic\Omniresult\MyraceGr
 */
class MyraceGrClient extends TimingClient
{
    use HasDetectorTrait;

    /**
     * @param $parameters
     * @return \Sportic\Omniresult\Common\Parsers\AbstractParser|Parsers\EventPage
     */
    public function event($parameters)
    {
        return $this->executeScrapper(EventPage::class, $parameters);
    }

    /**
     * @param $parameters
     * @return \Sportic\Omniresult\Common\Parsers\AbstractParser|Parsers\ResultsPage
     */
    public function results($parameters)
    {
        return $this->executeScrapper(ResultsPage::class, $parameters);
    }

    /**
     * @param $parameters
     * @return \Sportic\Omniresult\Common\Parsers\AbstractParser|Parsers\ResultPage
     */
    public function result($parameters)
    {
        return $this->executeScrapper(ResultPage::class, $parameters);
    }
}

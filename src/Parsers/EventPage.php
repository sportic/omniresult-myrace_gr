<?php

namespace Sportic\Omniresult\MyraceGr\Parsers;

use Sportic\Omniresult\Common\Content\ListContent;
use Sportic\Omniresult\Common\Models\Race;

/**
 * Class EventPage
 * @package Sportic\Omniresult\MyraceGr\Parsers
 */
class EventPage extends AbstractParser
{
    /**
     * @return array
     */
    protected function generateContent()
    {
        $races = $this->parseRaces();
        return ['records' => $races];
    }

    /**
     * @return Race[]
     */
    protected function parseRaces()
    {
        $return = [];
        $links = $this->getCrawler()->filterXPath('//a[@href]');

        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $raceId = $this->parseRaceIdFromHref($href);
            if ($raceId !== null) {
                $parameters = [
                    'id' => $raceId,
                    'name' => trim($link->textContent),
                    'href' => $href,
                ];
                $return[] = new Race($parameters);
            }
        }

        return $return;
    }

    /**
     * @param string $href
     * @return string|null
     */
    protected function parseRaceIdFromHref($href)
    {
        if (preg_match('#/race/(\d+)/results\.html#i', $href, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     * @inheritdoc
     */
    protected function getContentClassName()
    {
        return ListContent::class;
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     * @inheritdoc
     */
    public function getModelClassName()
    {
        return Race::class;
    }
}

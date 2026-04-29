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
        $panels = $this->getCrawler()->filterXPath(
            '//section[contains(@class,"events")]'
            . '//*[contains(@class,"panel-group")]'
            . '//*[contains(@class,"event")]'
        );

        foreach ($panels as $panel) {
            $panelCrawler = new \Symfony\Component\DomCrawler\Crawler($panel);

            $nameNode = $panelCrawler->filterXPath(
                './/a[contains(@class,"accordion-head")]//h3'
            );
            $linkNode = $panelCrawler->filterXPath(
                './/a[contains(@class,"btn-theme")]'
            );

            if ($nameNode->count() === 0 || $linkNode->count() === 0) {
                continue;
            }

            $href = $linkNode->attr('href');
            $raceId = $this->parseRaceIdFromHref($href);
            if ($raceId === null) {
                continue;
            }

            $return[] = new Race([
                'id' => $raceId,
                'name' => trim($nameNode->text()),
                'href' => $href,
            ]);
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

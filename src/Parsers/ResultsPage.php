<?php

namespace Sportic\Omniresult\MyraceGr\Parsers;

use Sportic\Omniresult\Common\Content\ListContent;
use Sportic\Omniresult\Common\Models\Result;
use Sportic\Omniresult\Common\Models\Split;
use Sportic\Omniresult\Common\Models\SplitCollection;

/**
 * Class ResultsPage
 * @package Sportic\Omniresult\MyraceGr\Parsers
 */
class ResultsPage extends AbstractParser
{
    /**
     * @return array
     */
    protected function generateContent()
    {
        $data = $this->getJsonData();
        $results = $this->parseResults($data);

        return [
            'records' => $results,
            'pagination' => $this->parsePagination($data),
        ];
    }

    /**
     * @param array $data
     * @return Result[]
     */
    protected function parseResults(array $data)
    {
        $return = [];
        $rows = isset($data['aaData']) ? $data['aaData'] : [];
        foreach ($rows as $row) {
            $result = $this->parseResult($row);
            if ($result instanceof Result) {
                $return[] = $result;
            }
        }
        return $return;
    }

    /**
     * @param array $row
     * @return Result
     */
    protected function parseResult(array $row)
    {
        $parameters = [];

        $parameters['posGen'] = isset($row['ranking']) ? trim($row['ranking']) : null;
        $parameters['posCategory'] = isset($row['agegroupranking']) ? trim($row['agegroupranking']) : null;
        $parameters['posGender'] = isset($row['genderranking']) ? trim($row['genderranking']) : null;

        if (isset($row['bib'])) {
            $this->parseBib($row['bib'], $parameters);
        }

        if (isset($row['lastname'])) {
            $this->parseLastname($row['lastname'], $parameters);
        }

        $parameters['splits'] = $this->parseSplits($row);
        $parameters['time'] = $this->parseFinishTime($row);

        return new Result($parameters);
    }

    /**
     * Parse BIB field (contains HTML with link)
     *
     * @param string $bibHtml
     * @param array $parameters
     */
    protected function parseBib($bibHtml, array &$parameters)
    {
        // Extract BIB number: <a href="/en/bibcard/2199045/results.html">3211</a>
        if (preg_match('#<a[^>]*href="([^"]*)"[^>]*>(\d+)</a>#i', $bibHtml, $matches)) {
            $parameters['bib'] = trim($matches[2]);
            $parameters['id'] = $this->parseBibcardId($matches[1]);
        } else {
            $parameters['bib'] = trim(strip_tags($bibHtml));
        }
    }

    /**
     * Extract bibcard ID from href
     *
     * @param string $href
     * @return string|null
     */
    protected function parseBibcardId($href)
    {
        if (preg_match('#/bibcard/(\d+)/results\.html#i', $href, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Parse lastname field (contains HTML with name, gender, birth year, nationality)
     *
     * @param string $lastnameHtml
     * @param array $parameters
     */
    protected function parseLastname($lastnameHtml, array &$parameters)
    {
        // Extract full name from first <a> tag in the bold div
        if (preg_match('#<a[^>]*href="/en/bibcard/[^"]*"[^>]*>(.*?)</a>#is', $lastnameHtml, $matches)) {
            $parameters['fullName'] = trim(strip_tags($matches[1]));
        }

        // Extract gender, birth year, category, nationality from second div
        // Format: "Male - 2005 (M) - ROU" or "Male - 1987 (M35) - GRC"
        if (preg_match(
            '#(Male|Female)\s*-\s*(\d{4})\s*\(([^)]+)\)\s*-\s*([A-Z]{2,3})#i',
            $lastnameHtml,
            $matches
        )) {
            $parameters['gender'] = strtolower(trim($matches[1]));
            $parameters['category'] = trim($matches[3]);
            $parameters['country'] = trim($matches[4]);
        }
    }

    /**
     * Parse split times from result row
     *
     * @param array $row
     * @return SplitCollection
     */
    protected function parseSplits(array $row)
    {
        $splits = new SplitCollection();
        $splitIndex = 1;

        while (isset($row['res_split' . $splitIndex])) {
            $splitHtml = $row['res_split' . $splitIndex];
            $time = $this->extractTimeFromHtml($splitHtml);
            if ($time !== null) {
                $splits->add(new Split(['name' => 'split' . $splitIndex, 'time' => $time]));
            }
            $splitIndex++;
        }

        return $splits;
    }

    /**
     * Parse finish time from result row
     *
     * @param array $row
     * @return string|null
     */
    protected function parseFinishTime(array $row)
    {
        if (!isset($row['res_finish'])) {
            return null;
        }
        return $this->extractTimeFromHtml($row['res_finish']);
    }

    /**
     * Extract time value from HTML (takes value from bold div = gun time)
     *
     * @param string $html
     * @return string|null
     */
    protected function extractTimeFromHtml($html)
    {
        // Extract time from bold div: <div style="font-weight:bold">00:04:01</div>
        if (preg_match('#<div[^>]*font-weight:bold[^>]*>([\d:]+)</div>#i', $html, $matches)) {
            return trim($matches[1]);
        }
        // Fallback: strip all tags
        $text = trim(strip_tags($html));
        if (preg_match('#^[\d:]+$#', $text)) {
            return $text;
        }
        return null;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function parsePagination(array $data)
    {
        $total    = isset($data['iTotalRecords']) ? (int)$data['iTotalRecords'] : 0;
        $filtered = isset($data['iTotalDisplayRecords']) ? (int)$data['iTotalDisplayRecords'] : 0;

        $page    = 1;
        $perPage = 50;

        $scraper = $this->getScraper();
        if ($scraper !== null && method_exists($scraper, 'getPage')) {
            $page    = $scraper->getPage();
            $perPage = $scraper->getPerPage();
        }

        $pages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        return [
            'items'    => $total,
            'filtered' => $filtered,
            'current'     => $page,
            'perPage'  => $perPage,
            'pages'    => $pages,
        ];
    }

    /**
     * @return array
     */
    protected function getJsonData()
    {
        $content = $this->getResponse()->getContent();
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
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
        return Result::class;
    }
}

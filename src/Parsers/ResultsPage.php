<?php

namespace Sportic\Omniresult\MyraceGr\Parsers;

use Nip\Utility\Str;
use Sportic\Omniresult\Common\Content\ListContent;
use Sportic\Omniresult\Common\Models\Result;
use Sportic\Omniresult\Common\Models\Split;
use Sportic\Omniresult\Common\Models\SplitCollection;
use Sportic\Omniresult\MyraceGr\Utility\CategoryParse;

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
        $this->parseFinishTime($row, $parameters);

        $parameters['category'] = !empty($parameters['category']) ?: $parameters['gender'];
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

        $secondDiv = Str::after($lastnameHtml, '</div>');
        $secondDiv = strip_tags($secondDiv);
        $parameters = array_merge($parameters, CategoryParse::parse($secondDiv));
    }

    /**
     * Parse split times from result row.
     * Finds all keys starting with "res_" (excluding "res_finish"), uses the
     * suffix as the split name, and extracts up to two times per cell:
     * the first (bold) is time_gross, the second is time.
     *
     * @param array $row
     * @return SplitCollection
     */
    protected function parseSplits(array $row)
    {
        $splits = new SplitCollection();

        foreach (array_keys($row) as $key) {
            if (strncmp($key, 'res_', 4) !== 0 || $key === 'res_finish') {
                continue;
            }

            $times = $this->extractTimesFromHtml($row[$key]);
            if (empty($times)) {
                continue;
            }

            $name = substr($key, 4);
            $splitParams = ['name' => $name];
            if (count($times) >= 2) {
                $splitParams['timeGross'] = $times[0];
                $splitParams['time'] = $times[1];
            } else {
                $splitParams['time'] = $times[0];
            }

            $splits->add(new Split($splitParams));
        }

        return $splits;
    }

    /**
     * Parse finish time from result row and populate time / timeGross.
     * The first (bold) value is time_gross; the second value is time (net).
     *
     * @param array $row
     * @param array $parameters
     */
    protected function parseFinishTime(array $row, array &$parameters)
    {
        if (!isset($row['res_finish'])) {
            return;
        }

        $times = $this->extractTimesFromHtml($row['res_finish']);
        if (empty($times)) {
            return;
        }

        if (count($times) >= 2) {
            $parameters['timeGross'] = $times[0];
            $parameters['time'] = $times[1];
        } else {
            $parameters['time'] = $times[0];
        }
    }

    /**
     * Extract all time values found inside div tags in the given HTML snippet.
     * Returns them in document order; the first entry (bold div) is the gun /
     * gross time, the second entry (plain div) is the net time.
     *
     * @param string $html
     * @return string[]
     */
    protected function extractTimesFromHtml($html)
    {
        preg_match_all('#<div[^>]*>([\d:]+)</div>#i', $html, $matches);
        $times = [];
        foreach ($matches[1] as $t) {
            $t = trim($t);
            if (preg_match('#^\d{1,2}:\d{2}(:\d{2})?$#', $t)) {
                $times[] = $t;
            }
        }
        return $times;
    }

    /**
     * Extract the first time value from HTML (backward-compatible helper).
     *
     * @param string $html
     * @return string|null
     */
    protected function extractTimeFromHtml($html)
    {
        $times = $this->extractTimesFromHtml($html);
        return $times[0] ?? null;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function parsePagination(array $data)
    {
        $total = isset($data['iTotalRecords']) ? (int)$data['iTotalRecords'] : 0;
        $filtered = isset($data['iTotalDisplayRecords']) ? (int)$data['iTotalDisplayRecords'] : 0;

        $scraper = $this->getScraper();
        if ($scraper !== null && method_exists($scraper, 'getPage')) {
            $page = $scraper->getPage();
            $perPage = $scraper->getPerPage();
        }

        $page = $page > 0 ? $page : 1;
        $perPage = $perPage ?? 50;

        $pages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        return [
            'items' => $total,
            'current' => $page,
            'all' => $pages,
            'filtered' => $filtered,
            'perPage' => $perPage,
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

<?php

namespace Sportic\Omniresult\MyraceGr\Parsers;

use Sportic\Omniresult\Common\Content\RecordContent;
use Sportic\Omniresult\Common\Models\Result;
use Sportic\Omniresult\Common\Models\Split;
use Sportic\Omniresult\Common\Models\SplitCollection;

/**
 * Class ResultPage
 * @package Sportic\Omniresult\MyraceGr\Parsers
 */
class ResultPage extends AbstractParser
{
    /**
     * @return array
     */
    protected function generateContent()
    {
        $parameters = $this->parseContent();
        return [
            RecordContent::KEY_SOURCE_URI => $this->getScraper()->getCrawlerUri(),
            'record' => new Result($parameters)
        ];
    }

    /**
     * @return array
     */
    protected function parseContent()
    {
        $parameters = [];
        $this->parseResultTable($parameters);
        $this->parseSplitsTable($parameters);
        return $parameters;
    }

    /**
     * Parse the main result details table
     *
     * @param array $parameters
     */
    protected function parseResultTable(array &$parameters)
    {
        $rows = $this->getCrawler()->filterXPath(
            '//table[contains(@class,"table-results")]//tr | //table//tr'
        );

        $labelMap = static::getLabelMaps();

        $firstName = null;
        $lastName = null;

        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length >= 2) {
                $label = trim(strip_tags($cells->item(0)->textContent));
                $value = trim(strip_tags($cells->item(1)->textContent));

                if ($label === 'Surname:') {
                    $lastName = $value;
                    continue;
                }
                if ($label === 'Name:') {
                    $firstName = $value;
                    continue;
                }

                if (isset($labelMap[$label])) {
                    $field = $labelMap[$label];
                    $parameters[$field] = $this->normalizeFieldValue($field, $value);
                }
            }
        }

        if ($firstName || $lastName) {
            $parameters['fullName'] = trim($lastName . ' ' . $firstName);
        }
    }

    /**
     * Normalize a field value based on the field type
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    protected function normalizeFieldValue($field, $value)
    {
        if (strpos($field, 'gender') === 0) {
            $value = strtolower($value);
            return $value === 'm' ? 'male' : ($value === 'f' ? 'female' : $value);
        }
        return $value;
    }

    /**
     * Parse splits table if present
     *
     * @param array $parameters
     */
    protected function parseSplitsTable(array &$parameters)
    {
        $splits = new SplitCollection();
        $splitRows = $this->getCrawler()->filterXPath(
            '//table[contains(@class,"table-splits")]//tr | //table[caption[contains(text(), "RESULTS")]]//tr'
        );

        foreach ($splitRows as $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length >= 2) {
                $name = trim(strip_tags($cells->item(0)->textContent));
                if (empty($name)) {
                    continue;
                }
                $time = trim(strip_tags($cells->item(1)->textContent));
                if ($name && $time) {
                    $splitParameters = ['name' => $name, 'time' => $time];
                    if ($cells->length >= 3) {
                        $timeValue = $time;
                        $timeNetOrPace = trim(strip_tags($cells->item(2)->textContent));
                        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $timeNetOrPace)) {
                            $splitParameters['time'] = $timeNetOrPace;
                            $splitParameters['timeGross'] = $timeValue;
                        }
                    }
                    $splits->add(new Split($splitParameters));
                }
            }
        }

        if (count($splits) > 0) {
            $parameters['splits'] = $splits;
            $splitsArray = $splits->all();
            $lastSplit = end($splitsArray);
            if ($lastSplit->getName() === 'Finish') {
                $parameters['time'] = $lastSplit->getTime();
                $parameters['timeGross'] = $lastSplit->getTimeGross();
            }
        }
    }

    /**
     * @return array
     */
    protected static function getLabelMaps()
    {
        return [
            'Name' => 'fullName',
            'BIB' => 'bib',
            'BIB:' => 'bib',
            'Gender' => 'gender',
            'Sex:' => 'gender',
            'Category' => 'category',
            'Age Group:' => 'category',
            'Nationality' => 'country',
            'Nationality:' => 'country',
            'Position' => 'posGen',
            'General Ranking:' => 'posGen',
            'Position/Gender' => 'posGender',
            'Ranking / Sex:' => 'posGender',
            'Position/Category' => 'posCategory',
            'Time' => 'time',
            'Gun Time' => 'timeGross',
        ];
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     * @inheritdoc
     */
    protected function getContentClassName()
    {
        return RecordContent::class;
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     * @inheritdoc
     */
    public function getModelClassName()
    {
        return Result::class;
    }
}

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

        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length >= 2) {
                $label = trim(strip_tags($cells->item(0)->textContent));
                $value = trim(strip_tags($cells->item(1)->textContent));

                $field = array_search($label, $labelMap);
                if ($field !== false) {
                    $parameters[$field] = $this->normalizeFieldValue($field, $value);
                }
            }
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
        if ($field === 'gender') {
            return strtolower($value);
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
            '//table[contains(@class,"table-splits")]//tr'
        );

        foreach ($splitRows as $row) {
            $cells = $row->getElementsByTagName('td');
            if ($cells->length >= 2) {
                $name = trim(strip_tags($cells->item(0)->textContent));
                $time = trim(strip_tags($cells->item(1)->textContent));
                if ($name && $time) {
                    $splits->add(new Split(['name' => $name, 'time' => $time]));
                }
            }
        }

        if (count($splits) > 0) {
            $parameters['splits'] = $splits;
        }
    }

    /**
     * @return array
     */
    protected static function getLabelMaps()
    {
        return [
            'fullName' => 'Name',
            'bib' => 'BIB',
            'gender' => 'Gender',
            'category' => 'Category',
            'country' => 'Nationality',
            'posGen' => 'Position',
            'posGender' => 'Position/Gender',
            'posCategory' => 'Position/Category',
            'time' => 'Time',
            'timeGross' => 'Gun Time',
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

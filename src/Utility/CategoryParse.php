<?php

namespace Sportic\Omniresult\MyraceGr\Utility;

class CategoryParse
{
    public static function parse($string): array
    {
        $params = [];

        if (empty($string)) {
            return $params;
        }

        // "Mixed" → gender empty
        if (trim($string) === 'Mixed') {
            $params['gender'] = '';
            return $params;
        }

        // Full pattern: "Male - 1994 - ROU"
        if (preg_match(
            '#^(Male|Female)\s*-\s*(\d{4})\s*-\s*([A-Z]{2,3})$#i',
            $string,
            $matches
        )) {
            $params['gender'] = strtolower(trim($matches[1]));
            $params['yob'] = trim($matches[2]);
            $params['country'] = trim($matches[3]);
            return $params;
        }

        // Full pattern: "Male - 2005 (M) - ROU"
        if (preg_match(
            '#^(Male|Female)\s*-\s*(\d{4})\s*\(([^)]+)\)\s*-\s*([A-Z]{2,3})$#i',
            $string,
            $matches
        )) {
            $params['gender'] = strtolower(trim($matches[1]));
            $params['yob'] = trim($matches[2]);
            $params['category'] = trim($matches[3]);
            $params['country'] = trim($matches[4]);
            return $params;
        }

        // Pattern with year and category: "Male - 2005 (M)"
        if (preg_match(
            '#^(Male|Female)\s*-\s*(\d{4})\s*\(([^)]+)\)$#i',
            $string,
            $matches
        )) {
            $params['gender'] = strtolower(trim($matches[1]));
            $params['yob'] = trim($matches[2]);
            $params['category'] = trim($matches[3]);
            return $params;
        }

        // Pattern with year only: "Male - 1981"
        if (preg_match('#^(Male|Female)\s*-\s*(\d{4})$#i', $string, $matches)) {
            $params['gender'] = strtolower(trim($matches[1]));
            $params['yob'] = trim($matches[2]);
            return $params;
        }

        // Standalone gender: "Male" or "Female" (preserve original case)
        if (preg_match('#^(Male|Female)$#i', $string, $matches)) {
            $params['gender'] = trim($matches[1]);
            return $params;
        }

        return $params;
    }
}



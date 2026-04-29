<?php

namespace Sportic\Omniresult\MyraceGr\Utility;

class CategoryParse
{
    public static function parse($string): array
    {
        $params = [];
        // Extract gender, birth year, category, nationality from second div
        // Format: "Male - 2005 (M) - ROU" or "Male - 1987 (M35) - GRC"
        if (preg_match(
            '#(Male|Female)\s*-\s*(\d{4})\s*\(([^)]+)\)\s*-\s*([A-Z]{2,3})#i',
            $string,
            $matches
        )) {
            $params['gender'] = strtolower(trim($matches[1]));
            $params['category'] = trim($matches[3]);
            $params['country'] = trim($matches[4]);
        }

        return $params;
    }
}



<?php

namespace Sportic\Omniresult\MyraceGr;

use Sportic\Omniresult\Common\RequestDetector\AbstractRequestDetector;

/**
 * Class RequestDetector
 * @package Sportic\Omniresult\MyraceGr
 */
class RequestDetector extends AbstractRequestDetector
{
    protected $pathParts = null;

    /**
     * @inheritdoc
     */
    protected function isValidRequest()
    {
        if (in_array(
            $this->getUrlComponent('host'),
            ['www.myrace.gr', 'myrace.gr']
        )) {
            return true;
        }
        return parent::isValidRequest();
    }

    /**
     * @return string
     */
    protected function detectAction()
    {
        $pathParts = $this->getPathParts();

        if (!isset($pathParts[0])) {
            return '';
        }

        switch ($pathParts[0]) {
            case 'event':
                return 'event';
            case 'race':
                return 'results';
            case 'bibcard':
                return 'result';
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    protected function detectParams()
    {
        $pathParts = $this->getPathParts();

        $return = [];

        if (!isset($pathParts[0])) {
            return $return;
        }

        switch ($pathParts[0]) {
            case 'event':
                $return['eventId'] = $pathParts[1] ?? '';
                break;
            case 'race':
                $return['raceId'] = $pathParts[1] ?? '';
                break;
            case 'bibcard':
                $return['bibcardId'] = $pathParts[1] ?? '';
                break;
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getPathParts(): array
    {
        if ($this->pathParts === null) {
            $this->detectUrlPathParts();
        }
        return $this->pathParts;
    }

    protected function detectUrlPathParts()
    {
        $path = strtolower($this->getUrlComponent('path'));
        $path = trim($path, '/');
        $parts = explode('/', $path);

        // Skip language prefix (e.g., "en")
        if (isset($parts[0]) && strlen($parts[0]) === 2 && ctype_alpha($parts[0])) {
            array_shift($parts);
        }

        $this->pathParts = $parts;
    }
}

<?php

namespace TomGud\Service;

use TomGud\Model\HttpCase;
use TomGud\Model\HttpIgnore;
use TomGud\Model\Specification;

/**
 * Class SpecificationParser
 * @package TomGud\Service
 */
class SpecificationParser
{
    /**
     * @var Specification
     */
    private $specification;

    /**
     * @var array
     */
    private $specificationInput;

    /**
     * SpecificationParser constructor.
     * @param array $specification
     */
    public function __construct($specification)
    {
        $this->specification = new Specification();
        $this->specificationInput = $specification;
    }

    /**
     * @return Specification
     */
    public function parse()
    {
        $this->parseBaseUri();
        $this->parseIgnore();
        $this->parseCases();
        return $this->specification;
    }

    private function parseBaseUri()
    {
        if (!isset($this->specificationInput['base_uri']) ||
            !is_array($this->specificationInput['base_uri']) ||
            count($this->specificationInput['base_uri']) !== 2) {
            throw new \RuntimeException('Base URI\'s are not configured or there are not exactly two of them');
        }
        $this->specification->setBaseUri($this->specificationInput['base_uri']);
    }

    private function parseIgnore()
    {
        $ignoreInput = array_key_exists('ignore', $this->specificationInput) ? $this->specificationInput['ignore'] : [];
        $ignore = (new HttpIgnore())
            ->setHeaders(array_key_exists('headers', $ignoreInput) ? $ignoreInput['headers'] : [])
            ->setHtml($ignoreHtml = array_key_exists('http', $ignoreInput) ? $ignoreInput['http'] : false)
            ->setStatusCode($ignoreStatusCode = array_key_exists('status', $ignoreInput) ?
                $ignoreInput['status'] :
                false
            );
        $this->specification->setIgnore($ignore);
    }

    private function parseCases()
    {
        $httpCases = [];
        if (isset($this->specificationInput['cases']) && is_array($this->specificationInput['cases'])) {
            foreach ($this->specificationInput['cases'] as $case) {
                $httpCases[] = (new HttpCase())
                    ->setUri(array_key_exists('path', $case) ? $case['path'] : null)
                    ->setMethod(array_key_exists('method', $case) ? $case['method'] : 'GET')
                    ->setQuery(array_key_exists('query', $case) ? $case['query'] : [])
                    ->setHeaders(array_key_exists('headers', $case) ? $case['headers'] : [])
                    ->setBody(array_key_exists('content', $case) ? $case['content'] : null);
            }
        }
        $this->specification->setCase($httpCases);
    }
}

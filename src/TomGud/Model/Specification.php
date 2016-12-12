<?php

namespace TomGud\Model;

/**
 * Class Specification
 * @package TomGud\Model
 */
class Specification
{
    /**
     * @var string[]
     */
    private $baseUri;

    /**
     * @var HttpIgnore
     */
    private $ignore;

    /**
     * @var HttpCase[]
     */
    private $case;

    /**
     * @return string[]
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * @param string[] $baseUri
     * @return Specification
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
        return $this;
    }

    /**
     * @return HttpIgnore
     */
    public function getIgnore()
    {
        return $this->ignore;
    }

    /**
     * @param HttpIgnore $ignore
     * @return Specification
     */
    public function setIgnore($ignore)
    {
        $this->ignore = $ignore;
        return $this;
    }

    /**
     * @return HttpCase[]
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * @param HttpCase[] $case
     * @return Specification
     */
    public function setCase($case)
    {
        $this->case = $case;
        return $this;
    }
}

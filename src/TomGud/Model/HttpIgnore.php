<?php

namespace TomGud\Model;

/**
 * Class HttpIgnore
 * @package TomGud\Model
 */
class HttpIgnore
{
    /**
     * @var string[]
     */
    private $headers;

    /**
     * @var bool
     */
    private $statusCode;

    /**
     * @var bool
     */
    private $html;

    /**
     * @return string[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param \string[] $headers
     * @return HttpIgnore
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param boolean $statusCode
     * @return HttpIgnore
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isHtml()
    {
        return $this->html;
    }

    /**
     * @param boolean $html
     * @return HttpIgnore
     */
    public function setHtml($html)
    {
        $this->html = $html;
        return $this;
    }
}

<?php

namespace Sb\Browser;

use Sb\Browser\Cache\CacheInterface;
use Sb\Browser\Dom\Dom;
use Sb\Browser\Engine\EngineInterface;

class Browser
{

    /**
     * @var Dom
     */
    private $dom;

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(EngineInterface $engine)
    {
        $this->setEngine($engine);
    }

    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return CacheInterface
     */
    private function getCache()
    {
        return $this->cache;
    }

    private function useCache()
    {
        return $this->cache instanceof CacheInterface;
    }

    public function setBrowserMockOptions()
    {
        $this->getEngine()->setBrowserMockOptions();
    }

    public function setProxy($proxy)
    {
        $this->getEngine()->setProxy($proxy);
    }

    public function setCookiesFileLocation($file)
    {
        $this->getEngine()->setCookiesFileLocation($file);
    }

    public function getRedirects()
    {
        return $this->getEngine()->getRedirects();
    }

    public function getUrl()
    {
        return $this->getEngine()->getUrl();
    }

    public function getHtml()
    {
        return $this->getEngine()->getHtml();
    }

    public function getHttpHeadersResponse()
    {
        return $this->getEngine()->getHttpHeadersResponse();
    }

    public function getDom()
    {
        return $this->dom;
    }

    public function get($url, array $options = [], $referer = null)
    {
        $this->dom = null;

        if ($this->useCache() && $this->getCache()->exist($url)) {
            $html = $this->getCache()->load($url);
            $this->dom = new Dom($html);
            return;
        }

        $this->engine->get($url, $options, $referer);

        $html = $this->engine->getHtml();
        if ($this->useCache()) {
            $this->getCache()->save($url, $html);
        }

        $this->dom = new Dom($html);
    }

    public function post($url, array $params = [], array $options = [], $referer = null)
    {
        $this->dom = null;

        $this->engine->post($url, $params, $options, $referer);

        $this->dom = new Dom($this->engine->getHtml());
    }

    public function submitForm(Form $form, array $options = [], $referer = null)
    {
        $this->dom = null;

        $this->engine->submitForm($form, $options, $referer);

        $this->dom = new Dom($this->engine->getHtml());
    }

    public function trace($includeResponseBody = false)
    {
        $this->getEngine()->trace($includeResponseBody);
    }

}
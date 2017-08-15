<?php

namespace Sb\Browser;

use Psr\Http\Message\UriInterface;
use Sb\Browser\Cache\CacheInterface;
use Sb\Browser\Dom\Dom;
use Sb\Browser\Engine\EngineInterface;
use GuzzleHttp\Psr7;

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

    private $url;

    private $responseHeaders;

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
        return $this->url;
    }

    public function getHtml()
    {
        if (!$this->getDom()) {
            return '';
        }

        return $this->getDom()->getContent();
    }

    public function getHttpHeadersResponse()
    {
        return $this->responseHeaders;
    }

    public function getDom()
    {
        return $this->dom;
    }

    /**
     * @param string|UriInterface $uri
     * @param string|UriInterface $baseUri
     * @return string
     */
    public function resolveUri($uri, $baseUri)
    {
        $formUri = Psr7\uri_for($uri === null ? '' : $uri);
        return (string) Psr7\UriResolver::resolve(Psr7\uri_for($baseUri), $formUri);
    }

    public function get($url, array $options = [], $referer = null)
    {
        $this->dom = null;
        $this->url = null;
        $this->responseHeaders = null;

        if ($this->useCache() && $this->getCache()->exist($url)) {
            $html = $this->getCache()->load($url);
            $this->dom = new Dom($html);
            $this->url = $url;
            $this->responseHeaders = [];
            return;
        }

        $this->engine->get($url, $options, $referer);

        $html = $this->engine->getHtml();
        if ($this->useCache()) {

            $encoding = $this->getEngine()->getEncoding();
            if ($encoding !== 'UTF-8') {
                $html = mb_convert_encoding($html, 'UTF-8', $encoding);
            }

            $this->getCache()->save($url, $html);
        }

        $this->dom = new Dom($html, $this->getEngine()->getEncoding());
        $this->url = $this->getEngine()->getUrl();
        $this->responseHeaders = $this->getEngine()->getHttpHeadersResponse();
    }

    public function post($url, array $params = [], array $options = [], $referer = null)
    {
        $this->dom = null;
        $this->url = null;
        $this->responseHeaders = null;

        $this->engine->post($url, $params, $options, $referer);

        $this->dom = new Dom($this->engine->getHtml(), $this->getEngine()->getEncoding());
        $this->url = $this->getEngine()->getUrl();
        $this->responseHeaders = $this->getEngine()->getHttpHeadersResponse();
    }

    public function submitForm(Form $form, array $options = [], $referer = null)
    {
        $this->dom = null;
        $this->url = null;
        $this->responseHeaders = null;

        $formAction = $this->resolveUri($form->getAction(), $this->getUrl());
        $form->setAction($formAction);

        $this->engine->submitForm($form, $options, $referer);

        $this->dom = new Dom($this->engine->getHtml(), $this->getEngine()->getEncoding());
        $this->url = $this->getEngine()->getUrl();
        $this->responseHeaders = $this->getEngine()->getHttpHeadersResponse();
    }

    public function trace($includeResponseBody = false)
    {
        $this->getEngine()->trace($includeResponseBody);
    }

}
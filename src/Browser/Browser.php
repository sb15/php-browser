<?php

namespace Sb\Browser;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;
use Sb\Browser\Dom\Dom;
use GuzzleHttp\Psr7;

class Browser
{
    private $client;
    private $dom;
    private $url;

    private $preUrl;

    private $redirects = [];

    /**
     * @var Response
     */
    private $response;

    private $options;

    public function __construct($options = [])
    {

        $this->options = $this->mergeOptions([
            RequestOptions::ALLOW_REDIRECTS => [
                'max'             => 5,
                'strict'          => false,
                'referer'         => true,
                'protocols'       => ['http', 'https'],
                'track_redirects' => false
            ],
            RequestOptions::ON_STATS => function (TransferStats $stats) {
                $this->url = (string) $stats->getEffectiveUri();
                $this->redirects[] = (string) $stats->getEffectiveUri();
            }
        ], $options);

        $this->client = new Client($this->options);
    }

    public function setBrowserMockOptions()
    {
        $this->options = $this->mergeOptions($this->options,
        [
            RequestOptions::HEADERS => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Encoding' => 'gzip, deflate',
                'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3'
            ]
        ]);
    }

    public function setProxy($proxy)
    {
        $this->options = $this->mergeOptions($this->options,
        [
            RequestOptions::PROXY => 'tcp://' . $proxy
        ]);
    }

    public function setCookiesFileLocation($file)
    {
        $jar = new FileCookieJar($file);
        $this->options = $this->mergeOptions($this->options, [
            RequestOptions::COOKIES => $jar
        ]);
    }

    public function mergeOptions($options1, $options2)
    {
        $result = $options1;
        foreach ($options2 as $optionKey => $optionValue) {
            if (!array_key_exists($optionKey, $options1)) {
                $result[$optionKey] = $optionValue;
            } else {
                foreach ($optionValue as $optionKey2 => $optionsValue2) {
                    $result[$optionKey][$optionKey2] = $optionsValue2;
                }
            }
        }
        return $result;
    }

    public function setSharedOption()
    {

    }

    /**
     * @param Response|ResponseInterface $response
     */
    public function setLastResponse($response)
    {
        $this->response = $response;
        $this->dom = new Dom($response->getBody());
    }

    public function getLastResponse()
    {
        return $this->response;
    }

    public function clearRedirects()
    {
        $this->redirects = [];
    }

    public function getRedirects()
    {
        return $this->redirects;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getHtml()
    {
        return (string) $this->response->getBody();
    }

    public function getHttpHeadersRequest()
    {

    }

    public function getHttpHeaderResponse()
    {

    }

    public function eventBeforeRequest()
    {
        $this->preUrl = $this->url;
    }

    public function get($url, $options = [])
    {
        $this->preUrl = $this->url;

        $this->clearRedirects();
        $response = $this->client->get($url, $this->getOptionsForCall($options));
        $this->setLastResponse($response);
        return $this->getHtml();
    }

    public function post($url, $options = [])
    {
        $this->preUrl = $this->url;

        $this->clearRedirects();
        $response = $this->client->post($url, $this->getOptionsForCall($options));
        $this->setLastResponse($response);
        return $this->getHtml();
    }

    public function submitForm(Form $form, $options = [])
    {
        $this->preUrl = $this->url;

        $action = $form->resolveUri($this->getUrl());

        $method = $form->getMethod();
        $response = $this->client->request($method, $action, $this->getOptionsForCall($form->getOptions($options)));
        $this->setLastResponse($response);
        return $this->getHtml();
    }

    public function getOptionsForCall($options = [])
    {
        // add referer option, if exist Referer = url

        if ($this->preUrl) {
            $options[RequestOptions::HEADERS]['Referer'] = $this->preUrl;
        }

        return $this->mergeOptions($this->options, $options);
    }

    /**
     * @return Dom
     */
    public function getDom()
    {
        return $this->dom;
    }

}
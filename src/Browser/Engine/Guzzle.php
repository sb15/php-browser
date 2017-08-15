<?php

namespace Sb\Browser\Engine;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;
use Sb\Browser\Engine\Guzzle\Trace;
use Sb\Browser\Form;

class Guzzle implements EngineInterface
{
    private $client;

    /**
     * @var TransferStats[]
     */
    private $stats = [];

    private $options;

    /**
     * @var Response
     */
    private $response;

    public function __construct(array $options = [])
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
                $this->stats[] = $stats;
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

    private function mergeOptions(array $options1, array $options2)
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

    private function startNewRequest()
    {
        $this->stats = [];
        $this->response = null;
    }

    /**
     * @param Response|ResponseInterface $response
     */
    private function setLastResponse($response)
    {
        $this->response = $response;
    }

    public function getOptionsForCall(array $options = [])
    {
        return $this->mergeOptions($this->options, $options);
    }

    private function getCurrentStat()
    {
        return end($this->stats);
    }

    public function getLastResponse()
    {
        return $this->response;
    }

    public function getRedirects()
    {
        $redirects = [];

        foreach ($this->stats as $stat) {
            $redirects[] = (string) $stat->getEffectiveUri();
        }

        return $redirects;
    }

    public function getUrl()
    {
        return (string) $this->getUri();
    }

    private function getUri()
    {
        $stat = $this->getCurrentStat();

        if (!$stat) {
            return null;
        }

        return $stat->getEffectiveUri();
    }

    public function getHttpHeadersRequest()
    {
        $stat = $this->getCurrentStat();
        if (!$stat instanceof TransferStats) {
            return [];
        }
        return $stat->getRequest()->getHeaders();
    }

    public function getHtml()
    {
        return (string) $this->response->getBody();
    }

    public function getHttpHeadersResponse()
    {
        $stat = $this->getCurrentStat();

        if (!$stat instanceof TransferStats) {
            return [];
        }

        if (null === $stat->getResponse()) {
            return [];
        }

        return $stat->getResponse()->getHeaders();
    }


    public function get($url, array $options = [], $referer = null)
    {
        if (null !== $referer) {
            $options[RequestOptions::HEADERS]['Referer'] = $referer;
        }

        $this->startNewRequest();
        $response = $this->client->get($url, $this->getOptionsForCall($options));
        $this->setLastResponse($response);

        return $this->getHtml();
    }

    public function post($url, array $params = [], array $options = [], $referer = null)
    {
        if (null !== $referer) {
            $options[RequestOptions::HEADERS]['Referer'] = $referer;
        }

        if ($params) {
            $options[RequestOptions::FORM_PARAMS] = $params;
        }

        $this->startNewRequest();
        $response = $this->client->post($url, $this->getOptionsForCall($options));
        $this->setLastResponse($response);

        return $this->getHtml();
    }

    public function submitForm(Form $form, array $options = [], $referer = null)
    {
        if (null !== $referer) {
            $options[RequestOptions::HEADERS]['Referer'] = $referer;
        }

        $action = $form->getAction();
        $method = $form->getMethod();

        $this->startNewRequest();
        $response = $this->client->request($method, $action, $this->getOptionsForCall($form->getOptions($options)));
        $this->setLastResponse($response);

        return $this->getHtml();
    }


    public function trace($includeResponseBody = false)
    {
        (new Trace($this->stats))->dump($includeResponseBody);
    }
}
<?php

namespace Sb\Browser;

use GuzzleHttp\RequestOptions;
use GuzzleHttp\Psr7;
use Psr\Http\Message\UriInterface;

class Form
{
    const ENCODING_URLENCODED = 'application/x-www-form-urlencoded';
    const ENCODING_MULTIPART = 'multipart/form-data';

    private $action;
    private $method;
    private $encoding;

    private $formParams = [];
    private $multipart = [];

    /**
     * Form constructor.
     * @param \simple_html_dom_node $dom
     */
    public function __construct($dom = null)
    {
        $this->init();

        if ($dom) {
            $this->fromDom($dom);
        }
    }

    public function init()
    {
        $this->action = '';
        $this->method = 'GET';
        $this->encoding = self::ENCODING_URLENCODED;

        $this->formParams = [];
        $this->multipart = [];
    }

    private function isValid($dom)
    {
        return !(!$dom instanceof \simple_html_dom && !$dom instanceof \simple_html_dom_node);
    }

    /**
     * @param \simple_html_dom_node $dom
     * @return $this
     */
    public function fromDom($dom)
    {
        $this->init();

        if (!$this->isValid($dom)) {
            return $this;
        }

        $this->action = $dom->getAttribute('action');

        $method = $dom->getAttribute('method');
        if ($method) {
            $this->method = strtoupper($method);
        }

        $enctype = $dom->getAttribute('enctype');
        if ($enctype === self::ENCODING_MULTIPART) {
            $this->encoding = self::ENCODING_MULTIPART;
        }

        $inputs = $dom->find('input');
        /** @var \simple_html_dom_node[] $inputs  */
        foreach ($inputs as $input) {
            $inputType = $input->getAttribute('type');
            $inputName = $input->getAttribute('name');
            if ($inputName) {
                $this->setNameValue($inputName, $input->getAttribute('value'));
            }
        }

        return $this;
    }

    public function isDefaultEncoding()
    {
        return $this->encoding === self::ENCODING_URLENCODED;
    }

    public function setNameValue($name, $value)
    {
        if ($this->isDefaultEncoding()) {
            $this->formParams[$name] = $value;
        } else {
            $this->multipart[] = [
                'name' => $name,
                'contents' => $value
            ];
        }
    }

    public function __toString()
    {
        return
        "Action: \"{$this->action}\"\n" .
        "Method: \"{$this->method}\"\n" .
        "Encoding: \"{$this->encoding}\"\n" .
        "Form Params: " . print_r($this->formParams, true) . "\n" .
        "Multipart: " . print_r($this->multipart, true). "\n";
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getOptions(array $options = [])
    {
        if ($this->isDefaultEncoding()) {
            $options[RequestOptions::FORM_PARAMS] = $this->formParams;
            return $options;
        }

        $options[RequestOptions::MULTIPART] = $this->multipart;
        return $options;
    }
}
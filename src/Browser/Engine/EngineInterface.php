<?php

namespace Sb\Browser\Engine;

use Sb\Browser\Form;

interface EngineInterface
{
    public function get($url, array $options = [], $referer = null);
    public function post($url, array $params = [], array $options = [], $referer = null);
    public function submitForm(Form $form, array $options = [], $referer = null);

    public function setBrowserMockOptions();
    public function setProxy($proxy);
    public function setCookiesFileLocation($file);

    public function getUrl();
    public function getHttpHeadersResponse();
    public function getHtml();
    public function getRedirects();

    public function trace($includeResponseBody = false);
}
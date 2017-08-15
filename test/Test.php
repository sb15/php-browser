<?php

include dirname(__DIR__) . '/vendor/autoload.php';

$guzzleEngine = new \Sb\Browser\Engine\Guzzle();
$browser = new \Sb\Browser\Browser($guzzleEngine);

$browser->get('http://httpbin.org/get');
echo $browser->getHtml();

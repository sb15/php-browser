<?php

include dirname(__DIR__) . "/vendor/autoload.php";

$browser = new \Sb\Browser\Browser();
$browser->get('http://httpbin.org/get');
echo $browser->getHtml();

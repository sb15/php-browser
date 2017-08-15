<?php

namespace Sb\Browser\Engine\Guzzle;

use GuzzleHttp\TransferStats;

class Trace
{
    /**
     * @var TransferStats[]
     */
    private $stats;

    public function __construct(array $stats = [])
    {
        $this->stats = $stats;
    }

    /**
     * @param TransferStats $stat
     * @param bool $includeResponseBody
     */
    private function statTrace($stat, $includeResponseBody = false)
    {

        echo str_repeat('=', 80) . "\n";
        echo (string) $stat->getRequest()->getUri() . "\n";
        echo str_repeat('=', 80) . "\n\n";

        echo $stat->getRequest()->getMethod() . ' ' . $stat->getRequest()->getRequestTarget() . ' ' . $stat->getRequest()->getProtocolVersion() . "\n";
        $headers = $stat->getRequest()->getHeaders();

        $host = $headers['Host'];
        echo 'Host: ' . implode(', ', $host) . "\n";
        unset($headers['Host']);

        ksort($headers);

        foreach ($headers as $headerName => $headerValues) {
            echo $headerName . ': ' . implode(', ', $headerValues) . "\n";
        }
        echo "\n";

        $body = (string) $stat->getRequest()->getBody();
        if ($body) {
            echo $body . "\n";
        } else {
            echo "<Empty body>\n";
        }

        echo "\n";

        if (null === $stat->getResponse()) {
            echo "NULL Response\n\n";
            return;
        }

        echo 'HTTP/' . $stat->getResponse()->getProtocolVersion() . ' ' . $stat->getResponse()->getStatusCode() . "\n";
        $headers = $stat->getResponse()->getHeaders();

        ksort($headers);
        foreach ($headers as $headerName => $headerValues) {
            echo $headerName . ': ' . implode(', ', $headerValues) . "\n";
        }
        echo "\n";

        if ($includeResponseBody) {
            echo (string) $stat->getResponse()->getBody() . "\n";
        } else {
            echo '<Body size: ' .  strlen((string) $stat->getResponse()->getBody()) . " bytes>\n";
        }

        echo "\n";
    }

    public function dump($includeResponseBody = false)
    {
        foreach ($this->stats as $stat) {
            $this->statTrace($stat, $includeResponseBody);
        }
    }
}
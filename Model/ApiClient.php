<?php
declare(strict_types=1);

namespace Simon\Integration\Model;

use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class ApiClient
{
    private $curl;
    private $logger;
    private $baseUrl;
    private $authKey;

    public function __construct(
        Curl $curl,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->logger = $logger;
    }

    public function setConfig(string $baseUrl, string $authKey): void
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->authKey = $authKey;
    }

    public function submit(string $endpoint, array $data): bool
    {
        if (empty($this->baseUrl) || empty($this->authKey)) {
            $this->logger->error('SIMON: API URL or Auth Key not configured');
            return false;
        }

        $url = $this->baseUrl . '/api/' . ltrim($endpoint, '/');

        $this->curl->setHeaders([
            'Content-Type' => 'application/json',
            'X-Auth-Key' => $this->authKey,
        ]);

        $this->curl->setTimeout(30);
        $this->curl->post($url, json_encode($data));

        $statusCode = $this->curl->getStatus();
        $response = $this->curl->getBody();

        if ($statusCode >= 200 && $statusCode < 300) {
            return true;
        }

        $this->logger->error('SIMON API Error: ' . $statusCode . ' - ' . $response);
        return false;
    }
}

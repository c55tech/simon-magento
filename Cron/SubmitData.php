<?php
declare(strict_types=1);

namespace Simon\Integration\Cron;

use Simon\Integration\Model\DataCollector;
use Simon\Integration\Model\ApiClient;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SubmitData
{
    private $scopeConfig;
    private $storeManager;
    private $dataCollector;
    private $apiClient;
    private $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DataCollector $dataCollector,
        ApiClient $apiClient,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->dataCollector = $dataCollector;
        $this->apiClient = $apiClient;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        if (!$this->scopeConfig->getValue('simon/cron/enable_cron')) {
            return;
        }

        $apiUrl = $this->scopeConfig->getValue('simon/api/api_url');
        $authKey = $this->scopeConfig->getValue('simon/api/auth_key');
        $clientId = $this->scopeConfig->getValue('simon/client/client_id');
        $siteId = $this->scopeConfig->getValue('simon/site/site_id');

        if (empty($apiUrl) || empty($authKey) || empty($clientId) || empty($siteId)) {
            return;
        }

        $this->apiClient->setConfig($apiUrl, $authKey);

        $siteData = $this->dataCollector->collect();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        $payload = [
            'client_id' => (int) $clientId,
            'site_id' => (int) $siteId,
            'cms_type' => 'magento',
            'site_name' => $this->scopeConfig->getValue('general/store_information/name'),
            'site_url' => $baseUrl,
            'data' => $siteData,
        ];

        if ($this->apiClient->submit('intake', $payload)) {
            $this->logger->info('SIMON: Data submitted successfully via cron');
        }
    }
}

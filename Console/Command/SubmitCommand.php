<?php
declare(strict_types=1);

namespace Simon\Integration\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Simon\Integration\Model\DataCollector;
use Simon\Integration\Model\ApiClient;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class SubmitCommand extends Command
{
    private $scopeConfig;
    private $storeManager;
    private $dataCollector;
    private $apiClient;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DataCollector $dataCollector,
        ApiClient $apiClient,
        string $name = null
    ) {
        parent::__construct($name);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->dataCollector = $dataCollector;
        $this->apiClient = $apiClient;
    }

    protected function configure()
    {
        $this->setName('simon:submit')
            ->setDescription('Submit site data to SIMON API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Collecting site data...');

        $apiUrl = $this->scopeConfig->getValue('simon/api/api_url');
        $authKey = $this->scopeConfig->getValue('simon/api/auth_key');
        $clientId = $this->scopeConfig->getValue('simon/client/client_id');
        $siteId = $this->scopeConfig->getValue('simon/site/site_id');

        if (empty($apiUrl) || empty($authKey)) {
            $output->writeln('<error>API URL or Auth Key not configured</error>');
            return Command::FAILURE;
        }

        if (empty($clientId) || empty($siteId)) {
            $output->writeln('<error>Client ID or Site ID not configured</error>');
            return Command::FAILURE;
        }

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

        $output->writeln('Submitting to SIMON API...');

        if ($this->apiClient->submit('intake', $payload)) {
            $output->writeln('<info>Data submitted successfully!</info>');
            return Command::SUCCESS;
        }

        $output->writeln('<error>Failed to submit data</error>');
        return Command::FAILURE;
    }
}

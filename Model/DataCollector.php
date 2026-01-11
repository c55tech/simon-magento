<?php
declare(strict_types=1);

namespace Simon\Integration\Model;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Module\ModuleListInterface;
use Psr\Log\LoggerInterface;

class DataCollector
{
    private $productMetadata;
    private $deploymentConfig;
    private $moduleList;
    private $logger;

    public function __construct(
        ProductMetadataInterface $productMetadata,
        DeploymentConfig $deploymentConfig,
        ModuleListInterface $moduleList,
        LoggerInterface $logger
    ) {
        $this->productMetadata = $productMetadata;
        $this->deploymentConfig = $deploymentConfig;
        $this->moduleList = $moduleList;
        $this->logger = $logger;
    }

    public function collect(): array
    {
        $data = [];

        // Core version
        $data['core'] = [
            'version' => $this->productMetadata->getVersion(),
            'status' => 'up-to-date', // Would need to check for updates
        ];

        // Log summary (simplified - Magento doesn't have a simple log query)
        $data['log_summary'] = [
            'total' => 0,
            'error' => 0,
            'warning' => 0,
            'by_level' => [],
        ];

        // Environment
        $data['environment'] = $this->getEnvironment();

        // Extensions (modules)
        $data['extensions'] = $this->getExtensions();

        // Themes (would need theme collection)
        $data['themes'] = [];

        return $data;
    }

    private function getEnvironment(): array
    {
        $dbConfig = $this->deploymentConfig->get('db/connection/default');

        return [
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => (int) ini_get('max_execution_time'),
            'web_server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'database_type' => $dbConfig['driver_options']['driver'] ?? 'mysql',
            'database_version' => '', // Would need DB connection to get version
            'php_modules' => get_loaded_extensions(),
        ];
    }

    private function getExtensions(): array
    {
        $modules = $this->moduleList->getAll();
        $extensions = [];

        foreach ($modules as $moduleName => $moduleInfo) {
            $extensions[] = [
                'type' => 'module',
                'machine_name' => $moduleName,
                'human_name' => $moduleName,
                'version' => $moduleInfo['setup_version'] ?? '0.0.0',
                'status' => 'enabled',
                'is_custom' => strpos($moduleName, 'Simon_') === 0 ? false : true,
            ];
        }

        return $extensions;
    }
}

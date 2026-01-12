# SIMON Magento 2 Module

Magento 2 module for integrating with the SIMON monitoring system.

## Installation

### Via Composer (Recommended)

Add the repository using Composer commands:

```bash
# Add the repository
composer config repositories.simon-magento vcs git@github.c55:c55tech/simon-magento.git

# Install the module
composer require simon/integration:dev-main

# Enable and configure
php bin/magento module:enable Simon_Integration
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

Alternatively, you can manually add the repository to your `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.c55:c55tech/simon-magento.git"
    }
  ],
  "require": {
    "simon/integration": "dev-main"
  }
}
```

Then install:

```bash
composer require simon/integration:dev-main
php bin/magento module:enable Simon_Integration
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

### Manual Installation

1. Copy the module to: `app/code/Simon/Integration`
2. Enable the module:
   ```bash
   php bin/magento module:enable Simon_Integration
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento cache:flush
   ```

## Configuration

### Step 1: Configure Settings

1. Navigate to: **Stores → Configuration → SIMON → SIMON Settings**
2. Under **API Configuration**:
   - Enter **API URL**: Base URL of your SIMON API (e.g., `http://localhost:3000`)
   - Enter **Auth Key**: Your SIMON authentication key
3. Under **Cron Settings**:
   - **Enable Automatic Submission**: Yes/No
   - **Cron Interval**: Time between submissions (in seconds, default: 3600)
4. Click **Save Config**

### Step 2: Create Client and Site

Use the Magento admin interface or API to create client and site records, then enter the IDs in the configuration.

## CLI Command

Submit site data manually:

```bash
php bin/magento simon:submit
```

## Cron Integration

If enabled, the module automatically submits data when Magento cron runs, based on the configured interval.

## What Data is Collected

- **Core**: Magento version
- **Log Summary**: Error counts (simplified)
- **Environment**: PHP version, database info, web server
- **Extensions**: All installed modules with versions

## Requirements

- Magento 2.4 or higher
- PHP 7.4 or higher
- cURL extension enabled

## Troubleshooting

- Check Magento logs: `var/log/system.log`, `var/log/exception.log`
- Verify API URL is accessible
- Ensure Client ID and Site ID are configured
- Test with CLI: `php bin/magento simon:submit`

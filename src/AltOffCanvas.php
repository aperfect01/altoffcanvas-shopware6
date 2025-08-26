<?php declare(strict_types=1);

namespace AltOffCanvas;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class AltOffCanvas extends Plugin
{
    public const PLUGIN_NAME = 'AltOffCanvas';

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);
        
        // Keep user data by default
        if ($uninstallContext->keepUserData()) {
            return;
        }
        
        // Remove plugin data if user chooses to remove all data
        // We'll add custom field cleanup here later
    }
}
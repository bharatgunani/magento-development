<?php
/**
 * Created by RedChamps.
 * User: Rav
 * Date: 01/01/18
 * Time: 5:10 PM
 */
namespace RedChamps\ShareCart\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.2') < 0) {
            $installer = $setup;

            $installer->startSetup();
            $connection = $installer->getConnection();
            $settingUpdates = [
                [
                    'old_path' => 'share_cart/settings/email_template',
                    'new_path' => 'share_cart/email/template'
                ],
                [
                    'old_path' => 'share_cart/settings/email_sender',
                    'new_path' => 'share_cart/email/sender'
                ],
                [
                    'old_path' => 'share_cart/settings/copy_to',
                    'new_path' => 'share_cart/email/copy_to'
                ],
                [
                    'old_path' => 'share_cart/settings/short_url',
                    'new_path' => 'share_cart/url_shortener/enabled'
                ],[
                    'old_path' => 'share_cart/settings/google_api_key',
                    'new_path' => 'share_cart/url_shortener/api_key'
                ],
            ];

            foreach ($settingUpdates as $settingUpdate) {
                $bind = [
                    'path'  => $settingUpdate['new_path']
                ];
                $where = 'path = "' . $settingUpdate['old_path'] . '"';
                $connection->update(
                    $installer->getTable('core_config_data'),
                    $bind,
                    $where
                );
            }
            $installer->endSetup();
        }
    }
}

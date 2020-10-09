<?php
namespace RedChamps\ShareCart\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the ShareCart module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $connection = $installer->getConnection();

            $connection->addColumn(
                $installer->getTable('redchamps_share_cart'),
                'sharing_method',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Sharing Method',
                ]
            );

            $connection->addColumn(
                $installer->getTable('redchamps_share_cart'),
                'recipient_email',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Recipient Email',
                ]
            );

            $connection->addColumn(
                $installer->getTable('redchamps_share_cart'),
                'recipient_mobile',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Recipient Mobile Number',
                ]
            );

            $connection->addColumn(
                $installer->getTable('redchamps_share_cart'),
                'restore_count',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Cart Restore Count',
                ]
            );

            $connection->addColumn(
                $installer->getTable('redchamps_share_cart'),
                'status',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Status',
                ]
            );

        }

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $connection = $installer->getConnection();
            $connection->addColumn(
                $installer->getTable('quote'),
                'is_shared_cart',
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'comment' => 'Is Shared Cart?',
                    'default' => 0
                ]
            );
            $query = $connection->select()->from($installer->getTable('redchamps_share_cart'), 'quote_id');
            $result = $connection->fetchCol($query);
            if ($result && is_array($result)) {
                $quoteIds = implode(',', $result);
                if ($quoteIds) {
                    $connection->update(
                        $installer->getTable('quote'),
                        ['is_shared_cart' => 1],
                        ["entity_id in ($quoteIds)"]
                    );
                }
            }
        }

        $installer->endSetup();
    }
}

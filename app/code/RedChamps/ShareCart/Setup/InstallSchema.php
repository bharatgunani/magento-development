<?php
namespace RedChamps\ShareCart\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'redchamps_share_cart'
         */
        $shareCartTable = $installer->getConnection()->newTable(
            $installer->getTable('redchamps_share_cart')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            11,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Shared Cart Id'
        )->addColumn(
            'created_at',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Created At'
        )->addColumn(
            'last_used_at',
            Table::TYPE_TIMESTAMP,
            11,
            ['nullable' => false],
            'Last Used At'
        )->addColumn(
            'unique_id',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Unique Id'
        )->addColumn(
            'quote_id',
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Quote Id'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            5,
            ['unsigned' => true, 'default' => null],
            'Store Id'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            10,
            ['nullable' => true],
            'Customer Id'
        )->addColumn(
            'sender_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Sender Name'
        )->addColumn(
            'sender_email',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Sender Email'
        )->addForeignKey(
            $setup->getFkName(
                'redchamps_share_cart',
                'store_id',
                'store',
                'store_id'
            ),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Share Cart Table'
        );

        $installer->getConnection()->createTable($shareCartTable);

        $quoteTable = $installer->getTable('quote');
        $orderTable = $installer->getTable('sales_order');

        $columns = [
            'shared_cart_info' => [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Shared Cart Info',
            ],

        ];

        $connection = $installer->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($quoteTable, $name, $definition);
            $connection->addColumn($orderTable, $name, $definition);
        }
    }
}

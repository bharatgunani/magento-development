<?php
namespace RedChamps\ShareCart\Block\Adminhtml\Report;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelperData;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\Order\Config;
use Magento\Store\Model\WebsiteFactory;

class Grid extends Extended
{

    /**
     * @var OrderCollectionFactory
     */
    protected $salesOrderModelFactory;

    /**
     * @var Config
     */
    protected $salesOrderConfig;

    protected $websiteFactory;

    public function __construct(
        Context $context,
        BackendHelperData $backendHelper,
        OrderCollectionFactory $salesOrderModelFactory,
        Config $salesOrderConfig,
        WebsiteFactory $websiteFactory
    ) {
        $this->salesOrderModelFactory = $salesOrderModelFactory;
        $this->salesOrderConfig = $salesOrderConfig;
        $this->websiteFactory = $websiteFactory;
        parent::__construct($context, $backendHelper);

        $this->setId('shareCartReportGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->salesOrderModelFactory->create()
            ->addFieldToFilter('shared_cart_info', ['notnull' => true]);

        $billingAliasName = 'billing_o_a';

        $collection->getSelect()->joinLeft(
            [$billingAliasName => $collection->getTable('sales_order_address')],
            "(main_table.entity_id = {$billingAliasName}.parent_id" .
            " AND {$billingAliasName}.address_type = 'billing')",
            [
                $billingAliasName . '.name' => "CONCAT_WS(' ', $billingAliasName.firstname, $billingAliasName.lastname)"
            ]
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'shared_by',
            [
                'header' => 'Shared By',
                'index' => 'shared_cart_info',
                'renderer'  => \RedChamps\ShareCart\Block\Adminhtml\Report\Renderer\SharedBy::class
            ]
        );

        $this->addColumn(
            'real_order_id',
            [
                'header'=> __('Order #'),
                'width' => '80px',
                'type'  => 'text',
                'index' => 'increment_id',
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'website_id',
                [
                    'header' => 'Purchased From (Store)',
                    'index' => 'store_id',
                    'type'      => 'options',
                    'options'   => $this->websiteFactory->create()->getCollection()->toOptionHash(),
                ]
            );
        }

        $this->addColumn('created_at', [
                'header' => __('Purchased On'),
                'index' => 'created_at',
                'type' => 'datetime',
                'width' => '100px',
            ]);

        $this->addColumn(
            'bill_to_name',
            [
                'header' => __('Bill-to Name'),
                'index' => 'billing_o_a.name',
            ]
        );

        $this->addColumn(
            'customer_email',
            [
                'header' => __('Customer Email'),
                'index' => 'customer_email',
            ]
        );

        $this->addColumn(
            'base_grand_total',
            [
                'header' => __('Grand Total'),
                'index' => 'base_grand_total',
                'type'  => 'currency',
                'currency' => 'base_currency_code',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type'  => 'options',
                'width' => '70px',
                'options' => $this->salesOrderConfig->getStatuses(),
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => 'Action',
                'align' =>'left',
                'index' => 'increment_id',
                'type'  => 'action',
                'getter'     => 'getId',
                'actions'   => [
                    [
                        'caption' => __('View Order'),
                        'url'     => ['base'=>'sales/order/view'],
                        'target'=>'_blank',
                        'field'   => 'order_id',
                        'data-column' => 'action',
                    ]
                ],
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ]
        );

        $this->addExportType('*/*/exportCsv', __('CSV'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $row->getId()]);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current'=>true]);
    }
}

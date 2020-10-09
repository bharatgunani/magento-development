<?php
namespace RedChamps\ShareCart\Block\Adminhtml\SharedCarts\Content;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelperData;
use Magento\Store\Model\WebsiteFactory;
use RedChamps\ShareCart\Block\Adminhtml\SharedCarts\Content\Renderer\Action;
use RedChamps\ShareCart\Block\Adminhtml\SharedCarts\Content\Renderer\SharedBy;
use RedChamps\ShareCart\Block\Adminhtml\SharedCarts\Content\Renderer\SharedTo;
use RedChamps\ShareCart\Model\ResourceModel\ShareCart\CollectionFactory as ShareCartCollectionFactory;

class Grid extends Extended
{

    /**
     * @var ShareCartCollectionFactory
     */
    protected $shareCartCollectionFactory;

    protected $websiteFactory;

    public function __construct(
        Context $context,
        BackendHelperData $backendHelper,
        ShareCartCollectionFactory $shareCartCollectionFactory,
        WebsiteFactory $websiteFactory
    ) {
        $this->shareCartCollectionFactory = $shareCartCollectionFactory;
        $this->websiteFactory = $websiteFactory;
        parent::__construct($context, $backendHelper);

        $this->setId('sharedCartsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->shareCartCollectionFactory->create()
            ->setOrder('id', 'desc');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('shared_cart');

        $this->getMassactionBlock()->addItem('delete', [
            'label'=> __('Delete'),
            'url'  => $this->getUrl('*/*/massDelete'),
            'confirm' => __('Are you sure?')
        ]);

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'index' => 'id'
            ]
        );

        $this->addColumn(
            'shared_by',
            [
                'header' => __('Shared By'),
                'index' => 'sender_name',
                'renderer'  => SharedBy::class,
                'filter_condition_callback' => [$this, '_shareByFilter']
            ]
        );

        $this->addColumn(
            'shared_to',
            [
                'header' => __('Shared To'),
                'index' => 'recipient_email',
                'renderer'  => SharedTo::class
            ]
        );

        $this->addColumn(
            'sharing_method',
            [
                'header' => __('Sharing Method'),
                'index' => 'sharing_method',
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'website_id',
                [
                    'header' => __('Shared From (Store)'),
                    'index' => 'store_id',
                    'type'      => 'options',
                    'options'   => $this->websiteFactory->create()->getCollection()->toOptionHash(),
                ]
            );
        }

        $this->addColumn(
            'created_at',
            [
                'header' => __('Shared ON'),
                'index' => 'created_at',
                'renderer'  => \RedChamps\ShareCart\Block\Adminhtml\SharedCarts\Content\Renderer\Date::class,
                'type' => 'datetime',
                'width' => '100px',
                'filter_condition_callback' => [$this, '_dateFilter']
            ]
        );

        $this->addColumn(
            'restore_count',
            [
                'header' => __('Clicks'),
                'index' => 'restore_count',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type'      => 'options',
                'options'   => ['Available' => 'Available', 'Expired' => 'Expired'],
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => 'Action',
                'align' =>'left',
                'width' => '200px',
                'index' => 'unique_id',
                'type'  => 'action',
                'renderer' => Action::class,
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ]
        );

        $this->addExportType('*/*/exportCsv', __('CSV'));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current'=>true]);
    }

    protected function _dateFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        if (isset($value['from'])) {
            $collection->getSelect()->where('created_at >= ?', $value['from']->getTimeStamp());
        }
        if (isset($value['to'])) {
            $collection->getSelect()->where('created_at <= ?', $value['to']->getTimeStamp());
        }
        return $this;
    }

    protected function _shareByFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->getSelect()->where("sender_name like '%$value%' or sender_email like '%$value%'");
        return $this;
    }

    protected function _shareToFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->getSelect()->where("recipient_name like '%$value%'");
        $collection->getSelect()->orWhere("recipient_email like '%$value%'");
        $collection->getSelect()->orWhere("recipient_mobile like '%$value%'");
        return $this;
    }
}

<?php
namespace RedChamps\ShareCart\Model\System\Config\Source\Customer;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

class Group
{
    protected $_options;

    /**
     * @var CollectionFactory
     */
    protected $customerGroupCollectionFactory;

    public function __construct(
        CollectionFactory $customerGroupCollectionFactory
    ) {
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
    }
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = array_merge(
                [['value' => 'all', 'label' => 'All']],
                $this->customerGroupCollectionFactory->create()
                    ->loadData()->toOptionArray()
            );
        }
        return $this->_options;
    }
}

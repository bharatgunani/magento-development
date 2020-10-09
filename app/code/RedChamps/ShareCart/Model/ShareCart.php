<?php
namespace RedChamps\ShareCart\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class ShareCart extends AbstractModel
{
    /**
     * @var DateTimeFactory
     */
    protected $dateTimeFactory;

    public function __construct(
        DateTimeFactory $dateTimeFactory,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\RedChamps\ShareCart\Model\ResourceModel\ShareCart::class);
    }

    public function beforeSave()
    {
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt($this->dateTimeFactory->create()->timestamp(time()));
        }
        $this->setLastUsedAt($this->dateTimeFactory->create()->timestamp(time()));
        return parent::beforeSave();
    }
}

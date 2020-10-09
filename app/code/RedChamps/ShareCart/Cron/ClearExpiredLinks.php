<?php
namespace RedChamps\ShareCart\Cron;

use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use RedChamps\ShareCart\Model\ConfigManager;
use RedChamps\ShareCart\Model\ResourceModel\ShareCart\CollectionFactory as ShareCartCollectionFactory;
use Magento\Quote\Model\QuoteRepository;

class ClearExpiredLinks
{

    /**
     * @var DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var ConfigManager
     */
    protected $shareCartHelper;

    /**
     * @var ShareCartCollectionFactory
     */
    protected $shareCartCollectionFactory;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    public function __construct(
        DateTimeFactory $dateTimeDateTimeFactory,
        ConfigManager $shareCartHelper,
        ShareCartCollectionFactory $shareCartCollectionFactory,
        QuoteRepository $quoteRepository
    ) {
        $this->dateTimeFactory = $dateTimeDateTimeFactory;
        $this->shareCartHelper = $shareCartHelper;
        $this->shareCartCollectionFactory = $shareCartCollectionFactory;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute()
    {
        $currentTimeStamp = $this->dateTimeFactory->create()->timestamp(time());
        $expiryTime = $this->shareCartHelper->getGeneralConfig('link_expiry');
        $diff = $currentTimeStamp-$expiryTime;
        $sharedCarts = $this->shareCartCollectionFactory->create()
            ->addFieldToFilter('status', 'Available')
            ->addFieldToFilter('created_at', ['lteq' => $diff]);
        foreach ($sharedCarts as $sharedCart) {
            $quote = $this->quoteRepository->get($sharedCart->getQuoteId());
            if ($quote && $quote->getId()) {
                $this->quoteRepository->delete($quote);
            }
            $sharedCart->setStatus('Expired')->save();
        }
    }
}

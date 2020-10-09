<?php
namespace RedChamps\ShareCart\Model\Share\Actions;

use Magento\Checkout\Model\CartFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use RedChamps\ShareCart\Model\ConfigManager;
use RedChamps\ShareCart\Model\EmailSender;
use RedChamps\ShareCart\Model\GoogleShortner;
use RedChamps\ShareCart\Model\ResourceModel\ShareCart as ShareCartResource;
use RedChamps\ShareCart\Model\ResourceModel\ShareCart\CollectionFactory as ShareCartCollectionFactory;
use RedChamps\ShareCart\Model\ShareCartFactory;

class Base
{
    /**
     * @var ShareCartCollectionFactory
     */
    protected $shareCartCollectionFactory;

    /**
     * @var CartFactory
     */
    protected $checkoutCartFactory;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EmailSender
     */
    protected $emailSender;

    /**
     * @var ShareCartFactory
     */
    protected $shareCartFactory;

    protected $shareCartResource;

    /**
     * @var ConfigManager
     */
    protected $shareCartHelper;

    protected $quoteRepository;

    protected $_url;

    protected $googleShortnerApi;

    protected $jsonSerializer;

    protected $serializer;

    protected $currentQuote;

    public function __construct(
        ShareCartCollectionFactory $shareCartCollectionFactory,
        CartFactory $checkoutCartFactory,
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager,
        EmailSender $emailSender,
        ShareCartFactory $shareCartFactory,
        ShareCartResource $shareCartResource,
        ConfigManager $shareCartHelper,
        UrlInterface $url,
        CartRepositoryInterface $quoteRepository,
        GoogleShortner $googleShortnerApi,
        Json $jsonSerializer,
        SerializerInterface $serializer
    ) {
        $this->shareCartCollectionFactory = $shareCartCollectionFactory;
        $this->checkoutCartFactory = $checkoutCartFactory;
        $this->quoteFactory = $quoteFactory;
        $this->emailSender = $emailSender;
        $this->shareCartFactory = $shareCartFactory;
        $this->shareCartResource = $shareCartResource;
        $this->shareCartHelper = $shareCartHelper;
        $this->quoteRepository = $quoteRepository;
        $this->storeManager = $storeManager;
        $this->_url = $url;
        $this->googleShortnerApi = $googleShortnerApi;
        $this->jsonSerializer = $jsonSerializer;
        $this->serializer = $serializer;
    }

    public function getSharedCart($newQuote, $senderName, $senderEmail, $sharingMethod, $customerId = null, $recipientData = [])
    {
        $uniqueId = hash('sha256', uniqid(rand(), true));
        $shareCart = $this->shareCartFactory->create()->setData(
            [
                'unique_id' => $uniqueId,
                'quote_id' => $newQuote->getId(),
                'store_id' => $newQuote->getStoreId(),
                'customer_id' => $customerId,
                'sender_name' => $senderName,
                'sender_email' => $senderEmail,
                'sharing_method' => $sharingMethod,
                'restore_count' => 0,
                'status' => 'Available'
            ]
        );
        if (isset($recipientData['email'])) {
            $shareCart->setRecipientEmail($recipientData['email']);
        }
        if (isset($recipientData['number'])) {
            $shareCart->setRecipientMobile($recipientData['number']);
        }
        $this->shareCartResource->save($shareCart);

        return $shareCart;
    }

    protected function getShareUrl($sharedCart)
    {
        $url = $this->_url->getUrl('share_cart/action/restore', [
            'unique_id' => $sharedCart->getUniqueId(),
            '_query' => $this->getGaParams($sharedCart),
            '_nosid' => true,
            '_type' => 'direct_link'
        ]);
        return $this->googleShortnerApi->shortenUrl($url);
    }

    public function formatResponse($response)
    {
        $result = $response;
        if (!is_array($response)) {
            $result[] = $response;
        }
        return $this->jsonSerializer->serialize($result);
    }

    public function copyQuote($quoteId = null)
    {
        $currentQuote = $this->getCurrentQuote($quoteId);
        $newQuote =  $this->quoteFactory->create()->setStoreId($currentQuote->getStoreId());
        $newQuote
            ->merge($currentQuote)
            ->setIsActive(false)
            ->setIsSharedCart(1)
            ->assignCustomer($currentQuote->getCustomer());
        if (!$newQuote->getIsVirtual() && $newShippingAddress = $newQuote->getShippingAddress()) {
            $currentShippingAddress = $currentQuote->getShippingAddress();
            $this->copyShippingInformation($newShippingAddress, $currentShippingAddress);
        }
        $newQuote->collectTotals();
        $this->quoteRepository->save($newQuote);
        return $newQuote;
    }

    public function getCurrentQuote($quoteId = null)
    {
        if (!$this->currentQuote) {
            if ($quoteId) {
                $this->currentQuote = $this->quoteRepository->get($quoteId);
            }
            $this->currentQuote = $this->checkoutCartFactory->create()->getQuote();
        }
        return $this->currentQuote;
    }

    protected function copyShippingInformation($newShippingAddress, $oldShippingAddress)
    {
        $newShippingAddress->setCountryId($oldShippingAddress->getCountryId());
        $newShippingAddress->setRegion($oldShippingAddress->getRegion());
        $newShippingAddress->setPostcode($oldShippingAddress->getPostcode());
        $newShippingAddress->setShippingMethod($oldShippingAddress->getShippingMethod());
        $newShippingAddress->setCollectShippingRates(true);
        return $newShippingAddress;
    }

    protected function getGaParams($sharedCart)
    {
        $params = [];
        if ($utmSource = $this->shareCartHelper->getGaConfig('utm_source')) {
            $params['utm_source'] = $utmSource;
        }
        $utmMedium = $this->shareCartHelper->getGaConfig('utm_medium');
        $params['utm_medium'] = $utmMedium ? $utmMedium : $sharedCart->getSharingMethod();
        if ($utmCampaign = $this->shareCartHelper->getGaConfig('utm_campaign')) {
            $params['utm_campaign'] = $utmCampaign;
        }
        return $params;
    }
}

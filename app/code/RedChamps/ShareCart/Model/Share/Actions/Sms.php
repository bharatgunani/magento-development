<?php
namespace RedChamps\ShareCart\Model\Share\Actions;

use Magento\Checkout\Model\CartFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use RedChamps\ShareCart\Api\SmsApiInterface;
use RedChamps\ShareCart\Model\ConfigManager;
use RedChamps\ShareCart\Model\EmailSender;
use RedChamps\ShareCart\Model\GoogleShortner;
use RedChamps\ShareCart\Model\ResourceModel\ShareCart as ShareCartResource;
use RedChamps\ShareCart\Model\ResourceModel\ShareCart\CollectionFactory as ShareCartCollectionFactory;
use RedChamps\ShareCart\Model\ShareCartFactory;
use Twilio\Rest\ClientFactory as TwilioClientFactory;

class Sms extends Base implements SmsApiInterface
{
    protected $twilioClientFactory;

    public function __construct(
        TwilioClientFactory $twilioClientFactory,
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
        $this->twilioClientFactory = $twilioClientFactory;
        parent::__construct(
            $shareCartCollectionFactory,
            $checkoutCartFactory,
            $quoteFactory,
            $storeManager,
            $emailSender,
            $shareCartFactory,
            $shareCartResource,
            $shareCartHelper,
            $url,
            $quoteRepository,
            $googleShortnerApi,
            $jsonSerializer,
            $serializer
        );
    }
    public function sendSms($senderName, $senderEmail, $recipientNumber, $quoteId = null, $customerId = null)
    {
        $result = [];
        $result['error'] = true;
        try {
            if ($this->shareCartHelper->getTwilioConfig('enabled')) {
                $currentQuote = $this->getCurrentQuote($quoteId);
                if ($currentQuote && $currentQuote->getItemsCount()) {
                    $newQuote = $this->copyQuote($quoteId);
                    $sharedCart = $this->getSharedCart(
                        $newQuote,
                        $senderName,
                        $senderEmail,
                        "SMS",
                        $customerId,
                        ['number' => $recipientNumber]
                    );
                    $url = $this->getSharedCart($sharedCart);
                    $client = $this->twilioClientFactory->create([
                        'username' => $this->shareCartHelper->getTwilioConfig('account_sid'),
                        'password' => $this->shareCartHelper->getTwilioConfig('account_auth_token')
                    ]);
                    $client->messages->create(
                        $recipientNumber,
                        [
                            'from' => $this->shareCartHelper->getTwilioConfig('twilio_phone'),
                            'body' => __(
                                "%1 shared his shopping cart from %2 with you. Please click on link %3 to view shopping cart contents.",
                                $senderName,
                                $this->storeManager->getStore($newQuote->getStoreId())->getFrontendName(),
                                $url
                            )
                        ]
                    );
                    $result['error'] = false;
                    $result['message'] = __('SMS has been sent successfully.');
                } else {
                    $result['message'] = __("The quote doesn't exist or shopping cart is empty.");
                }
            } else {
                $result['message'] = __("Sorry, this action is not allowed. Please contact our support team.");
            }
        } catch (\Exception $e) {
            $result['message'] = __("Some error occurred during sending SMS. Please contact our support team (Technical error message %1).", $e->getMessage());
        }
        if ($quoteId) {
            if ($result['error']) {
                throw new LocalizedException($result['message']);
            }
            return $this->formatResponse($result);
        }
        return $result;
    }
}

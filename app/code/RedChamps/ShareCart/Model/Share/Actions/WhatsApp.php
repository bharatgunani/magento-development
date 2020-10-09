<?php
namespace RedChamps\ShareCart\Model\Share\Actions;

use Magento\Framework\Exception\LocalizedException;
use RedChamps\ShareCart\Api\WhatsAppApiInterface;

class WhatsApp extends Base implements WhatsAppApiInterface
{
    /**
     * share cart via whatsApp.
     *
     * @param string $senderName
     *
     * @param string $senderEmail
     *
     * @param int $quoteId
     *
     * @param int $customerId
     *
     * @return mixed[]
     * @throws LocalizedException
     * @api
     *
     */
    public function whatsApp($senderName, $senderEmail, $quoteId = null, $customerId = null)
    {
        $result = [];
        $result['error'] = true;
        try {
            $currentQuote = $this->getCurrentQuote($quoteId);
            if ($currentQuote && $currentQuote->getItemsCount()) {
                $newQuote = $this->copyQuote($quoteId);
                $sharedCart = $this->getSharedCart($newQuote, $senderName, $senderEmail, "WhatsApp", $customerId);
                $url = $this->getShareUrl($sharedCart);
                $domain = "https://web.whatsapp.com/send?text=";
                $isMobile = \Zend_Http_UserAgent_Mobile::match($_SERVER['HTTP_USER_AGENT'], $_SERVER);
                if ($isMobile) {
                    $domain = 'whatsapp://send?text=';
                }
                $result['error'] = false;
                $result['message'] = $domain . __(
                        '%1 shared his shopping cart from %2 with you. Please click on link %3 to view shopping cart contents.',
                        $senderName,
                        $this->storeManager->getStore($newQuote->getStoreId())->getFrontendName(),
                        $url
                    );
            } else {
                $result['message'] = __("The quote doesn't exist or shopping cart is empty.");
            }
        } catch (\Exception $e) {
            $result['message'] = __(
                "Some error occurred. Please retry. while getting shopping cart URL. Please contact our support team."
            );
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
<?php
namespace RedChamps\ShareCart\Model\Share\Actions;

use Magento\Framework\Exception\LocalizedException;
use RedChamps\ShareCart\Api\UrlApiInterface;

class Url extends Base implements UrlApiInterface
{
    /**
     * share cart via url.
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
    public function getUrl($senderName, $senderEmail, $quoteId = null, $customerId = null)
    {
        $result = [];
        $result['error'] = true;
        try {
            $currentQuote = $this->getCurrentQuote($quoteId);
            if ($currentQuote && $currentQuote->getItemsCount()) {
                $newQuote = $this->copyQuote($quoteId);
                $sharedCart = $this->getSharedCart($newQuote, $senderName, $senderEmail, 'URL', $customerId);
                $url = $this->getShareUrl($sharedCart);
                $result['error'] = false;
                $result['message'] = $url;
            } else {
                $result['message'] = __("The quote doesn't exist or shopping cart is empty.");
            }
        } catch (\Exception $e) {
            $result['message'] = __("Some error occurred while getting shopping cart URL. Please contact our support team.");
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
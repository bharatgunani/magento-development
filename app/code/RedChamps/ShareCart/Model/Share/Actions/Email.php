<?php
namespace RedChamps\ShareCart\Model\Share\Actions;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use RedChamps\ShareCart\Api\EmailApiInterface;

class Email extends Base implements EmailApiInterface
{
    public function sendEmail($senderName, $senderEmail, $recipientEmail, $message, $quoteId = null, $customerId = null)
    {
        $result = [];
        $result['error'] = true;
        try {
            $currentQuote = $this->getCurrentQuote($quoteId);
            if ($currentQuote && $currentQuote->getItemsCount()) {
                $newQuote = $this->copyQuote($quoteId);
                $sharedCart = $this->getSharedCart(
                    $newQuote,
                    $senderName,
                    $senderEmail,
                    'Email',
                    $customerId,
                    ['email' => $recipientEmail]
                );
                $shareUrlCart = $this->getShareUrl($sharedCart);
                $shareUrlCheckout = $shareUrlCart . 'checkout/1';
                $params = new DataObject(
                    [
                        'sender_name' => $senderName,
                        'sender_email' => $senderEmail,
                        'message' => $message,
                        'quote' => $newQuote,
                        'shared_cart' => $sharedCart,
                        'shared_cart_id' => $sharedCart->getId(),
                        'share_url_cart' => $shareUrlCart,
                        'share_url_checkout' => $shareUrlCheckout,
                        'recipient_email' => $recipientEmail,
                        'store' => $newQuote->getStore()
                    ]
                );

                if ($this->emailSender->sendEmail($params)) {
                    $result['error'] = false;
                    $result['message'] = __('Email has been sent successfully.');
                } else {
                    $result['message'] = __('Some error occurred while sending email.');
                }
            } else {
                $result['message'] = __('The shopping cart has no items.');
            }
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
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
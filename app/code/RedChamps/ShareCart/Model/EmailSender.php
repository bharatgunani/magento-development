<?php
namespace RedChamps\ShareCart\Model;

use Magento\Framework\Mail\Template\TransportBuilder;

class EmailSender
{

    /**
     * @var ConfigManager
     */
    protected $shareCartHelper;

    /**
     * @var TransportBuilder
     */
    protected $templateTransportBuilder;

    public function __construct(
        ConfigManager $shareCartHelper,
        TransportBuilder $templateTransportBuilder
    ) {
        $this->shareCartHelper = $shareCartHelper;
        $this->templateTransportBuilder = $templateTransportBuilder;
    }

    public function sendEmail($params)
    {
        $recipientEmailAddresses = $this->explodeEmails($params->getRecipientEmail());
        if (!empty($recipientEmailAddresses)) {
            $result = true;
            foreach ($recipientEmailAddresses as $recipientEmailAddress) {
                $params->setRecipientEmail($recipientEmailAddress);
                if (!$this->processEmail($params)) {
                    $result = false;
                }
            }
            return $result;
        }
    }

    protected function processEmail($params)
    {
        $store = $params->getStore();
        $storeId = $store->getId();

        $mailer = $this->templateTransportBuilder;

        // Retrieve corresponding email template id and customer name
        $templateId = $this->shareCartHelper->getEmailConfig('template');

        $mailer->addTo($params->getRecipientEmail());

        if ($copyToEmails = $this->getEmailCopyTo()) {
            foreach ($copyToEmails as $copyToEmail) {
                $mailer->addBcc($copyToEmail);
            }
        }

        $mailer->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' =>$storeId
            ]
        );

        // Set all required params and send emails
        $emailSender = $this->shareCartHelper->getEmailConfig('sender');
        if ($emailSender == 'cart_sender') {
            $mailer->setFrom(
                [
                    'email'=>$params->getSenderEmail(),
                    'name'=> $params->getSenderName()
                ]
            );
        } else {
            $mailer->setFrom($emailSender);
        }

        $mailer->setTemplateIdentifier($templateId);
        $mailer->setTemplateVars(
            [
                'sender_name'  => $params->getSenderName(),
                'quote'   => $params->getQuote(),
                'message'   => $params->getMessage(),
                'shared_cart' => $params->getSharedCart(),
                'store'         => $store,
                'share_url_cart'     => $params->getShareUrlCart(),
                'share_url_checkout' => $params->getShareUrlCheckout(),
            ]
        );
        $mailer->getTransport()->sendMessage();

        return $this;
    }

    /**
     * @return array|bool
     */
    public function getEmailCopyTo()
    {
        $data = $this->shareCartHelper->getEmailConfig('copy_to');
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }

    protected function explodeEmails($emailAddresses)
    {
        if (!empty($emailAddresses)) {
            return preg_split('/\r\n|[\r\n]/', $emailAddresses);
        }
        return false;
    }
}

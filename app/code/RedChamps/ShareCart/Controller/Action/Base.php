<?php
namespace RedChamps\ShareCart\Controller\Action;

use Magento\Customer\Model\Session  as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Validator\EmailAddress;
use RedChamps\ShareCart\Api\EmailApiInterface;
use RedChamps\ShareCart\Model\ConfigManager;

abstract class Base extends Action
{
    /**
     * @var EmailApiInterface
     */
    protected $shareCartApi;

    protected $customerSession;

    protected $shareCartHelper;

    protected $resultJsonFactory;

    protected $curl;

    protected $formKeyValidator;

    protected $senderName;

    protected $senderEmail;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerSession $customerSession,
        ConfigManager $shareCartHelper,
        Curl $curl,
        Validator $formKeyValidator,
        $shareCartApiInterface
    ) {
        parent::__construct(
            $context
        );
        $this->shareCartApi = isset($shareCartApiInterface['instance']) ? $this->_objectManager->create($shareCartApiInterface['instance']) : $shareCartApiInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->shareCartHelper = $shareCartHelper;
        $this->curl = $curl;
        $this->formKeyValidator = $formKeyValidator;
    }

    public function getSession()
    {
        return $this->messageManager;
    }

    public function isAllowed()
    {
        return $this->shareCartHelper->isAllowed();
    }

    public function sendResponse($response)
    {
        return $this->resultJsonFactory
                ->create()
                ->setData($response);
    }

    protected function _validateCaptcha()
    {
        $isCaptchaEnabled = $this->shareCartHelper->getCaptchaConfig('enabled');
        if (!$this->customerSession->isLoggedIn() && $isCaptchaEnabled) {
            $token = $this->getRequest()->getPost('g-recaptcha-response');
            if ($token) {
                $secretKey = $this->shareCartHelper->getCaptchaConfig('secret_key');
                $url =  'https://www.google.com/recaptcha/api/siteverify?secret='
                    . urlencode($secretKey)
                    . '&response='
                    . urlencode($token);
                $response = file_get_contents($url);
                $responseKeys = json_decode($response, true);
                if (isset($responseKeys["success"])) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    protected function stripTags($input, $allowedTags = null)
    {
        return strip_tags($input, $allowedTags);
    }

    protected function prepareSenderData($request)
    {
        $result = ['success' => true];
        $this->senderEmail = $this->stripTags($request->getPost("sender_email"));
        $this->senderName = $this->stripTags($request->getPost("sender_name"));
        $customerSession = $this->customerSession;
        if ($customerSession->isLoggedIn()) {
            $this->senderName = $customerSession->getCustomer()->getName();
            $this->senderEmail = $customerSession->getCustomer()->getEmail();
        } elseif (!$this->senderName) {
            $result = [
                'error' => true,
                'message' => __('Your Name is required. Please retry.')
            ];
        }
        $this->validateEmailAddress([$this->senderEmail], "Sender Email");
        return $result;
    }

    protected function getCustomerId()
    {
        $customerId = null;
        if ($customer = $this->customerSession->getCustomer()) {
            $customerId = $customer->getId();
        }
        return $customerId;
    }

    public function validateEmailAddress($emailAddresses, $type)
    {
        foreach ($emailAddresses as $emailAddress) {
            if (empty($emailAddress) || !\Zend_Validate::is($emailAddress, EmailAddress::class)) {
                throw new \Exception(__('Invalid %1. Please retry.', $type));
            }
        }
    }
}

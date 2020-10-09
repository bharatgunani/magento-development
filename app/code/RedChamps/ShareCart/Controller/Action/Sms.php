<?php
namespace RedChamps\ShareCart\Controller\Action;

class Sms extends Base
{
    public function execute()
    {
        $response = [];
        $response['error'] = true;
        try {
            if ($this->formKeyValidator->validate($this->getRequest()) && $this->_validateCaptcha()) {
                if ($this->isAllowed()) {
                    $request = $this->getRequest();
                    $recipientNumber = $this->stripTags($request->getPost('recipient_number'));
                    if ($recipientNumber) {
                        $result = $this->prepareSenderData($request);
                        if (isset($result['error'])) {
                            return $this->sendResponse($result);
                        }
                        $response = $this->shareCartApi->sendSms(
                            $this->senderName,
                            $this->senderEmail,
                            $recipientNumber,
                            null,
                            $this->getCustomerId()
                        );
                    } else {
                        $response['message'] = __('Recipient Number is required. Please retry.');
                    }
                } else {
                    $response['message'] = __('Sorry, this action is not allowed or cart is empty.');
                }
            } else {
                $response['message'] = __('Invalid form key. Please refresh the page and retry.');
            }
        } catch (\Exception $exception) {
            $response['message'] = $exception->getMessage();
        }
        return $this->sendResponse($response);
    }
}

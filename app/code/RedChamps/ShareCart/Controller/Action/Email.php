<?php
namespace RedChamps\ShareCart\Controller\Action;

class Email extends Base
{
    public function execute()
    {
        $response = [];
        $response['error'] = true;
        try {
            if ($this->formKeyValidator->validate($this->getRequest()) && $this->_validateCaptcha()) {
                if ($this->isAllowed()) {
                    $request = $this->getRequest();
                    if ($recipientEmail = trim($this->stripTags($request->getPost('recipient_email')))) {
                        $this->validateEmailAddress(preg_split('/\r\n|[\r\n]/', $recipientEmail), 'Recipient Email');
                        $message = $this->stripTags($request->getPost('message'));
                        $result = $this->prepareSenderData($request);
                        if (isset($result['error'])) {
                            return $this->sendResponse($result);
                        }
                        $response = $this->shareCartApi->sendEmail(
                            $this->senderName,
                            $this->senderEmail,
                            $recipientEmail,
                            $message,
                            null,
                            $this->getCustomerId()
                        );
                    } else {
                        $response['message'] = __('Recipient Email is required. Please retry.');
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

<?php
namespace RedChamps\ShareCart\Controller\Action;

class WhatsApp extends Base
{
    public function execute()
    {
        $response = [];
        $response['error'] = true;
        try {
            if ($this->formKeyValidator->validate($this->getRequest()) && $this->_validateCaptcha()) {
                if ($this->isAllowed()) {
                    $request = $this->getRequest();
                    $result = $this->prepareSenderData($request);
                    if (isset($result['error'])) {
                        return $this->sendResponse($result);
                    }
                    $response = $this->shareCartApi->whatsApp(
                        $this->senderName,
                        $this->senderEmail,
                        null,
                        $this->getCustomerId()
                    );
                } else {
                    $response['message'] = __('Sorry, this action is not allowed.');
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

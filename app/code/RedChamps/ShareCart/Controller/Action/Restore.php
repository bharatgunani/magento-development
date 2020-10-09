<?php
namespace RedChamps\ShareCart\Controller\Action;

class Restore extends Base
{
    public function execute()
    {
        if ($uniqueId = $this->getRequest()->getParam('unique_id')) {
            $session = $this->getSession();
            $result = $this->shareCartApi->restore($uniqueId, $this->fromAdmin());
            if (isset($result['success'])) {
                $redirectToCheckout = $this->shareCartHelper->getGeneralConfig('checkout_redirect');
                if (!$this->fromAdmin() && !$redirectToCheckout) {
                    $session->addSuccessMessage($result['success']);
                }
                $redirectPath = 'checkout/cart';
                if ($redirectToCheckout) {
                    $redirectPathConfig = $this->shareCartHelper->getGeneralConfig('checkout_path');
                    if ($redirectPathConfig) {
                        $redirectPath = $redirectPathConfig;
                    }
                }
                return $this->_redirect($redirectPath, ['_query' => $this->getGaParams()]);
            } elseif (isset($result['error'])) {
                $session->addErrorMessage($result['error']);
            }
        }
        return $this->_redirect('/');
    }

    protected function fromAdmin()
    {
        return ($this->getRequest()->getParam('source') == "admin");
    }

    protected function getGaParams()
    {
        $params = [];
        if ($utmSource = $this->getRequest()->getParam('utm_source')) {
            $params['utm_source'] = $utmSource;
        }
        if ($utmMedium = $this->getRequest()->getParam('utm_medium')) {
            $params['utm_medium'] = $utmMedium;
        }
        if ($utmCampaign = $this->getRequest()->getParam('utm_campaign')) {
            $params['utm_campaign'] = $utmCampaign;
        }
        return $params;
    }
}

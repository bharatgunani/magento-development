<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Controller\Adminhtml\Option\Value\Gallery;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use MageWorx\OptionFeatures\Helper\Image as ImageHelper;

class Upload extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        ImageHelper $imageHelper
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        try {
            if ($this->getRequest()->getPost('hex')) {
                $result = $this->imageHelper->createColorFile($this->getRequest()->getPost('hex'));
            } else {
                $result = $this->imageHelper->createImageFile($this);
            }
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));

        return $response;
    }
}

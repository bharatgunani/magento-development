<?php
/**
 * Created by RedChamps.
 * User: rav
 * Date: 2019-01-17
 * Time: 12:08
 */
namespace RedChamps\Core\Controller\Adminhtml\System\Message;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\ResultFactory;
use RedChamps\Core\Model\Processor;

/*
 * Package: GuestOrders
 * Class: MarkRead
 * Company: RedChamps
 * author: rav(rav@redchamps.com)
 * */
class MarkRead extends AbstractAction
{
    protected $configWriter;

    protected $cacheManager;

    /**
     * MarkRead constructor.
     * @param Action\Context $context
     * @param WriterInterface $configWriter
     * @param CacheManager $manager
     */
    public function __construct(
        Action\Context $context,
        WriterInterface $configWriter,
        CacheManager $manager
    ) {
        $this->configWriter = $configWriter;
        $this->cacheManager = $manager;
        parent::__construct($context);
    }

    public function execute()
    {
        $extension = $this->getRequest()->getParam('extension');
        $version = $this->getRequest()->getParam('version');
        if ($extension && $version) {
            $this->configWriter->save(Processor::XML_BASE_CONFIG_PATH . $extension, $version);
            $this->cacheManager->clean(['config']);
            $this->messageManager->addSuccessMessage("Successfully Marked as Read.");
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}

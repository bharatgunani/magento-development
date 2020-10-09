<?php
namespace RedChamps\ShareCart\Controller\Adminhtml\Action\Report;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends Action
{
    protected $_fileFactory;

    public function __construct(
        Context $context,
        FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('RedChamps_ShareCart::report');
    }

    public function execute()
    {
        
            $fileName = 'shared_cart_conversions.csv';
            $content = $this->_view->getLayout()->createBlock(
                \RedChamps\ShareCart\Block\Adminhtml\Report\Grid::class
            )->getCsvFile();
            return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}

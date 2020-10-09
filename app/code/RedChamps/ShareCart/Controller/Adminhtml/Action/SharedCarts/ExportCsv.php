<?php
namespace RedChamps\ShareCart\Controller\Adminhtml\Action\SharedCarts;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \Magento\Backend\App\Action
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
        return $this->_authorization->isAllowed('RedChamps_ShareCart::shared_carts');
    }

    public function execute()
    {
        
            $fileName = 'shared_carts_list.csv';
            $content = $this->_view->getLayout()->createBlock(
                \RedChamps\ShareCart\Block\Adminhtml\SharedCarts\Content\Grid::class
            )->getCsvFile();
            return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}

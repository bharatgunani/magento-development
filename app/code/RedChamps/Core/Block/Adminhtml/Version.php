<?php
/**
 * Created by RedChamps.
 * User: rav
 * Date: 2018-12-20
 * Time: 14:41
 */
namespace RedChamps\Core\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\ModuleListInterface;
use RedChamps\Core\Model\Processor as Processor;

/*
 * Package: GuestOrders
 * Class: Version
 * Company: RedChamps
 * author: rav(rav@redchamps.com)
 * */
class Version extends Field
{
    /**
     * @var Processor
     */
    protected $processor;

    protected $messageManager;

    protected $moduleList;

    /**
     * @param ModuleListInterface $moduleList
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ModuleListInterface $moduleList,
        Processor $processor,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->processor = $processor;
        $this->moduleList = $moduleList;
    }

    /**
     * Return heading block html
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $extensionName = $element->getLegend()->getText();
        $extensionDetails = $this->processor->getExtensionVersion($extensionName);
        $html = "";
        if (isset($extensionDetails['update_needed']) && $extensionDetails['update_needed']) {
            $css = "padding:22px 12px 20px 34px;position: relative;margin:0 0 10px 0;background-color:#e9fbdb";
            $html .= "<div 
                        class='rc-update-notification'
                        style='{$css}'
                        >"
                        . __("New version")
                        . ' '
                        . __($extensionDetails['status_message'])
                        . ' '
                        . __($extensionDetails['notification_msg'])
                . '</div>';
        }

        return $html;
    }
}

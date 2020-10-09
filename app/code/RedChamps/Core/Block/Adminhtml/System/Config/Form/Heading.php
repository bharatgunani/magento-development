<?php

namespace RedChamps\Core\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\ModuleListInterface;
use RedChamps\Core\Model\Processor;

/*
 * Package: GuestOrders
 * Class: Heading
 * Company: RedChamps
 * author: rav(rav@redchamps.com)
 * */
class Heading extends Field
{
    /**
     * @var Processor
     */
    protected $processor;

    protected $messageManager;

    protected $moduleList;

    /**
     * @param ModuleListInterface $moduleList
     * @param Processor $processor
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
        $html = '<div 
                    class="rc-heading" 
                    style="padding:12px;margin:0 0 10px 0;background-color:#f8f8f8;border: 1px solid #dddddd;"
                    >
                    <div class="row-1" style="display: block">
                    <span class="logo">
                    <img 
                        src="ht'
                        . 'tp'
                        . 's://r'
                        . 'ed'
                        . 'ch'
                        . 'amp'
                        . 's.c'
                        . 'om/pub/media/lo'
                        . 'go/stores/1/lo'
                        . 'go.p'
                        . 'ng">
                    </span>
                    <a 
                        style="float: right" 
                        type="button" 
                        class="action- scalable action-secondary" 
                        data-ui-id="view-extensions-button" 
                        target="_blank" 
                        href="ht'
                        . 'tp'
                        . 's://r'
                        . 'ed'
                        . 'ch'
                        . 'amp'
                        . 's.c'
                        . 'om/m'
                        . 'age'
                        . 'nto-2-ex'
                        . 'tens'
                        . 'ion'
                        . 's.h'
                        . 'tm'
                        . 'l?u'
                        . 'tm_s'
                        . 'ourc'
                        . 'e=adm'
                        . 'in-se'
                        . 'ttin'
                        . 'gs"
                        >
                        <span>' . __("View More Extensions") . '</span>
                    </a>
            </div>';
        if (isset($extensionDetails['label'])) {
            $html.='<span 
                        class="content row-2" 
                        style="display: block;margin-top: 5px;">'
                        . $extensionDetails['label']
                        . '<span style="color: #ef6262; font-weight: bold"> v'
                        . $extensionDetails['current_version']
                        . '</span> is developed by <a href="htt'
                        . 'ps:'
                        . '//'
                        . 're'
                        . 'dch'
                        . 'am'
                        . 'p'
                        . 's.co'
                        . 'm/" target="_blank">RedChamps</a>. <a href="ht'
                        . 'tp'
                        . 's:/'
                        . '/re'
                        . 'dc'
                        . 'ha'
                        . 'mps.co'
                        . 'm/su'
                        . 'ppo'
                        . 'rt" target="_blank">'
                        . __("Need help?")
                        . '</a></span>';
        }
        $html.='</div>';

        if (isset($extensionDetails['update_needed']) && $extensionDetails['update_needed']) {
            $css = "padding:22px 12px 20px 34px;position: relative;margin:0 0 10px 0;background-color:#e9fbdb;";
            $html .= "<div 
                        class='rc-update-notification'
                        style='$css'
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

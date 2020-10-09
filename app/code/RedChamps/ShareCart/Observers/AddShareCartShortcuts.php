<?php
namespace RedChamps\ShareCart\Observers;

use RedChamps\ShareCart\Block\MiniCart\Button;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddShareCartShortcuts
 */
class AddShareCartShortcuts implements ObserverInterface
{
    /**
     * Block class
     */
    const SHARE_CART_SHORTCUT_BLOCK = Button::class;

    /**
     * Add Share Cart shortcut buttons
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Remove button from catalog
        if ($observer->getData('is_catalog_product')) {
            return;
        }

        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        $shortcut = $shortcutButtons->getLayout()->createBlock(self::SHARE_CART_SHORTCUT_BLOCK);

        $shortcutButtons->addShortcut($shortcut);
    }
}

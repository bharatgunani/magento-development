<?php
/**
 * Created by PhpStorm.
 * User: rav
 * Date: 24/11/18
 * Time: 10:57 AM
 */
namespace RedChamps\ShareCart\Plugin;

class CleanExpiredQuotes
{
    public function beforeExecute(\Magento\Sales\Cron\CleanExpiredQuotes $subject)
    {
        $subject->setExpireQuotesAdditionalFilterFields(
            ['is_shared_cart' => 0]
        );
    }
}

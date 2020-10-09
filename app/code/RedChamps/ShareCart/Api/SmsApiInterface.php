<?php
namespace RedChamps\ShareCart\Api;

interface SmsApiInterface
{
    /**
     * share cart via sms.
     *
     * @api
     *
     * @param string $senderName
     *
     * @param string $senderEmail
     *
     * @param string $recipientNumber
     *
     * @param int $quoteId
     *
     * @param int|null $customerId
     *
     * @return string
     */
    public function sendSms($senderName, $senderEmail, $recipientNumber, $quoteId, $customerId = null);
}

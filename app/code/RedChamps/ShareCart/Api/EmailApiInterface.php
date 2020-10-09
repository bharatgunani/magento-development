<?php
namespace RedChamps\ShareCart\Api;

interface EmailApiInterface
{
    /**
     * share cart via email.
     *
     * @api
     *
     * @param string $senderName
     *
     * @param string $senderEmail
     *
     * @param string $recipientEmail
     *
     * @param string|null $message
     *
     * @param int $quoteId
     *
     * @param int|null $customerId
     *
     * @return string $result
     */
    public function sendEmail($senderName, $senderEmail, $recipientEmail, $message, $quoteId, $customerId = null);
}

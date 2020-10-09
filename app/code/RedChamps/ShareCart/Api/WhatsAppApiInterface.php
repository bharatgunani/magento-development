<?php
namespace RedChamps\ShareCart\Api;

interface WhatsAppApiInterface
{
    /**
     * share cart via whatsApp.
     *
     * @api
     *
     * @param string $senderName
     *
     * @param string $senderEmail
     *
     * @param int $quoteId
     *
     * @param int|null $customerId
     *
     * @return mixed[]
     */
    public function whatsApp($senderName, $senderEmail, $quoteId, $customerId = null);
}

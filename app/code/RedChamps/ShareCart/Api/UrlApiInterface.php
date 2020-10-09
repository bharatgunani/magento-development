<?php
namespace RedChamps\ShareCart\Api;

interface UrlApiInterface
{
    /**
     * share cart via url.
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
    public function getUrl($senderName, $senderEmail, $quoteId, $customerId = null);

}

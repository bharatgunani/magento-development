<?php
namespace RedChamps\ShareCart\Model;

use Magento\Framework\HTTP\Client\Curl;

class GoogleShortner
{
    protected $curl;

    protected $helper;

    public function __construct(
        Curl $curl,
        ConfigManager $shareCartHelper
    ) {
        $this->curl = $curl;
        $this->helper = $shareCartHelper;
    }

    public function shortenUrl($longUrl)
    {
        $shortUrlEnabled= $this->helper->getUrlShortenerConfig('enabled');
        $accessToken = $this->helper->getUrlShortenerConfig('api_key');
        if ($shortUrlEnabled && $accessToken) {
            $apiURL = "https://www.googleapis.com/urlshortener/v1/url?key=$accessToken";
            try {
                $this->curl->setOption(CURLOPT_URL, $apiURL);
                $this->curl->setOption(CURLOPT_POST, 1);
                $this->curl->setOption(CURLOPT_POSTFIELDS, json_encode(["longUrl"=>$longUrl]));
                $this->curl->setOption(CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                $this->curl->setOption(CURLOPT_RETURNTRANSFER, 1);
                // Execute the post
                $this->curl->get($apiURL);
                $result = $this->curl->getBody();
                $output = json_decode($result);
                if (isset($output)) {
                    return $output->id;
                }
            } catch (\Exception $e) {
                return $longUrl;
            }
        }
        return $longUrl;
    }
}

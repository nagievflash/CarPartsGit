<?php

namespace App\Helpers;

use App\Helpers\Ebay\AddFixedPriceItem;
use App\Helpers\Ebay\GetCompatibilityEnginesFromEbay;
use App\Helpers\Ebay\GetCompatibilityTrimsFromEbay;
use App\Helpers\Ebay\GetItem;
use App\Helpers\Ebay\GetSuggestedCategories;
use App\Helpers\Ebay\RenderImageSpecifications;
use App\Helpers\Ebay\ReviseFixedPriceItem;
use App\Helpers\Ebay\RemoveItemCompatibility;
use App\Helpers\Ebay\ReviseFixedPriceItemImages;
use App\Helpers\Ebay\UpdateInventoryPricing;
use App\Helpers\Ebay\ReviseFixedPriceItemAllModels;
use App\Models\Shop;
use App\Models\Setting;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class EbayHelper {

    use AddFixedPriceItem,
        ReviseFixedPriceItem,
        RemoveItemCompatibility,
        UpdateInventoryPricing,
        GetCompatibilityTrimsFromEbay,
        RenderImageSpecifications,
        GetCompatibilityEnginesFromEbay,
        GetSuggestedCategories,
        GetItem,
        ReviseFixedPriceItemAllModels,
        ReviseFixedPriceItemImages;

    private string $url;

    private string $access_token;

    private $headers;

    private Shop $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;

        $this->headers = [
            "Content-Type"                      => "content-type: application/xml; charset=UTF-8",
            "X-EBAY-API-APP-NAME"               => "fastdeal-autoelem-PRD-4f2fb35bc-cbb0b166",
            "X-EBAY-API-DEV-NAME"               => "f4927169-5c25-41f4-9751-3c7455b20912",
            "X-EBAY-API-CERT-NAME"              => "PRD-f2fb35bc9102-6d45-460b-a53a-aa4a",
            "X-EBAY-API-SITEID"                 => 0,
            "X-EBAY-API-COMPATIBILITY-LEVEL"    => 967,
            "X-EBAY-API-CALL-NAME"              => "",
            "X-EBAY-API-DETAIL-LEVEL"           => "0",
            "Transfer-Encoding"                 => 'chunked'
        ];

        $this->url = 'https://api.ebay.com/ws/api.dll';

        $this->access_token = Setting::getAccessToken();
    }


    /**
     * Method for sending a request to Ebay
     * @param $body
     * @return PromiseInterface|Response
     * @throws Exception
     */
    public function sendRequest($body): PromiseInterface|Response
    {
        return Http::withHeaders($this->headers)->send('POST', $this->url, [
            'body' => $body
        ]);
    }
}

<?php

namespace App\Helpers\Ebay;

use XMLWriter;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

trait GetSuggestedCategories
{
    /**
     * Request for getting suggested categories by title from Ebay
     * @param string $query
     * @return PromiseInterface|Response
     * @throws Exception
     */
    public function getSuggestedCategories(string $query): PromiseInterface|Response
    {
        $this->headers["X-EBAY-API-CALL-NAME"] = 'GetSuggestedCategories';

        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startDocument('1.0', 'utf-8');
        $xmlWriter->startElement('GetSuggestedCategoriesRequest');
        $xmlWriter->writeAttribute('xmlns', "urn:ebay:apis:eBLBaseComponents");
        $xmlWriter->startElement('RequesterCredentials');
        $xmlWriter->writeElement('eBayAuthToken', $this->shop->token);
        $xmlWriter->endElement();
        $xmlWriter->writeElement('Query', $query);
        $xmlWriter->endElement();
        $xmlWriter->endDocument();
        return $this->sendRequest($xmlWriter->outputMemory());
    }
}

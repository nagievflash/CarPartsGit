<?php

namespace App\Helpers\Ebay;

use XMLWriter;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;

trait GetItem
{
    /**
     * GetItem Ebay
     * @param string $ebay_id
     * @return PromiseInterface|Response
     * @throws Exception
     */
    public function getItem(string $ebay_id): PromiseInterface|Response
    {
        $this->headers["X-EBAY-API-CALL-NAME"] = 'GetItem';

        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startDocument('1.0', 'utf-8');
        $xmlWriter->startElement('GetItemRequest');
        $xmlWriter->writeAttribute('xmlns', "urn:ebay:apis:eBLBaseComponents");
        $xmlWriter->startElement('RequesterCredentials');
        $xmlWriter->writeElement('eBayAuthToken', $this->shop->token);
        $xmlWriter->endElement();
        $xmlWriter->writeElement('ItemID', $ebay_id);
        $xmlWriter->writeElement('IncludeItemCompatibilityList', true);
        $xmlWriter->endElement();
        $xmlWriter->endDocument();
        return $this->sendRequest($xmlWriter->outputMemory());
    }
}

<?php

namespace App\Helpers\Ebay;

use App\Models\EbayListing;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use XMLWriter;
use Exception;

trait RemoveItemCompatibility
{

    /**
     * Method for revise fixed price item at Ebay
     * @param EbayListing $listing
     * @return PromiseInterface|Response|string
     * @throws Exception
     */
    public function removeItemCompatibility(EbayListing $listing): PromiseInterface|string|Response
    {
        $response = $this->getItem($listing->ebay_id);
        $body = simplexml_load_string($response->body());
        $this->headers["X-EBAY-API-CALL-NAME"] = 'ReviseFixedPriceItem';
        $this->headers["X-EBAY-API-SITEID"] = '100';

        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startDocument('1.0', 'utf-8');
        $xmlWriter->startElement('ReviseFixedPriceItemRequest');
        $xmlWriter->writeAttribute('xmlns', "urn:ebay:apis:eBLBaseComponents");
        $xmlWriter->startElement('RequesterCredentials');
        $xmlWriter->writeElement('eBayAuthToken', $this->shop->token);
        $xmlWriter->endElement();
        $xmlWriter->writeElement('ErrorLanguage', 'en_US');
        $xmlWriter->writeElement('WarningLevel', 'High');

        // Start Item
        $xmlWriter->startElement('Item');
        $xmlWriter->writeElement('Location', 'NV CA PA IL TX FL');
        $xmlWriter->writeElement('ItemID', $listing->ebay_id);
        $xmlWriter->writeElement('SKU', $listing->product->sku);
        $xmlWriter->startElement('ItemCompatibilityList');
        $xmlWriter->startElement('Compatibility');
        $xmlWriter->writeElement('Delete', 'true');
        $xmlWriter->endElement();
        $xmlWriter->writeElement('ReplaceAll', 'true');
        $xmlWriter->endElement();
        $xmlWriter->endElement();
        // End Item

        $xmlWriter->endElement();
        $xmlWriter->endDocument();
        try {
            $response = $this->sendRequest($xmlWriter->outputMemory());
        }
        catch (Exception $e) {
            return 'Error while sending request to Ebay API';
        }

        return $response;
    }
}

<?php

namespace App\Helpers\Ebay;

use App\Models\Backlog;
use App\Models\EbayListing;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use XMLWriter;
use Exception;

trait UpdateInventoryPricing
{

    /**
     * Method for update listing inventory pricing fixed price item at Ebay
     * @param EbayListing $listing
     * @return PromiseInterface|Response|string
     */
    public function updateInventoryPricing(EbayListing $listing): PromiseInterface|string|Response
    {
        $this->headers["X-EBAY-API-CALL-NAME"] = 'ReviseFixedPriceItem';
        $this->headers["X-EBAY-API-SITEID"] = '100';

        $price = $listing->getPrice();
        $stock = ($listing->getQuantity() - $this->shop->qty_reserve) > 0 ? $listing->getQuantity() - $this->shop->qty_reserve : 0;
        if ($stock > $this->shop->max_qty) $stock = $this->shop->max_qty;

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
        $xmlWriter->writeElement('ItemID', $listing->ebay_id);
        $xmlWriter->writeElement('Quantity', $stock);
        if ($stock != 0) $xmlWriter->writeElement('StartPrice', $price);

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
        Backlog::createBacklog('update pricing', $listing->ebay_id);
        return $response;
    }
}

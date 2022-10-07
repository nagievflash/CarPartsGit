<?php

namespace App\Helpers\Ebay;

use App\Models\Attribute;
use App\Models\Backlog;
use App\Models\Compatibility;
use App\Models\EbayListing;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use XMLWriter;
use Exception;

trait ReviseFixedPriceItemAllModels
{
    /**
     * Method for revise fixed price item at Ebay
     * @param EbayListing $listing
     * @return PromiseInterface|Response|string
     * @throws Exception
     */
    public function ReviseFixedPriceItemAllModels(EbayListing $listing): PromiseInterface|string|Response
    {
        $response = $this->getItem($listing->ebay_id);
        $body = (array)simplexml_load_string($response->body());
        $categoryID = 0;
        $itemsFromEbay = '';
        if ($body['Item']) {
            $itemsFromEbay = $body['Item'];
            $xml = $itemsFromEbay->PrimaryCategory->CategoryID;
            $categoryID = $xml[0];

            $this->headers["X-EBAY-API-CALL-NAME"] = 'ReviseFixedPriceItem';
            $this->headers["X-EBAY-API-SITEID"] = '100';

            $fitments = Compatibility::select('id', 'sku', 'year', 'make_name', 'model_name', 'submodel_name', 'bodytypename', 'liter', 'position')->where('sku', $listing->product->sku)->get();
            $attributes = Attribute::where('sku', $listing->product->sku)->get();

            $positions = $fitments->where('position', '!=', '')->unique('position')->implode('position', ', ');

            $result = $fitments->groupBy(['submodel_name']);
            $fits = array();
            foreach ($result as $items) {
                foreach ($items as $item) {
                    $fits[] = array('name' => $item->make_name . ' ' . $item->model_name . ' ' . $item->submodel_name, 'year' => $item->year );
                }
            }
            $fitmentItems = array();
            $collection = collect($fits);
            foreach ($collection->groupBy('name') as $item) {
                $fitmentItems[] = count($item) > 1 ? $item[0]['name'] . ' ' . $item[0]['year'] . '-' . $item[count($item) - 1]['year'] : $item[0]['name'] . ' ' . $item[0]['year'];
            }
            rsort($fitmentItems);

            $price = $listing->product->price + $listing->product->price  * $this->shop->percent / 100;
            $stock = ($listing->product->qty - $this->shop->qty_reserve) > 0 ? $listing->product->qty - $this->shop->qty_reserve : 0;
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
            $xmlWriter->writeElement('Location', 'NV CA PA IL TX FL');
            $xmlWriter->writeElement('ItemID', $listing->ebay_id);
            $xmlWriter->writeElement('SKU', $listing->product->sku);
            $xmlWriter->writeElement('Quantity', $stock);
            $xmlWriter->writeElement('StartPrice', $price);

            // Start ItemSpecifics
            $xmlWriter->startElement('ItemCompatibilityList');
            foreach ($fitments as $item) {
                $year = $item->year;
                $make = $item->make_name;
                $model = $item->model_name;
                $xmlWriter->startElement('Compatibility');
                $xmlWriter->writeElement('CompatibilityNotes', '');
                $xmlWriter->startElement('NameValueList');
                $xmlWriter->writeElement('Name', 'Year');
                $xmlWriter->writeElement('Value', $year);
                $xmlWriter->endElement();
                $xmlWriter->startElement('NameValueList');
                $xmlWriter->writeElement('Name', 'Make');
                $xmlWriter->writeElement('Value', $make);
                $xmlWriter->endElement();
                $xmlWriter->startElement('NameValueList');
                $xmlWriter->writeElement('Name', 'Model');
                $xmlWriter->writeElement('Value', $model);
                $xmlWriter->endElement();
                $xmlWriter->endElement();
            }


            $xmlWriter->endElement();
            // End ItemSpecifics


            // Start ItemSpecifics
            $xmlWriter->startElement('ItemSpecifics');
            $xmlWriter->startElement('NameValueList');
            $xmlWriter->writeElement('Name', 'Brand');
            $xmlWriter->writeElement('Value', $this->shop->title);
            $xmlWriter->endElement();
            if ($listing->product->oem_number) {
                $xmlWriter->startElement('NameValueList');
                $xmlWriter->writeElement('Name', 'OE/OEM Part Number');
                $xmlWriter->writeElement('Value', $listing->product->oem_number);
                $xmlWriter->endElement();
            }
            if ($listing->product->partslink) {
                $xmlWriter->startElement('NameValueList');
                $xmlWriter->writeElement('Name', 'Manufacturer Part Number');
                $xmlWriter->writeElement('Value', $listing->product->partslink);
                $xmlWriter->endElement();
            }
            if ($attributes->count() > 0) {
                foreach ($attributes as $attribute) {
                    if ($attribute->name != 'Prop 65 Warning' && $attribute->name != 'Warranty') {
                        if (strlen($attribute->value) < 70) {
                            $xmlWriter->startElement('NameValueList');
                            $xmlWriter->writeElement('Name', $attribute->name);
                            $xmlWriter->writeElement('Value', $attribute->value);
                            $xmlWriter->endElement();
                        }
                    }
                }
            }
            $xmlWriter->startElement('NameValueList');
            $xmlWriter->writeElement('Name', 'Warranty');
            if ($this->shop->slug == 'ebay3') $xmlWriter->writeElement('Value', '1-year warranty');
            if ($this->shop->slug == 'ebay4') $xmlWriter->writeElement('Value', '3-year warranty');
            $xmlWriter->endElement();

            if ($positions != '') {
                $xmlWriter->startElement('NameValueList');
                $xmlWriter->writeElement('Name', 'Placement on Vehicle');
                $xmlWriter->writeElement('Value', $positions);
                $xmlWriter->endElement();
            }

            $xmlWriter->endElement();
            // End ItemSpecifics

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
        else {
            Backlog::createBacklog("reviseItem", "Error while update listings ". $listing->sku);
            return $response;
        }
    }
}

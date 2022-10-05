<?php

namespace App\Helpers\Ebay;

use App\Models\Attribute;
use App\Models\Backlog;
use App\Models\Compatibility;
use App\Models\EbayListing;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use XMLWriter;
use Exception;

trait ReviseFixedPriceItem
{

    /**
     * Method for revise fixed price item at Ebay
     * @param EbayListing $listing
     * @return PromiseInterface|Response|string
     * @throws Exception
     */
    public function reviseFixedPriceItem(EbayListing $listing): PromiseInterface|string|Response
    {
        $response = $this->getItem($listing->ebay_id);
        $body = simplexml_load_string($response->body());
        $categoryID = 0;
        if ((array)$body->Item[0]) {
            $xml = (array)$body->Item[0]->PrimaryCategory->CategoryID;
            $categoryID = $xml[0];
        }
        $this->headers["X-EBAY-API-CALL-NAME"] = 'ReviseFixedPriceItem';
        $this->headers["X-EBAY-API-SITEID"] = '100';

        $fitments = Compatibility::select('id', 'sku', 'year', 'make_name', 'model_name', 'submodel_name', 'bodytypename', 'liter')->where('sku', $listing->product->sku)->get();
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
        $submodelArray = array();
        $engineArray = array();
        foreach ($fitments as $item) {
            $year = $item->year;
            $make = $item->make_name;
            $model = $item->model_name;
            $compatibilityNotes = $fitments->where('make_name', $make)->where('year', $year)->where('model_name', $model)->first();
            $notes = 'For';
            $notes .= ' ' . $compatibilityNotes->make_name . ' ' . $compatibilityNotes->model_name;
            if ($compatibilityNotes->position) $notes .= ' ' . $compatibilityNotes->position;


            if ($item->submodel_name) {
                if (!isset($submodelArray[$item->sku. '_'. $item->submodel_name]))  $submodelArray[$item->sku. '_'. $item->submodel_name] = $this->getCompatibilityTrimsFromEbay($categoryID, $year, $make, $model);

                dd($this->getCompatibilityTrimsFromEbay($categoryID, $year, $make, $model));
                if ($submodelArray[$item->sku. '_'. $item->submodel_name]) {
                    foreach ($submodelArray[$item->sku. '_'. $item->submodel_name] as $fit) {
                        $trim = '';
                        if ($item->bodytypename != "") {
                            if (preg_match("/\b".$item->submodel_name."\b/i", $fit["value"]) && preg_match("/\b".$item->bodytypename."\b/i", $fit["value"])) {
                                $trim = $fit["value"];
                            }
                        }
                        else {
                            if (preg_match("/\b".$item->submodel_name."\b/i", $fit["value"])) {
                                $trim = $fit["value"];
                            }
                        }
                        if ($trim != '')  {
                            if ($item->liter) {
                                if (!isset($engineArray[$item->sku . '_' . $item->liter]))  $engineArray[$item->sku . '_' . $item->liter] = $this->getCompatibilityEnginesFromEbay($categoryID, $year, $make, $model);

                                if ($engineArray[$item->sku . '_' . $item->liter]) {
                                    foreach ($engineArray[$item->sku . '_' . $item->liter] as $e) {
                                        if ($item->liter != "") {
                                            if (Str::contains($e["value"], $item->liter.'L')) {
                                                $engine = $e["value"];
                                                $xmlWriter->startElement('Compatibility');
                                                $xmlWriter->writeElement('CompatibilityNotes', $notes. ' ' . $trim . ' ' . $engine);
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
                                                $xmlWriter->startElement('NameValueList');
                                                $xmlWriter->writeElement('Name', 'Trim');
                                                $xmlWriter->writeElement('Value', $trim);
                                                $xmlWriter->endElement();
                                                $xmlWriter->startElement('NameValueList');
                                                $xmlWriter->writeElement('Name', 'Engine');
                                                $xmlWriter->writeElement('Value', $engine);
                                                $xmlWriter->endElement();
                                                $xmlWriter->endElement();
                                            }
                                        }
                                    }
                                }
                            }
                            else {
                                $xmlWriter->startElement('Compatibility');
                                $xmlWriter->writeElement('CompatibilityNotes', $notes. ' ' . $trim);
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
                                $xmlWriter->startElement('NameValueList');
                                $xmlWriter->writeElement('Name', 'Trim');
                                $xmlWriter->writeElement('Value', $trim);
                                $xmlWriter->endElement();
                                $xmlWriter->endElement();
                            }
                        }
                    }
                }
                else {
                    $xmlWriter->startElement('Compatibility');
                    $xmlWriter->writeElement('CompatibilityNotes', $notes);
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

                    Backlog::createBacklog('Trim Error while revising an item', $item->sku);
                }
            }
            else {
                if ($item->liter) {

                    if (!isset($engineArray[$item->sku . '_' . $item->liter]))  $engineArray[$item->sku . '_' . $item->liter] = $this->getCompatibilityEnginesFromEbay($categoryID, $year, $make, $model);
                    if ($engineArray[$item->sku . '_' . $item->liter]) {
                        foreach ($engineArray[$item->sku . '_' . $item->liter] as $e) {
                            if (Str::contains($e["value"], $item->liter.'L')) {
                                $engine = $e["value"];
                                $xmlWriter->startElement('Compatibility');
                                $xmlWriter->writeElement('CompatibilityNotes', $notes. ' ' . $engine);
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
                                $xmlWriter->startElement('NameValueList');
                                $xmlWriter->writeElement('Name', 'Engine');
                                $xmlWriter->writeElement('Value', $engine);
                                $xmlWriter->endElement();
                                $xmlWriter->endElement();
                            }
                        }
                    }
                }
                else {
                    $xmlWriter->startElement('Compatibility');
                    $xmlWriter->writeElement('CompatibilityNotes', $notes);
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
            }
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
}

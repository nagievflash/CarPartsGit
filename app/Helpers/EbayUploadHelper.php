<?php

namespace App\Helpers;

use App\Models\Attribute;
use App\Models\Backlog;
use App\Models\EbayListing;
use App\Models\Compatibility;
use App\Models\Fitment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Shop;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use XMLWriter;

class EbayUploadHelper
{
    private string $url;
    private string $access_token;

    private array $headers;


    private Shop $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;

        $this->headers = array(
            "Content-Type"                      => "content-type: application/xml; charset=UTF-8",
            "X-EBAY-API-APP-NAME"               => "fastdeal-autoelem-PRD-4f2fb35bc-cbb0b166",
            "X-EBAY-API-DEV-NAME"               => "f4927169-5c25-41f4-9751-3c7455b20912",
            "X-EBAY-API-CERT-NAME"              => "PRD-f2fb35bc9102-6d45-460b-a53a-aa4a",
            "X-EBAY-API-SITEID"                 => 0,
            "X-EBAY-API-COMPATIBILITY-LEVEL"    => 967,
            "X-EBAY-API-CALL-NAME"              => "",
            "X-EBAY-API-DETAIL-LEVEL"           => "0",
            "Transfer-Encoding"                 => 'chunked'
        );

        $this->url = 'https://api.ebay.com/ws/api.dll';

        if (Cache::has('access_token')) {
            $this->access_token = Cache::get('access_token');
        }
        else {
            $oauthToken = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic '.base64_encode('fastdeal-autoelem-PRD-4f2fb35bc-cbb0b166:PRD-f2fb35bc9102-6d45-460b-a53a-aa4a'),
            ])->send('POST', 'https://api.ebay.com/identity/v1/oauth2/token', [
                'form_params' => [
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => Setting::where('key', 'ebay_refresh_token')->first()->value,
                    'redirect_uri'  => 'fastdeal24-fastdeal-autoel-ymxyoese',
                ]
            ]);
            $this->access_token = $oauthToken->json()['access_token'];
            Cache::put('access_token', $oauthToken->json()['access_token'], 7100);
        }
    }




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

        foreach ($fitments as $item) {
            $year = $item->year;
            $make = $item->make_name;
            $model = $item->model_name;
            $compatibilityNotes = $fitments->where('make_name', $make)->where('year', $year)->where('model_name', $model)->first();
            $notes = 'For';
            $notes .= ' ' . $compatibilityNotes->make_name . ' ' . $compatibilityNotes->model_name;
            if ($compatibilityNotes->position) $notes .= ' ' . $compatibilityNotes->position;


            if ($item->submodel_name) {
                $ebayTrims = $this->getCompatibilityTrimsFromEbay($categoryID, $year, $make, $model);
                if (is_array($ebayTrims)) {
                    $trim = '';
                    foreach ($ebayTrims as $fit) {
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
                                $ebayEngines = $this->getCompatibilityEnginesFromEbay($categoryID, $year, $make, $model, $trim);
                                if (is_array($ebayEngines)) {
                                    $engine = '';
                                    foreach ($ebayEngines as $fit) {
                                        if ($item->liter != "") {
                                            if (Str::contains($fit["value"], $item->liter.'L')) {
                                                $engine = $fit["value"];
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
                    if ($item->liter) {
                        $ebayEngines = $this->getCompatibilityEnginesFromEbay($categoryID, $year, $make, $model);
                        dd($ebayEngines);
                        if (is_array($ebayEngines)) {
                            $engine = '';
                            foreach ($ebayEngines as $fit) {
                                if (Str::contains($fit["value"], $item->liter.'L')) {
                                    $engine = $fit["value"];
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

    /**
     * Method for revise fixed price item at Ebay
     * @param EbayListing $listing
     * @return PromiseInterface|Response|string
     * @throws Exception
     */
    public function removeItemCompatibility(EbayListing $listing): PromiseInterface|string|Response
    {
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

    /**
     * Method for update listing inventory pricing fixed price item at Ebay
     * @param EbayListing $listing
     * @return PromiseInterface|Response|string
     */
    public function updateInventoryPricing(EbayListing $listing): PromiseInterface|string|Response
    {
        $this->headers["X-EBAY-API-CALL-NAME"] = 'ReviseFixedPriceItem';
        $this->headers["X-EBAY-API-SITEID"] = '100';

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
        $xmlWriter->writeElement('ItemID', $listing->ebay_id);
        $xmlWriter->writeElement('SKU', $listing->product->sku);
        $xmlWriter->writeElement('Quantity', $stock);
        $xmlWriter->writeElement('StartPrice', $price);
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


    /**
     * Method for rendering image specifications from first Listing's image
     * @param $imageUrl
     * @param $type
     * @return string
     */
    public function renderImageSpecifications($imageUrl, $type): string
    {
        $contents = file_get_contents($imageUrl);
        $url = 'images/ebay/'. substr($imageUrl, strrpos($imageUrl, '/') + 1) . '_' . $type . '.jpg';
        file_put_contents(public_path($url), $contents);

        $img = Image::make(public_path($url));
        $watermark = Image::make(public_path('images/bg/watermark_'.$type.'.png'));
        $canvas = Image::canvas(1200, 1200);

        $img->resize(1200, 1200, function($constraint)
        {
            $constraint->aspectRatio();
        });

        if ($type == 'ebay4') $canvas->insert($img, 'center', 0, 100);
        else $canvas->insert($img, 'center', 0, 0);

        $canvas->insert($watermark, 'center');
        $canvas->save(public_path($url));

        return env('APP_URL') . '/' . $url;
    }

    /**
     * Get product fitments from Ebay
     * @param int $category_id
     * @param string $year
     * @param string $make
     * @param string $model
     * @return mixed
     * @throws Exception
     */
    public function getCompatibilityTrimsFromEbay(int $category_id, string $year, string $make, string $model): mixed
    {
        $headers = array();
        $headers["Authorization"] = 'Bearer ' . $this->access_token;
        $headers["Accept"] = 'application/json';
        $headers["Content-Type"] = 'application/json';
        $headers["Accept-Encoding"] = 'gzip';
        $url = 'https://api.ebay.com/commerce/taxonomy/v1/category_tree/100/get_compatibility_property_values?compatibility_property=Trim&category_id='.$category_id
            .'&filter=Year:'.$year.',Make:'.$make.',Model:'.$model;
        $response = Http::withHeaders($headers)->send('GET', $url);
        if ($response->json() != null) {
            return array_key_exists('compatibilityPropertyValues', $response->json()) ? $response->json()["compatibilityPropertyValues"] : false;
        }
        else die('Error while sending request');
    }

    /**
     * Get getCompatibilityEngines From Ebay
     * @param int $category_id
     * @param string $year
     * @param string $make
     * @param string $model
     * @param string|null $trim
     * @return mixed
     * @throws Exception
     */
    public function getCompatibilityEnginesFromEbay(int $category_id, string $year, string $make, string $model, string $trim = null): mixed
    {
        $headers = array();
        $headers["Authorization"] = 'Bearer ' . $this->access_token;
        $headers["Accept"] = 'application/json';
        $headers["Content-Type"] = 'application/json';
        $headers["Accept-Encoding"] = 'gzip';
        $url = 'https://api.ebay.com/commerce/taxonomy/v1/category_tree/100/get_compatibility_property_values?compatibility_property=Engine&category_id='.$category_id
            .'&filter=Year:'.$year.',Make:'.$make.',Model:'.$model.',Trim:'.$trim;
        if (!$trim) $url = 'https://api.ebay.com/commerce/taxonomy/v1/category_tree/100/get_compatibility_property_values?compatibility_property=Engine&category_id='.$category_id
            .'&filter=Year:'.$year.',Make:'.$make.',Model:'.$model;
        $response = Http::withHeaders($headers)->send('GET', $url);
        if ($response->json() != null) {
            return array_key_exists('compatibilityPropertyValues', $response->json()) ? $response->json()["compatibilityPropertyValues"] : false;
        }
        else die('Error while sending request');
    }


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
        $xmlWriter->endElement();
        $xmlWriter->endDocument();
        return $this->sendRequest($xmlWriter->outputMemory());
    }

}

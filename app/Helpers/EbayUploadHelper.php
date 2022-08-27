<?php

namespace App\Helpers;

use App\Models\Attribute;
use App\Models\Backlog;
use App\Models\EbayListing;
use App\Models\Fitment;
use App\Models\Product;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use XMLWriter;

class EbayUploadHelper
{
    private string $token;

    private string $url;

    private array $headers;

    public function __construct()
    {
        $this->token = 'v^1.1#i^1#p^3#I^3#r^1#f^0#t^Ul4xMF8yOjc0M0Q3OUNCNjJCNjRERjBENEY4RURDQzNENzc5NkVFXzJfMSNFXjI2MA==';

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
    }

    /**
     * Запрос на получение рекомендуемой категории
     * @param string $query
     * @return PromiseInterface|Response
     * @throws \Exception
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
        $xmlWriter->writeElement('eBayAuthToken', $this->token);
        $xmlWriter->endElement();
        $xmlWriter->writeElement('Query', $query);
        $xmlWriter->endElement();
        $xmlWriter->endDocument();

        return $this->sendRequest($xmlWriter->outputMemory());
    }

    /**
     * @param Product $product
     * @return PromiseInterface|Response|string
     * @throws \Exception
     */
    public function addFixedPriceItem(Product $product): PromiseInterface|string|Response
    {
        $title = $product->getTitle();
        $response = $this->getSuggestedCategories($title);
        $body = simplexml_load_string($response->body());
        $xml = (array)$body->SuggestedCategoryArray[0]->SuggestedCategory->Category->CategoryID;
        $categoryID = $xml[0];

        $fitments = Fitment::where('sku', $product->sku)->get();
        $attributes = Attribute::where('sku', $product->sku)->get();

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


        for ($i = 1; $i < 8; $i++) {
            $file = 'https://res.cloudinary.com/us-auto-parts-network-inc/image/upload/images/' . $product->sku . '_' . $i;
            $ch = curl_init($file);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode == 200) {
                $images[] = $file;
            } else break;
        }

        $images[0] = $this->renderImageSpecifications($images[0]);

        $product->images = implode(',', $images);
        $product->save();


        $template = View('ebay.templates.ebay4', [
            'title'         => $title,
            'fitments'      => $fitmentItems,
            'attributes'    => $attributes,
            'images'        => explode(',', $product->images)
        ])->render();

        $price = $product->price + $product->price / 4;
        $stock = ($product->qty - 2) > 0 ? $product->qty - 2 : 0;
        if ($stock > 7) $stock = 7;

        $this->headers["X-EBAY-API-CALL-NAME"] = 'AddFixedPriceItem';
        $this->headers["X-EBAY-API-SITEID"] = '100';

        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startDocument('1.0', 'utf-8');
        $xmlWriter->startElement('AddFixedPriceItemRequest');
        $xmlWriter->writeAttribute('xmlns', "urn:ebay:apis:eBLBaseComponents");
        $xmlWriter->startElement('RequesterCredentials');
        $xmlWriter->writeElement('eBayAuthToken', $this->token);
        $xmlWriter->endElement();
        $xmlWriter->writeElement('ErrorLanguage', 'en_US');
        $xmlWriter->writeElement('WarningLevel', 'High');

        // Start Item
        $xmlWriter->startElement('Item');
        $xmlWriter->writeElement('title', $title);  // Need Title with masking
        $xmlWriter->writeElement('SKU', $product->sku);  // Need Title with masking
        $xmlWriter->startElement('PrimaryCategory');
        $xmlWriter->writeElement('CategoryID', $categoryID); // Need Category ID
        $xmlWriter->endElement();
        $xmlWriter->writeElement('StartPrice', $price);
        $xmlWriter->writeElement('ConditionID', '1000');
        $xmlWriter->writeElement('CategoryMappingAllowed', 'true');
        $xmlWriter->writeElement('Country', 'US');
        $xmlWriter->writeElement('Currency', 'USD');
        $xmlWriter->startElement('Description');  // Need HTML Template
        $xmlWriter->text($template);
        $xmlWriter->endElement();
        $xmlWriter->writeElement('DispatchTimeMax', '3');
        $xmlWriter->writeElement('ListingDuration', 'GTC');
        $xmlWriter->writeElement('ListingType', 'FixedPriceItem');
        $xmlWriter->writeElement('Location', 'AZ CA NY FL TX WA');
        $xmlWriter->writeElement('PrivateListing', 'false');
        $xmlWriter->startElement('PictureDetails');
        $xmlWriter->writeElement('GalleryType', 'Gallery');
        $xmlWriter->writeElement('PhotoDisplay', 'PicturePack');

        foreach (explode(',', $product->images) as $image) {
            $xmlWriter->writeElement('PictureURL', $image); //NEED SOME URLS
        }

        $xmlWriter->endElement();
        $xmlWriter->writeElement('Quantity', $stock);
        $xmlWriter->startElement('ReservePrice');
        $xmlWriter->writeAttribute('currencyID', "USD");
        $xmlWriter->text($price);
        $xmlWriter->endElement();
        $xmlWriter->startElement('ReviseStatus');
        $xmlWriter->writeElement('ItemRevised', 'true');
        $xmlWriter->endElement();

        // Seller block
        $xmlWriter->startElement('Seller');
        $xmlWriter->writeElement('AboutMePage', 'false');
        $xmlWriter->writeElement('Email', 'motorelementss@gmail.com');
        $xmlWriter->writeElement('FeedbackScore', '134');
        $xmlWriter->writeElement('PositiveFeedbackPercent', '100.0');
        $xmlWriter->writeElement('FeedbackPrivate', 'false');
        $xmlWriter->writeElement('IDVerified', 'false');
        $xmlWriter->writeElement('eBayGoodStanding', 'true');
        $xmlWriter->writeElement('NewUser', 'false');
        $xmlWriter->writeElement('RegistrationDate', '2021-04-16T16:13:24.000Z');
        $xmlWriter->writeElement('Site', 'eBayMotors');
        $xmlWriter->writeElement('Status', 'Confirmed');
        $xmlWriter->writeElement('UserID', 'motor_elements');
        $xmlWriter->writeElement('UserIDChanged', 'false');
        $xmlWriter->writeElement('UserIDLastChanged', '2021-10-19T20:28:38.000Z');
        $xmlWriter->writeElement('VATStatus', 'NoVATTax');
        $xmlWriter->startElement('SellerInfo');
        $xmlWriter->writeElement('AllowPaymentEdit', 'true');
        $xmlWriter->writeElement('CheckoutEnabled', 'true');
        $xmlWriter->writeElement('CIPBankAccountStored', 'false');
        $xmlWriter->writeElement('GoodStanding', 'true');
        $xmlWriter->writeElement('LiveAuctionAuthorized', 'false');
        $xmlWriter->writeElement('MerchandizingPref', 'OptIn');
        $xmlWriter->writeElement('QualifiesForB2BVAT', 'false');
        $xmlWriter->writeElement('StoreOwner', 'true');
        $xmlWriter->writeElement('StoreURL', 'https://stores.ebay.com/motorelements');
        $xmlWriter->writeElement('SafePaymentExempt', 'false');
        $xmlWriter->endElement();
        $xmlWriter->writeElement('MotorsDealer', 'false');
        $xmlWriter->endElement();
        // End Seller Section

        // Start SellingStatus
        $xmlWriter->startElement('SellingStatus');
        $xmlWriter->writeElement('BidCount', '0');
        $xmlWriter->startElement('BidIncrement');
        $xmlWriter->writeAttribute('currencyID', "USD");
        $xmlWriter->text("0.0");
        $xmlWriter->endElement();

        $xmlWriter->startElement('ConvertedCurrentPrice');
        $xmlWriter->writeAttribute('currencyID', "USD");
        $xmlWriter->text("1.0");
        $xmlWriter->endElement();

        $xmlWriter->startElement('CurrentPrice');
        $xmlWriter->writeAttribute('currencyID', "USD");
        $xmlWriter->text($price);
        $xmlWriter->endElement();

        $xmlWriter->startElement('MinimumToBid');
        $xmlWriter->writeAttribute('currencyID', "USD");
        $xmlWriter->text("1.0");
        $xmlWriter->endElement();


        $xmlWriter->writeElement('LeadCount', '0');
        $xmlWriter->writeElement('QuantitySold', $stock);
        $xmlWriter->writeElement('ReserveMet', 'true');
        $xmlWriter->writeElement('SecondChanceEligible', 'false');
        $xmlWriter->writeElement('QuantitySoldByPickupInStore', '0');

        $xmlWriter->endElement();
        // End SellingStatus

        // Start ShippingDetails
        $xmlWriter->startElement('ShippingDetails');
        $xmlWriter->writeElement('ApplyShippingDiscount', 'false');
        $xmlWriter->startElement('SalesTax');
        $xmlWriter->writeElement('SalesTaxPercent', '0.0');
        $xmlWriter->writeElement('ShippingIncludedInTax', 'false');
        $xmlWriter->endElement();

        // Start ShippingServiceOptions
        $xmlWriter->startElement('ShippingServiceOptions');
        $xmlWriter->writeElement('ShippingService', 'FedExHomeDelivery');
        $xmlWriter->writeElement('ShippingServicePriority', '1');
        $xmlWriter->writeElement('ExpeditedService', 'false');
        $xmlWriter->writeElement('ShippingTimeMin', '1');
        $xmlWriter->writeElement('ShippingTimeMax', '5');
        $xmlWriter->writeElement('FreeShipping', 'true');
        $xmlWriter->endElement();
        // End ShippingServiceOptions

        $xmlWriter->writeElement('ThirdPartyCheckout', 'true');
        $xmlWriter->writeElement('ShippingDiscountProfileID', '0');
        $xmlWriter->writeElement('InternationalShippingDiscountProfileID', '0');
        $xmlWriter->writeElement('ExcludeShipToLocation', 'Alaska/Hawaii');
        $xmlWriter->writeElement('ExcludeShipToLocation', 'US Protectorates');
        $xmlWriter->writeElement('ExcludeShipToLocation', 'APO/FPO');
        $xmlWriter->writeElement('ExcludeShipToLocation', 'PO Box');
        $xmlWriter->writeElement('SellerExcludeShipToLocationsPreference', 'false');

        $xmlWriter->endElement();
        // End ShippingDetails

        // Start ReturnPolicy
        $xmlWriter->startElement('ReturnPolicy');
        $xmlWriter->writeElement('ReturnsWithinOption', 'Days_30');
        $xmlWriter->writeElement('ReturnsWithin', '30 Days');
        $xmlWriter->writeElement('ReturnsAcceptedOption', 'ReturnsAccepted');
        $xmlWriter->writeElement('ShippingCostPaidByOption', 'Buyer');
        $xmlWriter->writeElement('ShippingCostPaidBy', 'Buyer');
        $xmlWriter->writeElement('InternationalReturnsAcceptedOption', 'ReturnsNotAccepted');
        $xmlWriter->endElement();
        // End ReturnPolicy

        // Start SellerProfiles
        $xmlWriter->startElement('SellerProfiles');

        $xmlWriter->startElement('SellerShippingProfile');
        $xmlWriter->writeElement('ShippingProfileID', '224972567012');
        $xmlWriter->writeElement('ShippingProfileName', 'NEW Products Fedex 4 days (4 listings)');
        $xmlWriter->endElement();

        $xmlWriter->startElement('SellerReturnProfile');
        $xmlWriter->writeElement('ReturnProfileID', '209396756012');
        $xmlWriter->writeElement('ReturnProfileName', 'Returns Accepted,Buyer,30 Days,Money back or');
        $xmlWriter->endElement();

        $xmlWriter->startElement('SellerPaymentProfile');
        $xmlWriter->writeElement('PaymentProfileID', '209177521012');
        $xmlWriter->writeElement('PaymentProfileName', 'eBay Payments');
        $xmlWriter->endElement();

        $xmlWriter->endElement();
        // End SellerProfiles

        $xmlWriter->writeElement('ShipToLocations', 'US');

        // Start ItemSpecifics
        $xmlWriter->startElement('ItemCompatibilityList');
        foreach ($fitments as $fitment) {
            $xmlWriter->startElement('Compatibility');
            $xmlWriter->startElement('NameValueList');
            $xmlWriter->writeElement('Name', 'Year');
            $xmlWriter->writeElement('Value', $fitment->year);
            $xmlWriter->endElement();
            $xmlWriter->startElement('NameValueList');
            $xmlWriter->writeElement('Name', 'Make');
            $xmlWriter->writeElement('Value', $fitment->make_name);
            $xmlWriter->endElement();
            $xmlWriter->startElement('NameValueList');
            $xmlWriter->writeElement('Name', 'Model');
            $xmlWriter->writeElement('Value', $fitment->model_name);
            $xmlWriter->endElement();
            if ($fitment->submodel_name) {
                $xmlWriter->startElement('NameValueList');
                $xmlWriter->writeElement('Name', 'Submodel');
                $xmlWriter->writeElement('Value', $fitment->submodel_name);
                $xmlWriter->endElement();
            }
            $xmlWriter->endElement();
        }
        $xmlWriter->endElement();
        // End ItemSpecifics


        // Start ItemSpecifics
        $xmlWriter->startElement('ItemSpecifics');
        $xmlWriter->startElement('NameValueList');
        $xmlWriter->writeElement('Name', 'Brand');
        $xmlWriter->writeElement('Value', 'Motor Elements');
        $xmlWriter->endElement();
        if ($product->oem_number) {
            $xmlWriter->startElement('NameValueList');
            $xmlWriter->writeElement('Name', 'OE/OEM Part Number');
            $xmlWriter->writeElement('Value', $product->oem_number);
            $xmlWriter->endElement();
        }
        if ($product->partslink) {
            $xmlWriter->startElement('NameValueList');
            $xmlWriter->writeElement('Name', 'Manufacturer Part Number');
            $xmlWriter->writeElement('Value', $product->partslink);
            $xmlWriter->endElement();
        }
        if ($attributes->count() > 0) {
            foreach ($attributes as $attribute) {
                if ($attribute->name != 'Prop 65 Warning') {
                    if (strlen($attribute->value) < 70) {
                        $xmlWriter->startElement('NameValueList');
                        $xmlWriter->writeElement('Name', $attribute->name);
                        $xmlWriter->writeElement('Value', $attribute->value);
                        $xmlWriter->endElement();
                    }
                }
            }
        }
        $xmlWriter->endElement();
        // End ItemSpecifics

        $xmlWriter->endElement();
        // End Item

        $xmlWriter->endElement();
        $xmlWriter->endDocument();

        try {
            $response = $this->sendRequest($xmlWriter->outputMemory());
        } catch (\Exception $e) {
            return 'Error while sending request to Ebay API';
        }

        return $response;
    }

    /**
     * @param EbayListing $listing
     * @return PromiseInterface|Response|string
     */
    public function reviseFixedPriceItem(EbayListing $listing): PromiseInterface|string|Response
    {
        $this->headers["X-EBAY-API-CALL-NAME"] = 'ReviseFixedPriceItem';
        $this->headers["X-EBAY-API-SITEID"] = '100';

        $price = $listing->product->price + $listing->product->price / 4;
        $stock = ($listing->product->qty > 2) ? $listing->product->qty - 2 : 0;
        if ($stock > 7) $stock = 7;

        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startDocument('1.0', 'utf-8');
        $xmlWriter->startElement('ReviseFixedPriceItemRequest');
        $xmlWriter->writeAttribute('xmlns', "urn:ebay:apis:eBLBaseComponents");
        $xmlWriter->startElement('RequesterCredentials');
        $xmlWriter->writeElement('eBayAuthToken', $this->token);
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
        $xmlWriter->endElement();
        $xmlWriter->endDocument();

        try {
            $response = $this->sendRequest($xmlWriter->outputMemory());
        }
        catch (\Exception $e) {
            return 'Error while sending request to Ebay API';
        }

        return $response;
    }


    /**
     * Метод отправки запроса в Ebay
     * @param $body
     * @return PromiseInterface|Response
     * @throws \Exception
     */
    public function sendRequest($body): PromiseInterface|Response
    {
        return Http::withHeaders($this->headers)->send('POST', $this->url, [
            'body' => $body
        ]);
    }


    /**
     * Rendering Image Specifications from first Listing's image
     * @param $imageUrl
     * @return string
     */
    public function renderImageSpecifications($imageUrl): string
    {
        $contents = file_get_contents($imageUrl);
        $url = 'images/ebay/'. substr($imageUrl, strrpos($imageUrl, '/') + 1) . '.jpg';
        file_put_contents(public_path($url), $contents);

        $img = Image::make(public_path($url));
        $watermark = Image::make(public_path('images/bg/watermark.png'));
        $canvas = Image::canvas(1200, 1200);

        $img->resize(1200, 1200, function($constraint)
        {
            $constraint->aspectRatio();
        });

        $canvas->insert($img, 'center', 0, 100);
        $canvas->insert($watermark, 'center');
        $canvas->save(public_path($url));

        return env('APP_URL') . '/' . $url;
    }
}

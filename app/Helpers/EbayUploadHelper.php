<?php

namespace App\Helpers;

use App\Models\Attribute;
use App\Models\Backlog;
use App\Models\EbayListing;
use App\Models\Compatibility;
use App\Models\Fitment;
use App\Models\Product;
use App\Models\Shop;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use XMLWriter;

class EbayUploadHelper
{
    private string $url;

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
    }


    /**
     * Method for listing a fixed price item at Ebay
     * @param Product $product
     * @return PromiseInterface|Response|string
     * @throws Exception
     */
    public function addFixedPriceItem(Product $product): PromiseInterface|string|Response
    {
        $title = strlen($product->getTitle()) < 70 ? $product->getTitle() : $product->getTitleShort();
        $response = $this->getSuggestedCategories($title);
        $body = simplexml_load_string($response->body());
        if (count((array)$body->SuggestedCategoryArray[0]) != 0) {
            $xml = (array)$body->SuggestedCategoryArray[0]->SuggestedCategory->Category->CategoryID;
            $categoryID = $xml[0];

            $fitments = Compatibility::where('sku', $product->sku)->get();
            $attributes = Attribute::where('sku', $product->sku)->get();

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

            $images[0] = $this->renderImageSpecifications($images[0], $this->shop->slug);

            $product->images = implode(',', $images);
            $product->save();


            $template = View('ebay.templates.'.$this->shop->slug, [
                'title'         => $product->getTitle(),
                'fitments'      => $fitmentItems,
                'attributes'    => $attributes,
                'positions'      => $positions,
                'images'        => explode(',', $product->images)
            ])->render();

            $price = $product->price + $product->price  * $this->shop->percent / 100;
            $stock = ($product->qty - $this->shop->qty_reserve) > 0 ? $product->qty - $this->shop->qty_reserve : 0;
            if ($stock > $this->shop->max_qty) $stock = $this->shop->max_qty;

            $this->headers["X-EBAY-API-CALL-NAME"] = 'AddFixedPriceItem';
            $this->headers["X-EBAY-API-SITEID"] = '100';

            $xmlWriter = new XMLWriter();
            $xmlWriter->openMemory();
            $xmlWriter->startDocument('1.0', 'utf-8');
            $xmlWriter->startElement('AddFixedPriceItemRequest');
            $xmlWriter->writeAttribute('xmlns', "urn:ebay:apis:eBLBaseComponents");
            $xmlWriter->startElement('RequesterCredentials');
            $xmlWriter->writeElement('eBayAuthToken', $this->shop->token);
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
            $xmlWriter->writeElement('Location', 'NV CA PA IL TX FL');
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
            $xmlWriter->writeElement('Email', $this->shop->email);
            $xmlWriter->writeElement('FeedbackScore', '134');
            $xmlWriter->writeElement('PositiveFeedbackPercent', '100.0');
            $xmlWriter->writeElement('FeedbackPrivate', 'false');
            $xmlWriter->writeElement('IDVerified', 'false');
            $xmlWriter->writeElement('eBayGoodStanding', 'true');
            $xmlWriter->writeElement('NewUser', 'false');
            $xmlWriter->writeElement('Site', 'eBayMotors');
            $xmlWriter->writeElement('Status', 'Confirmed');
            $xmlWriter->writeElement('UserID', $this->shop->username);
            $xmlWriter->writeElement('UserIDChanged', 'false');
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
            $xmlWriter->writeElement('StoreURL', $this->shop->store_url);
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
            $xmlWriter->writeElement('ShippingProfileID', $this->shop->shipping_profile_id);
            $xmlWriter->writeElement('ShippingProfileName', $this->shop->shipping_profile_name);
            $xmlWriter->endElement();

            $xmlWriter->startElement('SellerReturnProfile');
            $xmlWriter->writeElement('ReturnProfileID', $this->shop->return_profile_id);
            $xmlWriter->writeElement('ReturnProfileName', $this->shop->return_profile_name);
            $xmlWriter->endElement();

            $xmlWriter->startElement('SellerPaymentProfile');
            $xmlWriter->writeElement('PaymentProfileID', $this->shop->payment_profile_id);
            $xmlWriter->writeElement('PaymentProfileName', $this->shop->return_profile_name);
            $xmlWriter->endElement();

            $xmlWriter->endElement();
            // End SellerProfiles

            $xmlWriter->writeElement('ShipToLocations', 'US');

            // Start ItemSpecifics
            $xmlWriter->startElement('ItemCompatibilityList');

            $compatibilityNotes = '';

            foreach ($fitments as $fitment) {
                $xmlWriter->startElement('Compatibility');

                $compatibilityNotes = $fitments->where('make_name', $fitment->make_name)->where('year', $fitment->year)->where('model_name', $fitment->model_name)->first();
                $notes = 'For';
                if ($compatibilityNotes->cylinders) $notes .= ' ' . $compatibilityNotes->cylinders.'Cyl';
                if ($compatibilityNotes->liter) $notes .= ' ' . $compatibilityNotes->liter.'L';
                $notes .= ' ' . $compatibilityNotes->make_name . ' ' . $compatibilityNotes->model_name . ' ' . $compatibilityNotes->part_name;
                if ($compatibilityNotes->position) $notes .= ' ' . $compatibilityNotes->position;

                $xmlWriter->writeElement('CompatibilityNotes', $notes);

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
                if ($fitment->bodytypename) {
                    $xmlWriter->startElement('NameValueList');
                    $xmlWriter->writeElement('Name', 'Trim');
                    $xmlWriter->writeElement('Value', $fitment->bodytypename);
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
            $xmlWriter->writeElement('Value', $this->shop->title);
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
            } catch (Exception $e) {
                return 'Error while sending request to Ebay API';
            }

            return $response;
        }

        return $response;
    }

    /**
     * Method for revise fixed price item at Ebay
     * @param EbayListing $listing
     * @return PromiseInterface|Response|string
     * @throws Exception
     */
    public function reviseFixedPriceItem(EbayListing $listing): PromiseInterface|string|Response
    {
        $title = strlen($listing->product->getTitle()) < 70 ? $listing->product->getTitle() : $listing->product->getTitleShort();
        $response = $this->getSuggestedCategories($title);
        $body = simplexml_load_string($response->body());
        $categoryID = 0;
        if (count((array)$body->SuggestedCategoryArray[0]) != 0) {
            $xml = (array)$body->SuggestedCategoryArray[0]->SuggestedCategory->Category->CategoryID;
            $categoryID = $xml[0];
        }
        $this->headers["X-EBAY-API-CALL-NAME"] = 'ReviseFixedPriceItem';
        $this->headers["X-EBAY-API-SITEID"] = '100';

        $fitments = Compatibility::select('id', 'sku', 'year', 'make_name', 'model_name', 'submodel_name', 'bodytypename')->where('sku', $listing->product->sku)->get();
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


/*        for ($i = 1; $i < 8; $i++) {
            $file = 'https://res.cloudinary.com/us-auto-parts-network-inc/image/upload/images/' . $listing->product->sku . '_' . $i;
            $ch = curl_init($file);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode == 200) {
                $images[] = $file;
            } else break;
        }

        $images[0] = $this->renderImageSpecifications($images[0], $this->shop->slug);

        $listing->product->images = implode(',', $images);
        $listing->product->save();


        $template = View('ebay.templates.'.$this->shop->slug, [
            'title'         => $listing->product->getTitle(),
            'fitments'      => $fitmentItems,
            'attributes'    => $attributes,
            'positions'      => $positions,
            'images'        => explode(',', $listing->product->images)
        ])->render();*/

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

/*            $xmlWriter->startElement('PictureDetails');
            $xmlWriter->writeElement('GalleryType', 'Gallery');
            $xmlWriter->writeElement('PhotoDisplay', 'PicturePack');

            foreach (explode(',', $listing->product->images) as $image) {
                $xmlWriter->writeElement('PictureURL', $image); //NEED SOME URLS
            }

            $xmlWriter->endElement();*/


            /*$xmlWriter->writeElement('Title', $title);

            $xmlWriter->startElement('Description');  // Need HTML Template
                $xmlWriter->text($template);
            $xmlWriter->endElement();*/

        // Start ItemSpecifics
        $xmlWriter->startElement('ItemCompatibilityList');
        foreach ($fitments->groupBy(['year', 'make_name', 'model_name']) as $year => $y) {
            foreach ($y as $make => $m) {
                foreach ($m as $model => $ml) {
                    $ebayFitments = $this->getCompatibilityTrimsFromEbay($categoryID, $year, $make, $model);
                    foreach ($ml as $item) {
                        $xmlWriter->startElement('Compatibility');
                            $compatibilityNotes = $fitments->where('make_name', $make)->where('year', $year)->where('model_name', $model)->first();
                            $notes = 'For';
                            if ($compatibilityNotes->cylinders) $notes .= ' ' . $compatibilityNotes->cylinders.'Cyl';
                            if ($compatibilityNotes->liter) $notes .= ' ' . $compatibilityNotes->liter.'L';
                            $notes .= ' ' . $compatibilityNotes->make_name . ' ' . $compatibilityNotes->model_name . ' ' . $compatibilityNotes->part_name;
                            if ($compatibilityNotes->position) $notes .= ' ' . $compatibilityNotes->position;
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
                            if (is_array($ebayFitments)) {
                                if ($item->submodel_name) {
                                    $trim = '';
                                    foreach ($ebayFitments as $fit) {
                                        if ($item->bodytypename != "") {
                                            if (Str::contains($fit["value"], $item->submodel_name) && Str::contains($fit["value"], $item->bodytypename)) {
                                                $trim = $fit["value"];
                                            }
                                        }
                                        else {
                                            if (Str::contains($fit["value"], $item->submodel_name)) {
                                                $trim = $fit["value"];
                                            }
                                        }
                                    }
                                    $xmlWriter->startElement('NameValueList');
                                    $xmlWriter->writeElement('Name', 'Trim');
                                    $xmlWriter->writeElement('Value', $trim);
                                    $xmlWriter->endElement();
                                }
                                else {
                                    if ($item->bodytypename) {
                                        $trim = '';
                                        foreach ($ebayFitments as $fit) {
                                            if (Str::contains($fit["value"], $item->bodytypename)) {
                                                $trim = $fit["value"];
                                            }
                                        }
                                        $xmlWriter->startElement('NameValueList');
                                        $xmlWriter->writeElement('Name', 'Trim');
                                        $xmlWriter->writeElement('Value', $trim);
                                        $xmlWriter->endElement();
                                    }
                                }
                            }
                        $xmlWriter->endElement();
                    }
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
        $headers["Authorization"] = 'Bearer ' . env('OAUTH_TOKEN');
        $headers["Accept"] = 'application/json';
        $headers["Content-Type"] = 'application/json';
        $headers["Accept-Encoding"] = 'gzip';
        $url = 'https://api.ebay.com/commerce/taxonomy/v1/category_tree/100/get_compatibility_property_values?compatibility_property=Trim&category_id='.$category_id
            .'&filter=Year:'.$year.',Make:'.$make.',Model:'.$model;
        $response = Http::withHeaders($headers)->send('GET', $url);
        if ($response->json() != null) {
            return array_key_exists('compatibilityPropertyValues', $response->json()) ? $response->json()["compatibilityPropertyValues"] : false;
        }
        else return false;
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

}

<?php

namespace App\Helpers\Ebay;

use App\Models\Attribute;
use App\Models\Compatibility;
use App\Models\Product;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use XMLWriter;
use Exception;

trait AddFixedPriceItem
{
    /**
     * Method for listing a fixed price item at Ebay
     * @param Product $product
     * @return PromiseInterface|Response|string
     * @throws Exception
     */
    public function addFixedPriceItem(Product $product): PromiseInterface|string|Response
    {
        $title = strlen($product->getTitle()) < 80 ? $product->getTitle() : $product->getTitleShort();
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
                } else {
                    $file = 'https://res.cloudinary.com/us-auto-parts-network-inc/image/upload/images/' . explode('-', $product->sku)[0] . '_' . $i;
                    $ch = curl_init($file);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if ($httpCode == 200) {
                        $images[] = $file;
                    }
                    else break;
                }
            }

            $images[0] = $this->renderImageSpecifications($images[0], $this->shop->slug,$product->id);

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
}

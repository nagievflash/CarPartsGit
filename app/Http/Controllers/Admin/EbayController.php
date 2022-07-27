<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use XMLWriter;

class EbayController extends Controller
{
    private string $token;
    public array $headers;
    public string $url;

    /**
     * Создание экзмепляра
     */
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            $this->token = $request->has('token') ? $request->input('token') : '';
            $this->headers = array(
                "Content-Type"                      => "content-type: application/xml; charset=UTF-8",
                "X-EBAY-API-APP-NAME"               => "fastdeal-autoelem-PRD-4f2fb35bc-cbb0b166",
                "X-EBAY-API-DEV-NAME"               => "f4927169-5c25-41f4-9751-3c7455b20912",
                "X-EBAY-API-CERT-NAME"              => "PRD-f2fb35bc9102-6d45-460b-a53a-aa4a",
                "X-EBAY-API-SITEID"                 => 0,
                "X-EBAY-API-COMPATIBILITY-LEVEL"    => 723,
                "X-EBAY-API-CALL-NAME"              => "",
                "X-EBAY-API-DETAIL-LEVEL"           => "0",
                "Transfer-Encoding"                 => 'chunked'
            );
            $this->url = 'https://api.ebay.com/ws/api.dll';
            return $next($request);
        });

    }

    /**
     * Запрос на получение рекомендуемой категории
     * @param Request $request
     * @return PromiseInterface|Response
     * @throws \Exception
     */
    public function getSuggestedCategories(Request $request): PromiseInterface|Response
    {
        $this->headers["X-EBAY-API-CALL-NAME"] = 'GetSuggestedCategories';
        $query = $request->has('query') ? $request->input('query') : '';

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
     * This method is adding fixed price items to Ebay Listings
     * Using Ebay Trading API
     * @param Request $request
     * @return PromiseInterface|Response
     * @throws \Exception
     */
    public function addFixedPriceItem(Request $request): PromiseInterface|Response
    {
        $this->headers["X-EBAY-API-CALL-NAME"] = 'AddFixedPriceItem';

        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->startDocument('1.0', 'utf-8');
        $xmlWriter->startElement('AddFixedPriceItemRequestsRequest');
            $xmlWriter->writeAttribute('xmlns', "urn:ebay:apis:eBLBaseComponents");
            $xmlWriter->startElement('RequesterCredentials');
                $xmlWriter->writeElement('eBayAuthToken', $this->token);
            $xmlWriter->endElement();
            $xmlWriter->writeElement('ErrorLanguage', 'en_US');
            $xmlWriter->writeElement('WarningLevel', 'High');

            // Start Item
            $xmlWriter->startElement('RequesterCredentials');
                $xmlWriter->writeElement('title', 'Beck Arnley Air Filter');  // Need Title with masking
                $xmlWriter->startElement('PrimaryCategory');
                    $xmlWriter->writeElement('CategoryID', '43509'); // Need Category ID
                $xmlWriter->endElement();
                $xmlWriter->writeElement('ConditionID', '1000');
                $xmlWriter->writeElement('ConditionDisplayName', 'New');
                $xmlWriter->writeElement('StartPrice', '0');
                $xmlWriter->writeElement('CategoryMappingAllowed', 'true');
                $xmlWriter->writeElement('Country', 'US');
                $xmlWriter->writeElement('Currency', 'USD');
                $xmlWriter->writeElement('Description', 'Template');  // Need HTML Template
                $xmlWriter->writeElement('DispatchTimeMax', '3');
                $xmlWriter->writeElement('ListingDuration', 'GTC');
                $xmlWriter->writeElement('ListingType', 'FixedPriceItem');
                $xmlWriter->writeElement('Location', 'AZ CA NY FL TX WA');
                $xmlWriter->writeElement('PrivateListing', 'false');
                $xmlWriter->startElement('PictureDetails');
                    $xmlWriter->writeElement('GalleryType', 'Gallery');
                    $xmlWriter->writeElement('PhotoDisplay', 'PicturePack');
                    $xmlWriter->writeElement('PictureURL', 'https://i.ebayimg.com/00/s/MTUyNlgxNjAw/z/0JUAAOSwAhNhhAwF/$_57.JPG?set_id=8800005007'); //NEED SOME URLS
                $xmlWriter->endElement();
                $xmlWriter->writeElement('Quantity', '0');
                $xmlWriter->startElement('ReservePrice');
                    $xmlWriter->writeAttribute('currencyID', "USD");
                    $xmlWriter->text("0.0");
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
                    $xmlWriter->writeElement('Site', 'US');
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
                        $xmlWriter->text("36.75");
                    $xmlWriter->endElement();

                    $xmlWriter->startElement('CurrentPrice');
                        $xmlWriter->writeAttribute('currencyID', "USD");
                        $xmlWriter->text("36.75");
                    $xmlWriter->endElement();

                    $xmlWriter->startElement('MinimumToBid');
                        $xmlWriter->writeAttribute('currencyID', "USD");
                        $xmlWriter->text("36.75");
                    $xmlWriter->endElement();


                    $xmlWriter->writeElement('LeadCount', '0');
                    $xmlWriter->writeElement('QuantitySold', '0');
                    $xmlWriter->writeElement('ReserveMet', 'true');
                    $xmlWriter->writeElement('SecondChanceEligible', 'false');
                    $xmlWriter->writeElement('ListingStatus', 'active');
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
                        $xmlWriter->writeElement('ShippingService', 'UPSGround');
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
                        $xmlWriter->writeElement('ShippingProfileID', '209396841012');
                        $xmlWriter->writeElement('ShippingProfileName', 'Flat:UPS Ground(Free),1 business day');
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
                $xmlWriter->startElement('ItemSpecifics');
                    $xmlWriter->startElement('NameValueList');
                        $xmlWriter->writeElement('Name', 'Brand');
                        $xmlWriter->writeElement('Value', 'Beck Arnley');
                    $xmlWriter->endElement();
                    $xmlWriter->startElement('NameValueList');
                        $xmlWriter->writeElement('Name', 'Type');
                        $xmlWriter->writeElement('Value', 'Custom');
                    $xmlWriter->endElement();
                $xmlWriter->endElement();
                // End ItemSpecifics

            $xmlWriter->endElement();
            // End Item

        $xmlWriter->endElement();
        $xmlWriter->endDocument();

        return $this->sendRequest($xmlWriter->outputMemory());
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

}

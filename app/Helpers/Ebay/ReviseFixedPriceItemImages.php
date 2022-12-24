<?php

namespace App\Helpers\Ebay;

use App\Models\Attribute;
use App\Models\Backlog;
use App\Models\Compatibility;
use App\Models\EbayListing;
use App\Models\Product;
use App\Models\Warehouse;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;
use XMLWriter;
use Exception;

trait ReviseFixedPriceItemImages
{

    /**
     * Method for revise fixed price item at Ebay
     * @param EbayListing $listing
     * @return PromiseInterface|Response|string
     * @throws Exception
     */
    public function reviseFixedPriceItemImages(EbayListing $listing): PromiseInterface|string|Response
    {
        $this->headers["X-EBAY-API-CALL-NAME"] = 'ReviseFixedPriceItem';
        $this->headers["X-EBAY-API-SITEID"] = '100';

        $images = array();
        for ($i = 1; $i < 8; $i++) {
            $file = 'https://res.cloudinary.com/us-auto-parts-network-inc/image/upload/images/' . $listing->sku . '_' . $i;
            $ch = curl_init($file);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode == 200) {
                $images[] = $file;
            } else {
                $file = 'https://res.cloudinary.com/us-auto-parts-network-inc/image/upload/images/' . explode('-', $listing->sku)[0] . '_' . $i;
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
        $images[0] = $this->renderImageSpecifications($images[0], $this->shop->slug);

        $price = $listing->getPrice();
        $product = Product::where('sku', $listing->sku)->first();
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
        $template = View('ebay.templates.'.$this->shop->slug, [
            'title'         => $product->getTitle(),
            'fitments'      => $fitmentItems,
            'attributes'    => $attributes,
            'positions'     => $positions,
            'images'        => $images
        ])->render();

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

        $xmlWriter->startElement('PictureDetails');
        $xmlWriter->writeElement('GalleryType', 'Gallery');
        $xmlWriter->writeElement('PhotoDisplay', 'PicturePack');

        foreach ($images as $image) {
            $xmlWriter->writeElement('PictureURL', $image); //NEED SOME URLS
        }

        $xmlWriter->endElement();

        $xmlWriter->startElement('Description');  // Need HTML Template
        $xmlWriter->text($template);
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
        Backlog::createBacklog('update pricing', $listing->ebay_id);
        return $response;
    }
}

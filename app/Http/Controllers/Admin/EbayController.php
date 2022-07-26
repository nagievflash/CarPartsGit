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

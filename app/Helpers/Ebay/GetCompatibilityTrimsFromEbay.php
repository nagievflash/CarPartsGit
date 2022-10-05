<?php

namespace App\Helpers\Ebay;

use XMLWriter;
use Exception;
use Illuminate\Support\Facades\Http;

trait GetCompatibilityTrimsFromEbay
{

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
}

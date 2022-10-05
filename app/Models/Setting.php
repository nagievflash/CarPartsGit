<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function getAccessToken() {
        if (!Cache::has('access_token')) {
            try {
                $oauthToken = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ' . base64_encode('fastdeal-autoelem-PRD-4f2fb35bc-cbb0b166:PRD-f2fb35bc9102-6d45-460b-a53a-aa4a'),
                ])->send('POST', 'https://api.ebay.com/identity/v1/oauth2/token', [
                    'form_params' => [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => Setting::where('key', 'ebay_refresh_token')->first()->value,
                        'redirect_uri' => 'fastdeal24-fastdeal-autoel-ymxyoese',
                    ]
                ]);
                Cache::put('access_token', $oauthToken->json()['access_token'], 7100);
                return $oauthToken->json()['access_token'];
            } catch (\Exception $e) {
                abort(404);
            }
        }
        return Cache::get('access_token');
    }
}

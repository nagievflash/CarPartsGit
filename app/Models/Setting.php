<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * @method updateOrCreate(array $array, array $array1)
 * @method static where(string $string, string $string1)
 * @method static firstOrNew(int[] $array)
 * @method static firstOrCreate(string[] $array, int[] $array1)
 */
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
                    'Authorization' => 'Basic ' . base64_encode(env('EBAY_APP_ID').':'.env('EBAY_SECRET')),
                ])->send('POST', 'https://api.ebay.com/identity/v1/oauth2/token', [
                    'form_params' => [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => Setting::where('key', 'ebay_refresh_token')->first()->value,
                        'redirect_uri' => env('EBAY_RUNAME'),
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
    public static function setAccessToken($token) {
        (new Setting)->updateOrCreate([
            'key'   => 'ebay_refresh_token',
        ],[
            'key'   => 'ebay_refresh_token',
            'value' => $token
        ]);
    }
}

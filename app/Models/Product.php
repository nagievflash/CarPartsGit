<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Compatibility;
use App\Models\Attribute;
use App\Models\Fitment;


/**
 * @method static paginate(int $int)
 * @method static where(string $string, mixed $input)
 */
class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'images' => 'array'
    ];

    /**
     * Get the VEHICLE COMPATIBILITY FITMENT
     */
    public function fitments()
    {
        return $this->hasMany(Fitment::class, 'sku', 'sku');
    }


    /**
     * Get the product's attributes
     */
    public function attributes()
    {
        return $this->hasMany(Attribute::class, 'sku', 'sku');
    }


    public function getTitle() {
        // = 'New ' + PARTNAME + ' for ' + MAKENAME + ' ' + MODELNAME + ' ' + MINYEAR + '-' + MAXYEAR
        $fitment = Fitment::select('make_name', 'part_name', 'model_name', 'year')->where('sku', $this->sku)->get();
        $title = $this->title;
        if ($fitment->count() > 0) {
            $items = $fitment->where('model_name', $fitment->first()->model_name)->sortBy(['year', 'asc']);
            if ($items->first()->year != $items->last()->year) $title = 'New ' . $items->first()->part_name . ' for ' . $items->first()->make_name . ' ' . $items->first()->model_name . ' ' . $items->first()->year . '-' . $items->last()->year;
            else $title = 'New ' . $items->first()->part_name . ' for ' . $items->first()->make_name . ' ' . $items->first()->model_name . ' ' . $items->first()->year;
        }
        return $title;
    }

}

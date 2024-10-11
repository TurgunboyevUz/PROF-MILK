<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['category_id', 'name_uz', 'name_ru', 'description_uz', 'description_ru', 'image', 'price', 'code', 'vat_percent', 'package_code'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function image()
    {
        return asset('storage/' . $this->image);
    }

    public function name()
    {
        return app()->getLocale() == 'uz' ? $this->name_uz : $this->name_ru;
    }

    public function description()
    {
        return app()->getLocale() == 'uz' ? $this->description_uz : $this->description_ru;
    }
}
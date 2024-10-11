<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name_uz', 'name_ru'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function name()
    {
        return app()->getLocale() == 'uz' ? $this->name_uz : $this->name_ru;
    }
}
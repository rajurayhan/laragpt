<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteComponentCategory extends Model
{
    protected $primaryKey = 'category_id';

    protected $fillable = ['category_name'];

    public function components()
    {
        return $this->hasMany(WebsiteComponent::class, 'category_id');
    }
}

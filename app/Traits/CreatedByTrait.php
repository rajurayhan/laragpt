<?php 


namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait CreatedByTrait
{
    public static function bootCreatedByTrait()
    {
        static::creating(function ($model) {
            $model->createdById = Auth::id();
        });
    }
}

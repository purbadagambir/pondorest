<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SellingPrice extends Model
{
    protected $table="selling_price";
    protected $primaryKey ="invoice_id";
    protected $fillable=[''];
}

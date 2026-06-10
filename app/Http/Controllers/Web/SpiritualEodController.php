<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\v1\SpiritualController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class SpiritualEodController extends Controller
{
    public function __construct(private SpiritualController $spiritual) {}

    public function storeEodMinistry(Request $request)
    {
        return $this->spiritual->storeEodMinistry($request);
    }
}
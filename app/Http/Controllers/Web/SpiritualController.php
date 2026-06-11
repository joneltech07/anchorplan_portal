<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\v1\SpiritualController as ApiSpiritualController;
use Illuminate\Http\Request;

class SpiritualController extends Controller
{
    public function __construct(private ApiSpiritualController $spiritual) {}

    public function dashboard(Request $request)
    {
        return $this->spiritual->dashboard($request);
    }

    public function devotionalRecords(Request $request)
    {
        return $this->spiritual->devotionalRecords($request);
    }

    public function updateDevotional(Request $request, string $userId)
    {
        return $this->spiritual->updateDevotional($request, $userId);
    }

    public function wednesdayRecords(Request $request)
    {
        return $this->spiritual->wednesdayRecords($request);
    }

    public function updateWednesday(Request $request, string $userId)
    {
        return $this->spiritual->updateWednesday($request, $userId);
    }

    public function sundayRecords(Request $request)
    {
        return $this->spiritual->sundayRecords($request);
    }

    public function updateSunday(Request $request, string $userId)
    {
        return $this->spiritual->updateSunday($request, $userId);
    }

    public function ministryStats(Request $request)
    {
        return $this->spiritual->ministryStats($request);
    }

    public function ministryReports(Request $request)
    {
        return $this->spiritual->ministryReports($request);
    }

    public function remindAll(Request $request)
    {
        return $this->spiritual->remindAll($request);
    }

    public function remindDevotional(Request $request, string $userId)
    {
        return $this->spiritual->remindDevotional($request, $userId);
    }
}


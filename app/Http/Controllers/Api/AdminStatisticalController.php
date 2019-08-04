<?php

namespace App\Http\Controllers\Api;

use App\AdminStatistical;
use App\Http\Resources\AdminStatisticalResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminStatisticalController extends Controller
{
    public function statisticalUserRegister(Request $request)
    {
        $params = $request->only(['start_date', 'end_date']);
        $from = date($params['start_date']);
        $to = date($params['end_date']);
        $usersCount = $this->countUserRegisterScope($from, $to);
        $userCountAll = $this->statisticalUserRegisterAll();
        $statistical = new AdminStatisticalResource($request);
        return $statistical;
    }

    private function countUserRegisterScope($from, $to) {
        $usersCount = User::whereBetween('created_at', [$from, $to])->get()->count();
        return $usersCount;
    }

    private function statisticalUserRegisterAll()
    {
        return User::all()->count();
    }
}

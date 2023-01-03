<?php

namespace App\Http\Controllers;

use App\Customer;
use App\PointLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiCustomerController extends Controller
{
    public function index()
    {
        return authapi();
    }

    public function memberLogin(Request $request)
    {
        $data = Customer::where('customer_mobile', $request->customer_mobile)->first();
        if ($data) {
            $header['code'] = 200;
            $header['message'] = 'Ok';
            return json_encode(array('metadata' => $header, 'response' => $data));
        } else {
            $header['code'] = 201;
            $header['message'] = 'Password Salah ' . Hash::make($request->password);
            $body['data'] = $data;
            return json_encode(array('metadata' => $header, 'response' => null));
        }
    }

    public function customerCredit(Request $request)
    {
        $data = Customer::where('customer_mobile', $request->customer_id)
            ->select('credit', 'total_points')
            ->first();
        if ($data) {
            $header['code'] = 200;
            $header['message'] = 'Ok';
            return json_encode(array('metadata' => $header, 'response' => $data));
        } else {
            $header['code'] = 404;
            $header['message'] = 'Data tidak ditemukan';
            $body['data'] = $data;
            return json_encode(array('metadata' => $header, 'response' => null));
        }
    }

    public function getMember(Request $request)
    {
        $data = Customer::where('parent_id', $request->customer_id)
            ->select('customer_id', 'customer_name', 'customer_mobile', 'customer_address')
            ->get();

        if ($data) {
            $header['code'] = 200;
            $header['message'] = 'Ok';
            return json_encode(array('metadata' => $header, 'response' => $data));
        } else {
            $header['code'] = 201;
            $header['message'] = 'Password Salah ' . Hash::make($request->password);
            $body['data'] = $data;
            return json_encode(array('metadata' => $header, 'response' => null));
        }
    }

    public function getPointData(Request $request)
    {
        $data = PointLogs::where('customer_id', $request->customer_id)
            ->orderby('id')
            ->limit(10)
            ->get();
        if ($data) {
            $header['code'] = 200;
            $header['message'] = 'Ok';
            return json_encode(array('metadata' => $header, 'response' => $data));
        } else {
            $header['code'] = 201;
            $header['message'] = 'Password Salah ' . Hash::make($request->password);
            $body['data'] = $data;
            return json_encode(array('metadata' => $header, 'response' => null));
        }
    }
}

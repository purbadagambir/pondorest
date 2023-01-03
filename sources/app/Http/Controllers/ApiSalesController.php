<?php

namespace App\Http\Controllers;

use App\{Sales, PointLogs, Customer, CustomerTransactions};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ApiSalesController extends Controller
{
    public function index()
    {
        return authapi();
    }

    public function getSalesInfo(Request $request)
    {
        $sales_Info = Sales::where('invoice_id', $request->invoice)->first();

        if ($sales_Info) {
            $metadata['Code'] = '200';
            $metadata['Message'] = 'Ok';
            $data = $sales_Info;
        } else {
            $metadata['Code'] = '404';
            $metadata['Message'] = 'Data tidak ditemukan.';
            $data = null;
        }
        return response(['metadata' => $metadata, 'data' => $data]);
    }

    public function insertSalesData(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'order_number' => 'required',
            'store_id' => 'required',
            'customer_mobile_number' => 'required',
            'hpp' => 'required',
            'amount' => 'required',
            'discount' => 'required',
            'amount_due' => 'required'
        ]);

        if (!$validator->fails()) {
            try {
                $total_points = $input['hpp'];
                $member = Customer::select('customer_id')->where('customer_mobile',  $input['customer_mobile_number'])->first();
                if ($member) {
                    $member_id = $member->customer_id;
                } else {
                    $metadata['Code'] = '404';
                    $metadata['Message'] = 'Data Customer tidak ditemukan.';
                    $data = null;
                    return response(['metadata' => $metadata, 'data' => $data]);
                }

                DB::beginTransaction();

                DB::table('selling_info')->insert([
                    'invoice_id' => $input['order_number'],
                    'store_id' => $input['store_id'],
                    'customer_id' => $member_id,
                    'total_points' => $total_points,
                    'created_at' => date('Y-m-d h:i:s')
                ]);

                DB::table('selling_price')->insert([
                    'invoice_id' => $input['order_number'],
                    'store_id' => $input['store_id'],
                    'total_purchase_price' => $input['hpp'],
                    'payable_amount' => $input['amount_due'],
                    'paid_amount' => $input['amount_due'],
                    'total_brutto' => $input['amount_due'],
                    'profit' => $input['amount_due'] - $input['hpp']
                ]);

                DB::select('call sp_calc_sharing_point(?,?)', [$input['order_number'], 'sell']);


                $point = PointLogs::where('trans_no', $input['order_number'])
                    ->where('customer_id', $member_id)
                    ->first();

                DB::commit();

                $headers['Code'] = '200';
                $headers['Message'] = 'Ok';
                $data = $point;
                return json_encode(['metadata' => $headers, 'data' => $data]);
            } catch (\Throwable $th) {
                DB::rollback();
                $headers['Code'] = '201';
                $headers['Message'] = 'Error : ' . $th->getMessage();
                $data = $input['order_number'] . ' Error Insert';
                return json_encode(['metadata' => $headers, 'data' => $data]);
            }
        } else {
            $headers['Code'] = '201';
            $headers['Message'] = 'Not Valid Request data';
            $data = null;
            return json_encode(['metadata' => $headers, 'data' => $data]);
        }
    }

    public function insertCredit(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'customer_mobile' => 'required',
            'order_number' => 'required',
            'store_id' => 'required',
            'pmethod_id' => 'required',
            'amount' => 'required'
        ]);

        if (!$validator->fails()) {
            try {
                $member = Customer::select('customer_id')->where('customer_mobile',  $input['customer_mobile'])->first();
                if ($member) {
                    $member_id = $member->customer_id;
                } else {
                    $metadata['Code'] = '404';
                    $metadata['Message'] = 'Data Customer tidak ditemukan.';
                    $data = null;
                    return response(['metadata' => $metadata, 'data' => $data]);
                }

                $credit = CustomerTransactions::select('id', 'amount', 'balance')->where('customer_id', $member_id)->orderby('id', 'desc')->first();

                DB::beginTransaction();

                $newCredit = $credit->balance + $input['amount'];

                DB::table('customer_transactions')->insert([
                    'type' => 'add_balance',
                    'ref_invoice_id' => $input['order_number'],
                    'store_id' => $input['store_id'],
                    'customer_id' => $member_id,
                    'amount' => $input['amount'],
                    'balance' => $newCredit,
                    'notes' => 'add while shoping',
                    'created_by' => 0,
                    'created_at' => date('Y-m-d h:i:s')
                ]);

                DB::table('customers')->where('customer_id', $member_id)->update([
                    'credit' => $newCredit,
                ]);

                DB::table('customer_to_store')->where('customer_id', $member_id)->update([
                    'balance' => $newCredit,
                ]);

                DB::commit();

                $headers['Code'] = '200';
                $headers['Message'] = 'Ok';
                $data['mobile_no'] = $input['customer_mobile'];
                $data['prev_balace'] = $credit['balance'];
                $data['amount'] = $input['amount'];
                $data['balance'] = $newCredit;

                return json_encode(['metadata' => $headers, 'response' => $data]);
            } catch (\Throwable $th) {
                DB::rollback();
                $headers['Code'] = '201';
                $headers['Message'] = 'Error : ' . $th->getMessage();
                $data = $input['order_number'] . ' Error Insert ';
                return json_encode(['metadata' => $headers, 'data' => $data]);
            }
        } else {
            $headers['Code'] = '201';
            $headers['Message'] = 'Not Valid Request data';
            $data = null;
            return json_encode(['metadata' => $headers, 'data' => $data]);
        }
    }
}

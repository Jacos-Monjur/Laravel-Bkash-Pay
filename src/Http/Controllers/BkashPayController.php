<?php

namespace monjur\bkash\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use monjur\bkash\Models\Order;

class BkashPayController extends Controller
{

    public function index()
    {
        for ($i = 0; $i < 25; $i++) {
            Order::create([
                'product_name' => 'Test Demo' . $i,
                'currency' => 'BDT',
                'amount' => 100 . $i,
                'invoice' => 'BBL-22179' . $i,
                'trxID' => '',
                'status' => 'Pending'
            ]);
        }
        $orders = Order::all();
        return view('bkash::Bkash.bkash-pay', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::find($id);

        return view('bkash::Bkash.load', compact('order'));
    }

    public function token()
    {
        session_start();

        $request_token = $this->bkash_Get_Token();
        $idtoken = $request_token['id_token'];

        $_SESSION['token'] = $idtoken;

        // $strJsonFileContents = file_get_contents($this->bkashJson());
        // $array = json_decode($strJsonFileContents, true);

        $array = $this->get_config_file();

        $array['token'] = $idtoken;

        $newJsonString = json_encode($array);
        File::put(storage_path('/app/public/bkash.json'), $newJsonString);

        echo $idtoken;
    }

    protected function bkash_Get_Token()
    {
        /*$strJsonFileContents = file_get_contents("config.json");
        $array = json_decode($strJsonFileContents, true);*/

        $array = $this->get_config_file();

        $post_token = array(
            'app_key' => $array["app_key"],
            'app_secret' => $array["app_secret"]
        );

        $url = curl_init($array["tokenURL"]);
        $proxy = $array["proxy"];
        $posttoken = json_encode($post_token);
        $header = array(
            'Content-Type:application/json',
            'password:' . $array["password"],
            'username:' . $array["username"]
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($url, CURLOPT_PROXY, $proxy);
        $resultdata = curl_exec($url);
        curl_close($url);
        dd($resultdata);
        return json_decode($resultdata, true);
    }

    protected function get_config_file()
    {
        $path = resource_path('/views/Bkash/bkash.json');
        // $path = $this->bkashJson();
        return json_decode(file_get_contents($path), true);
    }

    public function createpayment()
    {
        session_start();

        /*$strJsonFileContents = file_get_contents("config.json");
        $array = json_decode($strJsonFileContents, true);*/

        $array = $this->get_config_file();

        $amount = $_GET['amount'];
        $invoice = $_GET['invoice']; // must be unique
        $intent = "sale";
        $proxy = $array["proxy"];
        $createpaybody = array('amount' => $amount, 'currency' => 'BDT', 'merchantInvoiceNumber' => $invoice, 'intent' => $intent);
        $url = curl_init($array["createURL"]);

        $createpaybodyx = json_encode($createpaybody);

        $header = array(
            'Content-Type:application/json',
            'authorization:' . $array["token"],
            'x-app-key:' . $array["app_key"]
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $createpaybodyx);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($url, CURLOPT_PROXY, $proxy);

        $resultdata = curl_exec($url);
        curl_close($url);
        echo $resultdata;
    }

    public function executepayment()
    {
        session_start();

        /*$strJsonFileContents = file_get_contents("config.json");
        $array = json_decode($strJsonFileContents, true);*/

        $array = $this->get_config_file();

        $paymentID = $_GET['paymentID'];
        $proxy = $array["proxy"];

        $url = curl_init($array["executeURL"] . $paymentID);

        $header = array(
            'Content-Type:application/json',
            'authorization:' . $array["token"],
            'x-app-key:' . $array["app_key"]
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        // curl_setopt($url, CURLOPT_PROXY, $proxy);

        $resultdatax = curl_exec($url);
        curl_close($url);

        $this->updateOrder($resultdatax);

        echo $resultdatax;
    }

    protected function updateOrder($resultdatax)
    {
        $resultdatax = json_decode($resultdatax);
        // dd($resultdatax);
        if ($resultdatax && $resultdatax->paymentID != null && $resultdatax->transactionStatus == 'Completed') {
            Order::where([
                'invoice' => $resultdatax->merchantInvoiceNumber
            ])->update([
                'status' => 'Processing', 'trxID' => $resultdatax->trxID
            ]);
        }
    }

    protected function bkashJson()
    {
        $json = '{
            "createURL": "https://checkout.sandbox.bka.sh/v1.2.0-beta/checkout/payment/create",
            "executeURL": "https://checkout.sandbox.bka.sh/v1.2.0-beta/checkout/payment/execute/",
            "tokenURL": "https://checkout.sandbox.bka.sh/v1.2.0-beta/checkout/token/grant",
            "script": "https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js",
            "app_key": "5tunt4masn6pv2hnvte1sb5n3j",
            "proxy": "",
            "app_secret": "1vggbqd4hqk9g96o9rrrp2jftvek578v7d2bnerim12a87dbrrka",
            "username": "sandboxTestUser",
            "password": "hWD@8vtzw0",
            "token": "eyJraWQiOiJmalhJQmwxclFUXC9hM215MG9ScXpEdVZZWk5KXC9qRTNJOFBaeGZUY3hlamc9IiwiYWxnIjoiUlMyNTYifQ.eyJzdWIiOiJiM2Q4OGVkZC0xNzc2LTRhMjEtYWZlMi0zN2FkZTk3NzEyZDMiLCJhdWQiOiI2NmEwdGZpYTZvc2tkYjRhMDRyY24wNjNhOSIsImV2ZW50X2lkIjoiYjdiYWM1MTMtOTY2MS00YWI1LWFmYzMtZmE2OGM3OWM4ZmRmIiwidG9rZW5fdXNlIjoiaWQiLCJhdXRoX3RpbWUiOjE2NjI2NTMyNDAsImlzcyI6Imh0dHBzOlwvXC9jb2duaXRvLWlkcC5hcC1zb3V0aGVhc3QtMS5hbWF6b25hd3MuY29tXC9hcC1zb3V0aGVhc3QtMV9rZjVCU05vUGUiLCJjb2duaXRvOnVzZXJuYW1lIjoic2FuZGJveFRlc3RVc2VyIiwiZXhwIjoxNjYyNjU2ODQwLCJpYXQiOjE2NjI2NTMyNDB9.F4ACD2SEnFx07Q5Xpq9ONpCruCg9Sr8RPWuM4xOwNnJIJfgeROf6KdzqFbsTilj-MY9gstR_I2ocR1EKaTAPNOshxSxmIIvJr5S5DIwocSNWrA6eGHHe1qh1H7-I7lxmiblOya8xOY7o8Mb-dj4r5Yw8EV_ZByxO-6r_GjupFjlXv0Uag-_ISU6LeFuB4Iql-nIX_cKCtg9kiHcgNT3bVVsmukik5dkeDsBmXbFgJb8eMBFXaKPxNzvJftJbHUuab9qppefEUa1gE3Fvif9QLMEQt0x7E98ZP96ZYmT9s_t-SOnuSY3i1qR6IxPXE27_4Oz10I5-FQV-hottuKHMpQ"
        }';

        return json_decode($json);
    }
}

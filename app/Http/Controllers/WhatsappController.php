<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    public function invoice(Order $order)
    {
        return redirect()->to($order->invoice_link);
    }
}

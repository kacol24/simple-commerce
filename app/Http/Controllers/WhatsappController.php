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

    public function confirmation(Order $order)
    {
        return redirect()->to($order->confirmation_link);
    }

    public function bulkInvoice(Request $request)
    {
        $orders = Order::whereIn('id', $request->order_ids)->get()->groupBy('customer_id');

        config()->set('livewire.inject_morph_markers', false);

        $append = view('whatstapp.bulk_invoice', [
            'bulkOrders' => $orders,
        ])->render();

        return redirect()->to('https://wa.me/?lang=en&text='.urlencode($append));
    }
}

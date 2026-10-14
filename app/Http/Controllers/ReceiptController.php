<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    /**
     * Display a 58mm/80mm thermal receipt print view.
     */
    public function print(Request $request, Order $order): View
    {
        $order->load(['table', 'items.modifiers', 'latestPayment']);

        // Update receipt_printed_at timestamp if not already printed
        if (! $order->receipt_printed_at) {
            $order->update([
                'receipt_printed_at' => now(),
            ]);
        }

        return view('receipt.thermal', [
            'order' => $order,
            'cashierName' => $request->user()?->name ?? 'Kasir',
            'isReprint' => $order->receipt_printed_at !== null && $order->receipt_printed_at->lt(now()->subSeconds(10)),
        ]);
    }
}

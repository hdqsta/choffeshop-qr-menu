<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableEntryController extends Controller
{
    /**
     * Handle entry from table QR code.
     */
    public function __invoke(Request $request, string $token): RedirectResponse|View
    {
        $table = Table::where('token', $token)->first();

        if (! $table || ! $table->is_active) {
            return view('customer.invalid-table', [
                'message' => 'Meja tidak ditemukan atau sedang tidak aktif. Silakan hubungi kasir/staf coffee shop.',
            ]);
        }

        // Store active table in session
        $request->session()->put('table_id', $table->id);
        $request->session()->put('table_number', $table->number);
        $request->session()->put('table_token', $table->token);

        return redirect()->route('customer.menu');
    }
}

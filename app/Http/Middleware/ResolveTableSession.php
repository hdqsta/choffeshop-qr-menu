<?php

namespace App\Http\Middleware;

use App\Models\Table;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTableSession
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tableId = $request->session()->get('table_id');

        if (! $tableId) {
            return response()->view('customer.invalid-table', [
                'message' => 'Silakan scan kode QR yang tertera pada meja Anda untuk mulai memilih menu dan memesan.',
            ], 403);
        }

        $table = Table::find($tableId);

        if (! $table || ! $table->is_active) {
            $request->session()->forget(['table_id', 'table_number', 'table_token']);

            return response()->view('customer.invalid-table', [
                'message' => 'Sesi meja Anda sudah tidak aktif. Silakan scan ulang kode QR di meja Anda atau hubungi barista.',
            ], 403);
        }

        return $next($request);
    }
}

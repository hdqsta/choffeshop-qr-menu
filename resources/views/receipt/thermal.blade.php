<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $order->order_number }}</title>
    <style>
        @page {
            margin: 0;
            size: 58mm auto;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 8px 6px;
            line-height: 1.25;
            width: 58mm;
            max-width: 58mm;
            box-sizing: border-box;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .double-divider {
            border-top: 2px dashed #000;
            margin: 6px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .item-row {
            margin-bottom: 4px;
        }

        .modifier-row {
            font-size: 10px;
            padding-left: 8px;
            color: #222;
        }

        .notes-row {
            font-size: 9px;
            font-style: italic;
            padding-left: 8px;
        }

        .reprint-badge {
            display: inline-block;
            border: 1px solid #000;
            padding: 1px 4px;
            font-size: 9px;
            margin-bottom: 4px;
        }

        @media screen {
            body {
                margin: 20px auto;
                border: 1px solid #ddd;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                background: #fff;
            }

            .screen-actions {
                text-align: center;
                margin-bottom: 12px;
                padding-bottom: 8px;
                border-bottom: 1px solid #eee;
            }

            .btn {
                background: #111;
                color: #fff;
                border: none;
                padding: 4px 10px;
                font-size: 11px;
                cursor: pointer;
                border-radius: 4px;
            }
        }

        @media print {
            .screen-actions {
                display: none;
            }
            body {
                width: 100%;
                margin: 0;
                padding: 6px 4px;
            }
        }
    </style>
</head>
<body>

    <div class="screen-actions">
        <button class="btn" onclick="window.print()">🖨️ Cetak</button>
        <button class="btn" onclick="window.close()">Tutup</button>
    </div>

    @if ($isReprint)
        <div class="center">
            <span class="reprint-badge bold">** SALINAN / REPRINT **</span>
        </div>
    @endif

    <div class="center">
        <div class="bold" style="font-size: 14px;">KEDAI KOPI KITA</div>
        <div>Jl. Kopi Harapan No. 42</div>
        <div>Telp: 0812-3456-7890</div>
    </div>

    <div class="divider"></div>

    <div class="row">
        <span>No: {{ $order->order_number }}</span>
        <span>Meja: <strong style="font-size: 13px;">{{ $order->table->number ?? '-' }}</strong></span>
    </div>
    <div class="row">
        <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
        <span>Kasir: {{ $cashierName }}</span>
    </div>

    <div class="divider"></div>

    <div class="bold" style="margin-bottom: 4px;">ITEM PESANAN:</div>

    @foreach ($order->items as $item)
        <div class="item-row">
            <div class="row">
                <span class="bold">{{ $item->quantity }}x {{ $item->product_name }}</span>
                <span class="bold">{{ number_format($item->line_total, 0, ',', '.') }}</span>
            </div>

            @foreach ($item->modifiers as $mod)
                <div class="modifier-row">
                    + {{ $mod->option_name }}
                    @if ($mod->price_delta > 0)
                        ({{ number_format($mod->price_delta, 0, ',', '.') }})
                    @endif
                </div>
            @endforeach

            @if ($item->notes)
                <div class="notes-row">Note: "{{ $item->notes }}"</div>
            @endif
        </div>
    @endforeach

    @if ($order->notes)
        <div class="divider"></div>
        <div class="notes-row">Catatan Pesanan: "{{ $order->notes }}"</div>
    @endif

    <div class="double-divider"></div>

    <div class="row bold">
        <span>TOTAL ITEM:</span>
        <span>{{ $order->items->sum('quantity') }}</span>
    </div>
    <div class="row bold" style="font-size: 13px; margin-top: 3px;">
        <span>TOTAL BAYAR:</span>
        <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
    </div>

    <div class="divider"></div>

    <div class="row">
        <span>Metode:</span>
        <span class="bold">QRIS Instant</span>
    </div>
    <div class="row">
        <span>Status:</span>
        <span class="bold">
            @if ($order->isPaid())
                LUNAS (PAID)
            @else
                {{ strtoupper($order->payment_status->value) }}
            @endif
        </span>
    </div>

    <div class="divider"></div>

    <div class="center" style="font-size: 9px; margin-top: 6px;">
        <div>Terima kasih atas kunjungan Anda!</div>
        <div>Silakan nikmati hidangan Anda ☕</div>
        <div style="margin-top: 4px;">Lacak status pesanan:</div>
        <div style="font-size: 8px; word-break: break-all;">
            {{ route('customer.order.status', ['publicToken' => $order->public_token]) }}
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            // Auto open print dialog when loaded in print window
            if (!window.location.search.includes('noprint')) {
                setTimeout(() => window.print(), 350);
            }
        });
    </script>
</body>
</html>

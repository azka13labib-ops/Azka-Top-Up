<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaksi Top Up Gagal</title>
    <style>
        body {
            font-family: 'Inter', Helvetica, Arial, sans-serif;
            background-color: #f4f5f7;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
        }
        .content {
            padding: 30px;
        }
        .status-badge {
            display: inline-block;
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .details-table td {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 15px;
        }
        .details-table td.label {
            color: #64748b;
            font-weight: 500;
        }
        .details-table td.value {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
        }
        .alert-box {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>AZKA TOP UP</h1>
        </div>
        <div class="content">
            <span class="status-badge">GAGAL / BUTUH REFUND</span>
            <p style="font-size: 16px; line-height: 1.6; margin-top: 0;">Halo,</p>
            <p style="font-size: 16px; line-height: 1.6; color: #475569;">Transaksi top up Anda dengan kode di bawah ini mengalami kegagalan pengiriman dari server partner provider.</p>
            
            <div class="alert-box">
                <p style="margin: 0; font-size: 14px; color: #b45309; font-weight: 700;">Pengembalian Dana (Refund):</p>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #78350f; line-height: 1.5;">Jangan khawatir! Dana Anda aman. Tim administrator kami akan segera menghubungi Anda melalui email atau WhatsApp untuk proses refund manual.</p>
            </div>

            <table class="details-table">
                <tr>
                    <td class="label">Kode Transaksi</td>
                    <td class="value">{{ $order->order_code }}</td>
                </tr>
                <tr>
                    <td class="label">Game</td>
                    <td class="value">{{ $order->product->game->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Item</td>
                    <td class="value">{{ $order->product->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">ID Tujuan</td>
                    <td class="value">{{ $order->customer_no }} @if($order->zone_id) ({{ $order->zone_id }}) @endif</td>
                </tr>
                <tr>
                    <td class="label">Jumlah Pengembalian</td>
                    <td class="value" style="color: #ef4444; font-size: 18px;">Rp {{ number_format($order->selling_price, 0, ',', '.') }}</td>
                </tr>
                @if($order->failure_reason)
                <tr>
                    <td class="label">Alasan Gagal</td>
                    <td class="value" style="color: #64748b; font-weight: 500;">{{ $order->failure_reason }}</td>
                </tr>
                @endif
            </table>
            
            <p style="font-size: 15px; color: #64748b; line-height: 1.6; margin-bottom: 0;">Mohon hubungi CS kami jika Anda memiliki pertanyaan lebih lanjut.</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} AZKA TOP UP. All rights reserved.</p>
            <p>Email ini dikirimkan secara otomatis oleh sistem.</p>
        </div>
    </div>
</body>
</html>

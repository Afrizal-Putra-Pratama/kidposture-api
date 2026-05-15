<?php
// app/Http/Controllers/PaymentController.php
//
// Pakai Subscription model yang sudah ada.
// Amount diambil dari physiotherapist.consultation_fee (dinamis, tidak hardcode).
//
// Pastikan sudah:
//   composer require midtrans/midtrans-php
//
// .env:
//   MIDTRANS_SERVER_KEY=SB-Mid-server-XXXX
//   MIDTRANS_IS_PRODUCTION=false
//
// config/services.php:
//   'midtrans' => [
//       'server_key'    => env('MIDTRANS_SERVER_KEY'),
//       'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
//   ],

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use App\Models\Subscription;
use App\Models\Physiotherapist;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    // ── POST /api/payments/create-transaction ────────────────────────────
    // Body  : { physiotherapist_id }
    // Return: { snap_token, order_id }
    //
    // Amount diambil dari physio.consultation_fee di backend —
    // TIDAK dari request body — agar tidak bisa dimanipulasi frontend.
    public function createTransaction(Request $request)
    {
        $request->validate([
            'physiotherapist_id' => 'required|exists:physiotherapists,id',
        ]);

        $user  = $request->user();
        $physio = Physiotherapist::findOrFail($request->physiotherapist_id);

        // Ambil harga dari data fisioterapis
        $amount = (int) ($physio->consultation_fee ?? 0);

        if ($amount < 1000) {
            return response()->json([
                'message' => 'Tarif konsultasi fisioterapis belum diatur.',
            ], 422);
        }

        // Cek apakah user sudah punya subscription aktif
        $activeSub = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expired_at', '>', now())
            ->first();

        if ($activeSub) {
            return response()->json([
                'message'    => 'Kamu sudah memiliki langganan aktif.',
                'is_premium' => true,
            ], 200);
        }

        $orderId = 'ORDER-' . strtoupper(Str::random(8)) . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone ?? '',
            ],
            'item_details' => [
                [
                    'id'       => 'CHAT-PHYSIO-' . $physio->id,
                    'price'    => $amount,
                    'quantity' => 1,
                    'name'     => 'Konsultasi Chat dengan ' . $physio->name,
                ],
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat transaksi: ' . $e->getMessage(),
            ], 500);
        }

        // Buat subscription dengan status pending
        Subscription::create([
            'user_id'              => $user->id,
            'plan'                 => 'premium',
            'status'               => 'pending',
            'midtrans_order_id'    => $orderId,
            'amount'               => $amount,
            // started_at & expired_at diisi saat webhook/verify sukses
        ]);

        return response()->json([
            'snap_token' => $snapToken,
            'order_id'   => $orderId,
        ]);
    }

    // ── POST /api/payments/verify ────────────────────────────────────────
    // Dipanggil frontend setelah Snap onSuccess.
    // Body: { order_id, transaction_status }
    public function verify(Request $request)
    {
        $request->validate([
            'order_id'           => 'required|string',
            'transaction_status' => 'required|string',
        ]);

        $sub = Subscription::where('midtrans_order_id', $request->order_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $this->applyStatus($sub, $request->transaction_status);

        return response()->json([
            'status'     => $sub->status,
            'is_premium' => $sub->isActive(),
        ]);
    }

    // ── POST /api/payments/webhook ───────────────────────────────────────
    // Server-to-server dari Midtrans (lebih andal dari verify).
    // Daftarkan di: Midtrans Dashboard → Settings → Configuration → Payment Notification URL
    // Contoh URL: https://yourdomain.com/api/payments/webhook
    // Route ini TANPA middleware auth.
    public function webhook(Request $request)
    {
        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid notification'], 400);
        }

        $sub = Subscription::where('midtrans_order_id', $notif->order_id)->first();
        if (!$sub) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Simpan payload lengkap untuk audit
        $sub->update([
            'midtrans_transaction_id' => $notif->transaction_id ?? null,
            'midtrans_payment_type'   => $notif->payment_type   ?? null,
            'midtrans_payload'        => $request->all(),
        ]);

        $txStatus    = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status ?? null;

        // capture hanya settle jika fraud_status = accept
        if ($txStatus === 'capture') {
            $txStatus = ($fraudStatus === 'accept') ? 'settlement' : 'deny';
        }

        $this->applyStatus($sub, $txStatus);

        return response()->json(['message' => 'OK']);
    }

    // ── Helper: mapping status Midtrans → update Subscription ────────────
    private function applyStatus(Subscription $sub, string $txStatus): void
    {
        switch ($txStatus) {
            case 'settlement':
            case 'capture':
                $sub->update([
                    'status'     => 'active',
                    'started_at' => now(),
                    'expired_at' => now()->addMonth(), // 1 bulan akses
                ]);
                break;

            case 'pending':
                $sub->update(['status' => 'pending']);
                break;

            case 'deny':
            case 'cancel':
            case 'expire':
            case 'failure':
                $sub->update(['status' => 'failed']);
                break;
        }
    }
}
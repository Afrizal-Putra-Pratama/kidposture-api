<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Physiotherapist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
        Config::$curlOptions  = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [],
        ];
    }

    // ── POST /api/subscription/create-payment ────────────────────────────
    public function createPayment(Request $request)
    {
        $request->validate([
            'physiotherapist_id' => 'required|exists:physiotherapists,id',
        ]);

        $user   = $request->user();
        $physio = Physiotherapist::findOrFail($request->physiotherapist_id);
        $amount = (int) ($physio->consultation_fee ?? 50000);

        if ($amount < 1000) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif konsultasi fisioterapis belum diatur.',
            ], 422);
        }

        // Cek akses aktif ke fisio ini
        $existing = Subscription::where('user_id', $user->id)
            ->where('physiotherapist_id', $physio->id)
            ->where('status', 'active')
            ->where('expired_at', '>', now())
            ->first();

        if ($existing) {
            return response()->json([
                'success'    => true,
                'message'    => 'Anda sudah memiliki akses ke fisioterapis ini.',
                'has_access' => true,
            ]);
        }

        $orderId = 'ORDER-' . $user->id . '-' . $physio->id . '-' . Str::random(6) . '-' . time();

        Subscription::create([
            'user_id'            => $user->id,
            'physiotherapist_id' => $physio->id,
            'plan'               => 'premium',
            'status'             => 'pending',
            'midtrans_order_id'  => $orderId,
            'amount'             => $amount,
            'expired_at'         => now()->addMonth(),
        ]);

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
            ],
            'item_details' => [[
                'id'       => 'CHAT-PHYSIO-' . $physio->id,
                'price'    => $amount,
                'quantity' => 1,
                'name'     => 'Chat dengan ' . $physio->name,
            ]],
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'success'    => true,
            'snap_token' => $snapToken,
            'order_id'   => $orderId,
            'amount'     => $amount,
        ]);
    }

    // ── POST /api/subscription/verify ────────────────────────────────────
    // Dipanggil frontend setelah Snap onSuccess.
    // Ini yang mengaktifkan akses karena webhook tidak jalan di localhost.
    // ── POST /api/subscription/verify ────────────────────────────────────────
public function verifyPayment(Request $request)
{
    $request->validate([
        'order_id'           => 'required|string',
        'transaction_status' => 'required|string',
    ]);

    $subscription = Subscription::where('midtrans_order_id', $request->order_id)
        ->where('user_id', $request->user()->id)
        ->first();

    if (!$subscription) {
        return response()->json([
            'success' => false,
            'message' => 'Subscription tidak ditemukan.',
        ], 404);
    }

    $txStatus = $request->transaction_status;

    if (in_array($txStatus, ['settlement', 'capture'])) {
        $subscription->update([
            'status'     => 'active',
            'started_at' => now(),
            'expired_at' => now()->addMonth(),
        ]);

        // ✅ FIX: Gunakan $request->user() langsung, bukan lewat relasi
        $request->user()->update(['is_premium' => true]);
    }

    $subscription->refresh();

    return response()->json([
        'success'    => true,
        'has_access' => $subscription->status === 'active',
        'status'     => $subscription->status,
    ]);
    }

    // ── GET /api/subscription/status ─────────────────────────────────────
    public function checkStatus(Request $request)
    {
        $user     = $request->user();
        $physioId = $request->query('physiotherapist_id');

        if ($physioId) {
            $subscription = Subscription::where('user_id', $user->id)
                ->where('physiotherapist_id', $physioId)
                ->where('status', 'active')
                ->where('expired_at', '>', now())
                ->latest()
                ->first();

            return response()->json([
                'success'      => true,
                'is_premium'   => (bool) $subscription,
                'has_access'   => (bool) $subscription,
                'subscription' => $subscription,
            ]);
        }

        $anyActive = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expired_at', '>', now())
            ->exists();

        return response()->json([
            'success'    => true,
            'is_premium' => $anyActive,
            'has_access' => $anyActive,
        ]);
    }

    // ── POST /api/subscription/webhook ───────────────────────────────────
    // Server-to-server dari Midtrans (production/staging dengan URL publik)
    public function webhook(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        $data      = $request->all();

        $signatureKey = hash('sha512',
            ($data['order_id']     ?? '') .
            ($data['status_code']  ?? '') .
            ($data['gross_amount'] ?? '') .
            $serverKey
        );

        if ($signatureKey !== ($data['signature_key'] ?? '')) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $subscription = Subscription::where('midtrans_order_id', $data['order_id'])->first();
        if (!$subscription) {
            return response()->json(['message' => 'Subscription not found'], 404);
        }

        $txStatus    = $data['transaction_status'] ?? '';
        $fraudStatus = $data['fraud_status'] ?? null;

        if (
            $txStatus === 'settlement' ||
            ($txStatus === 'capture' && $fraudStatus === 'accept')
        ) {
            $this->activateAccess($subscription, $data);
        } elseif (in_array($txStatus, ['cancel', 'deny', 'expire', 'failure'])) {
            $subscription->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'OK']);
    }

    private function activateAccess(Subscription $subscription, array $data): void
{
    $subscription->update([
        'status'                  => 'active',
        'midtrans_transaction_id' => $data['transaction_id'] ?? null,
        'midtrans_payment_type'   => $data['payment_type']   ?? null,
        'started_at'              => now(),
        'expired_at'              => now()->addMonth(),
        'midtrans_payload'        => $data,
    ]);

    // ✅ FIX: Load ulang relasi user secara eksplisit
    $subscription->load('user');

    if (!$subscription->user) {
        \Log::error('activateAccess: user not found for subscription ' . $subscription->id);
        return;
    }

    $hasAnyActive = Subscription::where('user_id', $subscription->user_id)
        ->where('status', 'active')
        ->where('expired_at', '>', now())
        ->exists();

    $subscription->user->update(['is_premium' => $hasAnyActive]);
}
}
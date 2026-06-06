<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Screening;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * ScreeningPdfController
 *
 * SETUP (jalankan sekali di terminal server):
 *   composer require barryvdh/laravel-dompdf
 *
 * Lalu publish config (opsional):
 *   php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
 *
 * Endpoint:
 *   GET /api/screenings/{screening}/download-pdf
 *   Header: Authorization: Bearer {token}
 *
 * Response:
 *   File PDF langsung diunduh oleh browser (Content-Disposition: attachment)
 */
class ScreeningPdfController extends Controller
{
    public function download(Request $request, Screening $screening)
    {
        // --- Authorization ---
        $user = $request->user();

        if (strtolower($user->role) !== 'physio') {
            if ($screening->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        }

        // --- Load relasi ---
        $screening->load([
            'child:id,name,birth_date,gender,weight,height',
            'images',
            'manualRecommendations.physio:id,name',
            'physiotherapist:id,name,clinic_name,city,specialty',
        ]);

        // --- Siapkan data untuk view ---
        $child    = $screening->child;
        $ageYears = $child->birth_date
            ? Carbon::parse($child->birth_date)->age
            : null;

        $mainImages = $screening->images->filter(fn($img) => !str_starts_with($img->type, 'CROP_'));
        $cropImages = $screening->images->filter(fn($img) =>  str_starts_with($img->type, 'CROP_'));

        // Konversi gambar ke base64 agar dompdf tidak perlu fetch URL eksternal
        // (URL Cloudinary di-fetch server-side, bukan client-side)
        $mainImagesData = $mainImages->map(function ($img) {
            return [
                'id'              => $img->id,
                'type'            => $img->type,
                'type_label'      => $this->typeLabel($img->type),
                'url_original'    => asset('storage/' . $img->path),
                'url_processed'   => $img->processed_path
                    ? (str_starts_with($img->processed_path, 'http')
                        ? $img->processed_path
                        : asset('storage/' . $img->processed_path))
                    : null,
                'image_base64'    => $this->imageToBase64(
                    $img->processed_path
                        ? (str_starts_with($img->processed_path, 'http')
                            ? $img->processed_path
                            : storage_path('app/public/' . $img->processed_path))
                        : storage_path('app/public/' . $img->path)
                ),
                'is_processed'    => !empty($img->processed_path),
                'recommendations' => $img->recommendations ?? [],
            ];
        })->values();

        $cropImagesData = $cropImages->map(function ($img) {
            return [
                'id'           => $img->id,
                'type'         => $img->type,
                'type_label'   => $this->cropLabel($img->type),
                'image_base64' => $this->imageToBase64(
                    storage_path('app/public/' . $img->path)
                ),
            ];
        })->values();

        // --- Konfigurasi kategori ---
        $catConfig = [
            'GOOD'      => ['label' => 'Postur Baik',     'color' => '#16a34a'],
            'FAIR'      => ['label' => 'Perlu Dipantau',  'color' => '#d97706'],
            'ATTENTION' => ['label' => 'Perlu Perhatian', 'color' => '#dc2626'],
        ];
        $cat        = $catConfig[$screening->category] ?? ['label' => '-', 'color' => '#6b7280'];
        $themeColor = $cat['color'];

        $metrics  = $screening->metrics ?? [];
        $findings = $metrics['findings'] ?? [];

        // AI Recommendations dari images
        $aiRecs = [];
        foreach ($mainImagesData as $img) {
            if (!empty($img['recommendations'])) {
                foreach ($img['recommendations'] as $rec) {
                    $rec['view'] = $img['type_label'];
                    $aiRecs[]    = $rec;
                }
            }
        }

        // Status referral
        $statusMap = [
            'none'      => 'Belum ada konsultasi',
            'requested' => 'Menunggu konfirmasi fisioterapis',
            'accepted'  => 'Sedang dalam penanganan',
            'completed' => 'Selesai konsultasi',
            'cancelled' => 'Dibatalkan',
        ];

        $printDate  = now()->translatedFormat('d F Y');
        $printTime  = now()->format('H:i');
        $screenDate = $screening->created_at
            ? $screening->created_at->translatedFormat('d F Y')
            : '-';

        $childName = $child->name ?? 'Anak';
        $filename  = 'Laporan-Screening-' . str_replace(' ', '-', $childName) . '-' . now()->format('Y-m-d') . '.pdf';

        // --- Render HTML view ---
        $html = view('pdf.screening', [
            'child'                  => $child,
            'ageYears'               => $ageYears,
            'screening'              => $screening,
            'score'                  => $screening->score,
            'category'               => $screening->category,
            'cat'                    => $cat,
            'themeColor'             => $themeColor,
            'summary'                => $screening->summary,
            'metrics'                => $metrics,
            'findings'               => $findings,
            'mainImages'             => $mainImagesData,
            'cropImages'             => $cropImagesData,
            'aiRecs'                 => $aiRecs,
            'manualRecommendations'  => $screening->manualRecommendations,
            'physiotherapist'        => $screening->physiotherapist,
            'referral_status'        => $screening->referral_status,
            'statusMap'              => $statusMap,
            'printDate'              => $printDate,
            'printTime'              => $printTime,
            'screenDate'             => $screenDate,
        ])->render();

        // --- Generate PDF dengan dompdf ---
        $pdf = app('dompdf.wrapper');

        $pdf->loadHTML($html);

        $pdf->setPaper('A4', 'portrait');

        // Opsi dompdf: aktifkan remote (untuk gambar external jika base64 gagal)
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,  // izinkan load gambar dari URL
            'defaultFont'          => 'sans-serif',
            'dpi'                  => 96,
        ]);

        // Stream sebagai download langsung — browser auto-download, tidak ada print dialog
        return $pdf->download($filename);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Konversi path file lokal atau URL Cloudinary ke base64 data URI.
     * Dompdf sangat reliable dengan base64 dibanding URL eksternal.
     */
    private function imageToBase64(string $pathOrUrl): ?string
    {
        try {
            // Jika URL eksternal (Cloudinary dll)
            if (str_starts_with($pathOrUrl, 'http')) {
                $context = stream_context_create([
                    'http' => [
                        'timeout'  => 10,
                        'header'   => 'User-Agent: Mozilla/5.0',
                    ],
                    'ssl'  => [
                        'verify_peer'      => false,
                        'verify_peer_name' => false,
                    ],
                ]);
                $content = @file_get_contents($pathOrUrl, false, $context);
            } else {
                // File lokal
                if (!file_exists($pathOrUrl)) {
                    return null;
                }
                $content = file_get_contents($pathOrUrl);
            }

            if (empty($content)) {
                return null;
            }

            // Deteksi mime type
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($content);

            // Fallback mime
            if (!$mimeType || !str_starts_with($mimeType, 'image/')) {
                $ext = strtolower(pathinfo($pathOrUrl, PATHINFO_EXTENSION));
                $mimeMap = [
                    'jpg'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png'  => 'image/png',
                    'gif'  => 'image/gif',
                    'webp' => 'image/webp',
                ];
                $mimeType = $mimeMap[$ext] ?? 'image/jpeg';
            }

            return 'data:' . $mimeType . ';base64,' . base64_encode($content);
        } catch (\Throwable $e) {
            \Log::warning('PDF: gagal konversi gambar ke base64', [
                'path'  => $pathOrUrl,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function typeLabel(string $type = ''): string
    {
        $map = [
            'FRONT'  => 'Tampak Depan',
            'BACK'   => 'Tampak Belakang',
            'SIDE'   => 'Tampak Samping',
            'SIDE_L' => 'Tampak Samping Kiri',
            'SIDE_R' => 'Tampak Samping Kanan',
            'LEFT'   => 'Tampak Kiri',
            'RIGHT'  => 'Tampak Kanan',
        ];
        return $map[strtoupper($type)] ?? $type;
    }

    private function cropLabel(string $type = ''): string
    {
        $region = strtoupper(preg_replace('/^CROP_/i', '', $type));
        $map    = [
            'SHOULDER' => 'Area Bahu',
            'HIP'      => 'Area Panggul',
            'HEAD'     => 'Area Kepala',
            'NECK'     => 'Area Leher',
            'TORSO'    => 'Area Punggung',
        ];
        return $map[$region] ?? $region;
    }
}
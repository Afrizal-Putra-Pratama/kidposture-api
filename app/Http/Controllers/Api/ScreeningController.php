<?php

namespace App\Http\Controllers\Api;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Screening;
use App\Models\ScreeningImage;
use App\Models\ScreeningRecommendation;
use App\Services\PostureAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScreeningController extends Controller
{
    public function index(Request $request, Child $child)
    {
        if ($child->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $screenings = $child->screenings()
            ->with('images')
            ->latest()
            ->get();

        return response()->json($screenings);
    }

    private function uploadToCloudinary(string $filePath): ?string
    {
        $cloudName = env('CLOUDINARY_CLOUD_NAME', 'demdodupd');
        $apiKey    = env('CLOUDINARY_API_KEY', '435173116591269');
        $apiSecret = env('CLOUDINARY_API_SECRET', 'PlY-kPX4v2bbcU0Zr6odXbUEc_E');
        $timestamp = time();
        $signature = sha1("folder=screenings&timestamp={$timestamp}{$apiSecret}");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'file'      => new \CURLFile($filePath),
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder'    => 'screenings',
        ]);

        $result = curl_exec($ch);
        $error  = curl_error($ch);
        curl_close($ch);

        if ($error) {
            \Log::error('Cloudinary cURL error', ['error' => $error]);
            return null;
        }

        $data = json_decode($result, true);

        if (empty($data['secure_url'])) {
            \Log::error('Cloudinary upload failed', ['result' => $result]);
            return null;
        }

        return $data['secure_url'];
    }

    public function store(Request $request, Child $child, PostureAiService $aiService)
    {
        if ($child->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'images'         => 'required|array|min:1|max:3',
            'images.*.type'  => 'required|in:FRONT,SIDE,BACK',
            'images.*.image' => 'required|image|max:5120',
        ]);

        $screening = Screening::create([
            'child_id'           => $child->id,
            'user_id'            => $request->user()->id,
            'score'              => null,
            'category'           => null,
            'metrics'            => null,
            'summary'            => null,
            'is_multi_view'      => count($data['images']) > 1,
            'total_views'        => count($data['images']),
            'physiotherapist_id' => null,
            'referral_status'    => 'none',
        ]);

        $allScores  = [];
        $allMetrics = [];

        foreach ($data['images'] as $imageData) {
            $file = $imageData['image'];
            $type = $imageData['type'];

            $filename  = uniqid('screening_') . '.' . $file->getClientOriginalExtension();
            $directory = 'screenings';
            Storage::disk('public')->put($directory . '/' . $filename, file_get_contents($file->getPathname()));
            $path = $directory . '/' . $filename;

            $screeningImage = ScreeningImage::create([
                'screening_id'    => $screening->id,
                'type'            => $type,
                'path'            => $path,
                'processed_path'  => null,
                'recommendations' => null,
            ]);

            // Upload ke Cloudinary via cURL (bypass SSL lokal)
            $cloudinaryUrl = $this->uploadToCloudinary(storage_path('app/public/' . $path));

            // Fallback ke URL lokal kalau Cloudinary gagal
            if (!$cloudinaryUrl) {
                $cloudinaryUrl = asset('storage/' . $path);
            }

            $aiResult = $aiService->analyze($cloudinaryUrl, $type);

            // Overlay image
            if (!empty($aiResult['overlay_image_url'])) {
                $screeningImage->update([
                'processed_path' => $aiResult['overlay_image_url'], // URL Cloudinary langsung
                ]);
            }

            // Crop images
            if (!empty($aiResult['crop_images']) && is_array($aiResult['crop_images'])) {
                foreach ($aiResult['crop_images'] as $crop) {
                    $cropUrl = $crop['url'] ?? null;
                    $region  = $crop['region'] ?? 'unknown';

                    if (!$cropUrl) continue;

                    $cropContent = @file_get_contents($cropUrl);

                    if ($cropContent !== false && strlen($cropContent) > 500) {
                        $cropFilename = 'screenings/' . uniqid('crop_' . $region . '_') . '.png';
                        Storage::disk('public')->put($cropFilename, $cropContent);

                        ScreeningImage::create([
                            'screening_id'    => $screening->id,
                            'type'            => 'CROP_' . strtoupper($region),
                            'path'            => $cropFilename,
                            'processed_path'  => null,
                            'recommendations' => null,
                        ]);
                    }
                }
            }

            $allScores[]  = $aiResult['score'] ?? 0;
            $allMetrics[] = $aiResult['metrics'] ?? [];
        }

        $avgScore = count($allScores) > 0 ? array_sum($allScores) / count($allScores) : 0;

        if ($avgScore >= 85) {
            $category = 'GOOD';
        } elseif ($avgScore >= 70) {
            $category = 'FAIR';
        } else {
            $category = 'ATTENTION';
        }

        $mergedMetrics = [];
        if (count($allMetrics) > 0) {
            $keys = [
                'shoulder_tilt_index',
                'hip_tilt_index',
                'forward_head_index',
                'neck_inclination_deg',
                'torso_inclination_deg',
            ];

            foreach ($keys as $key) {
                $values = array_filter(array_column($allMetrics, $key), function ($v) {
                    return $v !== null && $v !== '';
                });
                if (count($values) > 0) {
                    $mergedMetrics[$key] = array_sum($values) / count($values);
                }
            }
            $mergedMetrics['raw_score'] = $avgScore;

            $allFindings = [];
            foreach ($allMetrics as $m) {
                if (!empty($m['findings']) && is_array($m['findings'])) {
                    $allFindings = array_merge($allFindings, $m['findings']);
                }
            }
            if (count($allFindings) > 0) {
                $mergedMetrics['findings'] = $allFindings;
            }
        }

        $summaryFormatter = app(\App\Services\ScreeningSummaryFormatter::class);
        $screening->update([
            'score'    => $avgScore,
            'category' => $category,
            'metrics'  => $mergedMetrics,
            'summary'  => $summaryFormatter->makeSummary($screening),
        ]);

        $screening->load('images', 'child');

        return response()->json($screening, 201);
    }

    public function show(Request $request, Screening $screening)
    {
        $user = auth()->user();

        if (strtolower($user->role) !== 'physio') {
            if ($screening->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }
        }

        $screening->load([
            'child:id,name,birth_date,gender,weight,height',
            'images',
            'manualRecommendations.physio:id,name',
            'physiotherapist:id,name,clinic_name,city,specialty',
        ]);

        $child    = $screening->child;
        $ageYears = $child->birth_date
            ? \Carbon\Carbon::parse($child->birth_date)->age
            : null;

        return response()->json([
            'success' => true,
            'data'    => [
                'id'              => $screening->id,
                'score'           => $screening->score,
                'category'        => $screening->category,
                'summary'         => $screening->summary,
                'metrics'         => $screening->metrics,
                'is_multi_view'   => $screening->is_multi_view,
                'total_views'     => $screening->total_views,
                'created_at'      => $screening->created_at,
                'referral_status' => $screening->referral_status,
                'physiotherapist' => $screening->physiotherapist,

                'child' => [
                    'id'         => $child->id,
                    'name'       => $child->name,
                    'age_years'  => $ageYears,
                    'birth_date' => $child->birth_date,
                    'gender'     => $child->gender,
                    'weight'     => $child->weight,
                    'height'     => $child->height,
                ],

                'images' => $screening->images->map(function ($img) {
                    return [
                        'id'              => $img->id,
                        'type'            => $img->type,
                        'url_original'    => asset('storage/' . $img->path),
                        'url_processed' => $img->processed_path
                            ? (str_starts_with($img->processed_path, 'http')
                            ? $img->processed_path
                            : asset('storage/' . $img->processed_path))
                            : null,
                        'recommendations' => $img->recommendations ?? [],
                    ];
                }),

                'manualRecommendations' => $screening->manualRecommendations,
            ],
        ]);
    }

    public function referToPhysio(Request $request, Screening $screening)
    {
        $user = $request->user();

        if ($screening->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $data = $request->validate([
            'physiotherapist_id' => 'required|exists:physiotherapists,id',
        ]);

        if (!in_array(strtoupper((string) $screening->category), ['ATTENTION', 'FAIR'])) {
            return response()->json([
                'success' => false,
                'message' => 'Screening ini tidak memerlukan rujukan fisioterapis.',
            ], 422);
        }

        $screening->update([
            'physiotherapist_id' => $data['physiotherapist_id'],
            'referral_status'    => 'requested',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rujukan ke fisioterapis berhasil dibuat.',
            'data'    => $screening->load('physiotherapist'),
        ]);
    }

    public function storeRecommendation(Request $request, Screening $screening)
    {
        $validated = $request->validate([
            'type'      => 'required|in:exercise,education,note,referral',
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'media_url' => 'nullable|url',
        ]);

        $recommendation = ScreeningRecommendation::create([
            'screening_id' => $screening->id,
            'physio_id'    => auth()->id(),
            'type'         => $validated['type'],
            'title'        => $validated['title'],
            'content'      => $validated['content'],
            'media_url'    => $validated['media_url'] ?? null,
        ]);

        NotificationHelper::sendToParent(
            $screening->id,
            'new_recommendation',
            'Rekomendasi Baru',
            'Fisioterapis telah menambahkan rekomendasi: ' . $validated['title']
        );

        return response()->json([
            'success' => true,
            'message' => 'Rekomendasi berhasil disimpan',
            'data'    => $recommendation->load('physio:id,name'),
        ], 201);
    }

    public function getRecommendations(Screening $screening)
    {
        $recommendations = $screening->manualRecommendations()
            ->with('physio:id,name')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $recommendations,
        ]);
    }

    public function physioIndex(Request $request)
    {
        $screenings = Screening::with(['child.user'])
            ->whereNotNull('category')
            ->where(function ($q) {
                $q->whereRaw('LOWER(category) like ?', ['%attention%'])
                  ->orWhereRaw('LOWER(category) like ?', ['%perlu perhatian%']);
            })
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $screenings,
        ]);
    }

    public function myReferrals(Request $request)
    {
        $user  = $request->user();
        $physio = $user->physiotherapist ?? null;

        if (!$physio) {
            return response()->json([
                'success' => false,
                'message' => 'Profil fisioterapis tidak ditemukan.',
            ], 404);
        }

        $screenings = Screening::with(['child.user'])
            ->where('physiotherapist_id', $physio->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $screenings->map(function (Screening $s) {
                return [
                    'id'              => $s->id,
                    'score'           => $s->score,
                    'category'        => $s->category,
                    'summary'         => $s->summary,
                    'created_at'      => $s->created_at,
                    'referral_status' => $s->referral_status,
                    'child'  => [
                        'id'        => $s->child->id,
                        'name'      => $s->child->name,
                        'age_years' => $s->child->age_years ?? null,
                    ],
                    'parent' => [
                        'id'    => $s->child->user->id,
                        'name'  => $s->child->user->name,
                        'email' => $s->child->user->email,
                    ],
                ];
            }),
        ]);
    }

    public function updateReferralStatus(Request $request, Screening $screening)
    {
        $user   = $request->user();
        $physio = $user->physiotherapist ?? null;

        if (!$physio || $screening->physiotherapist_id !== $physio->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $data = $request->validate([
            'status' => 'required|in:requested,accepted,completed,cancelled',
        ]);

        $screening->update([
            'referral_status' => $data['status'],
        ]);

        if (in_array($data['status'], ['accepted', 'completed'])) {
            $type = $data['status'] === 'accepted' ? 'referral_accepted' : 'referral_completed';
            $title = $data['status'] === 'accepted' ? 'Rujukan Diterima' : 'Konsultasi Selesai';
            $message = $data['status'] === 'accepted'
                ? 'Fisioterapis telah menerima rujukan screening untuk anak Anda.'
                : 'Fisioterapis telah menyelesaikan konsultasi untuk anak Anda. Silakan cek rekomendasi terbaru.';

            NotificationHelper::sendToParent($screening->id, $type, $title, $message);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status rujukan diperbarui.',
            'data'    => $screening,
        ]);
    }
}
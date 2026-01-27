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
    // List screening per anak (untuk orang tua)
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

    // Buat screening baru + upload + overlay + crops + recommendations
    public function store(Request $request, Child $child, PostureAiService $aiService)
    {
        if ($child->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'images' => 'required|array|min:1|max:3',
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
                'screening_id'   => $screening->id,
                'type'           => $type,
                'path'           => $path,
                'processed_path' => null,
                'recommendations'=> null,
            ]);

            $publicUrl = asset('storage/' . $path);
            $aiResult  = $aiService->analyze($publicUrl, $type);

            if (!empty($aiResult['overlay_image_url'])) {
                $overlayUrl     = $aiResult['overlay_image_url'];
                $overlayContent = @file_get_contents($overlayUrl);

                if ($overlayContent !== false && strlen($overlayContent) > 1000) {
                    $overlayFilename = 'screenings/' . uniqid('overlay_') . '.png';
                    Storage::disk('public')->put($overlayFilename, $overlayContent);

                    $screeningImage->update([
                        'processed_path'  => $overlayFilename,
                        'recommendations' => $aiResult['recommendations'] ?? [],
                    ]);
                }
            }

            if (!empty($aiResult['crop_images']) && is_array($aiResult['crop_images'])) {
                foreach ($aiResult['crop_images'] as $crop) {
                    $cropUrl = $crop['url'] ?? null;
                    $region  = $crop['region'] ?? 'unknown';

                    if (!$cropUrl) {
                        continue;
                    }

                    $cropContent = @file_get_contents($cropUrl);

                    if ($cropContent !== false && strlen($cropContent) > 500) {
                        $cropFilename = 'screenings/' . uniqid('crop_' . $region . '_') . '.png';
                        Storage::disk('public')->put($cropFilename, $cropContent);

                        ScreeningImage::create([
                            'screening_id'   => $screening->id,
                            'type'           => 'CROP_' . strtoupper($region),
                            'path'           => $cropFilename,
                            'processed_path' => null,
                            'recommendations'=> null,
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
                $values = array_filter(array_column($allMetrics, $key));
                if (count($values) > 0) {
                    $mergedMetrics[$key] = array_sum($values) / count($values);
                }
            }
            $mergedMetrics['raw_score'] = $avgScore;
        }

        $summaryFormatter = app(\App\Services\ScreeningSummaryFormatter::class);
        $screening->update([
            'score'   => $avgScore,
            'category'=> $category,
            'metrics' => $mergedMetrics,
            'summary' => $summaryFormatter->makeSummary($screening),
        ]);

        $screening->load('images', 'child');

        return response()->json($screening, 201);
    }

    // Detail screening (parent & fisio)
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

        $child = $screening->child;
        $ageYears = $child->birth_date
            ? \Carbon\Carbon::parse($child->birth_date)->age
            : null;

        return response()->json([
            'success' => true,
            'data' => [
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
                        'url_processed'   => $img->processed_path
                            ? asset('storage/' . $img->processed_path)
                            : null,
                        'recommendations' => $img->recommendations ?? [],
                    ];
                }),

                'manualRecommendations' => $screening->manualRecommendations,
            ],
        ]);
    }

    // Parent pilih fisioterapis untuk screening
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

        if (!in_array(strtoupper((string)$screening->category), ['ATTENTION', 'FAIR'])) {
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

    // Simpan rekomendasi manual + notifikasi
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

        // 🔔 Notifikasi ke parent
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

    // (Opsional) physioIndex lama
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
            'data' => $screenings,
        ]);
    }

    // List referral ke fisioterapis yang login – dipakai dashboard
    public function myReferrals(Request $request)
    {
        $user = $request->user();

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
                    'child' => [
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

    // Fisio update status referral + notifikasi
    public function updateReferralStatus(Request $request, Screening $screening)
    {
        $user = $request->user();
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

        // 🔔 Notifikasi ke parent sesuai status
        if (in_array($data['status'], ['accepted', 'completed'])) {
            $type = $data['status'] === 'accepted'
                ? 'referral_accepted'
                : 'referral_completed';

            $title = $data['status'] === 'accepted'
                ? 'Rujukan Diterima'
                : 'Konsultasi Selesai';

            $message = $data['status'] === 'accepted'
                ? 'Fisioterapis telah menerima rujukan screening untuk anak Anda.'
                : 'Fisioterapis telah menyelesaikan konsultasi untuk anak Anda. Silakan cek rekomendasi terbaru.';

            NotificationHelper::sendToParent(
                $screening->id,
                $type,
                $title,
                $message
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Status rujukan diperbarui.',
            'data'    => $screening,
        ]);
    }
}

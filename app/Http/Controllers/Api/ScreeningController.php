<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Screening;
use App\Models\ScreeningImage;
use App\Services\PostureAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ScreeningController extends Controller
{
    // List screening per anak
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

        // VALIDASI: bisa upload 1-3 foto sekaligus
        $data = $request->validate([
            'images' => 'required|array|min:1|max:3',
            'images.*.type' => 'required|in:FRONT,SIDE,BACK',
            'images.*.image' => 'required|image|max:5120',
        ]);

        // Buat screening SATU record
        $screening = Screening::create([
            'child_id' => $child->id,
            'user_id' => $request->user()->id,
            'score' => null,
            'category' => null,
            'metrics' => null,
            'summary' => null,
            'is_multi_view' => count($data['images']) > 1,
            'total_views' => count($data['images']),
        ]);

        $allScores = [];
        $allMetrics = [];

        // LOOP setiap foto
        foreach ($data['images'] as $imageData) {
            $file = $imageData['image'];
            $type = $imageData['type'];

            // Simpan original
            $filename = uniqid('screening_') . '.' . $file->getClientOriginalExtension();
            $directory = 'screenings';
            Storage::disk('public')->put($directory . '/' . $filename, file_get_contents($file->getPathname()));
            $path = $directory . '/' . $filename;

            $screeningImage = ScreeningImage::create([
                'screening_id' => $screening->id,
                'type' => $type,
                'path' => $path,
                'processed_path' => null,
                'recommendations' => null,
            ]);

            // Analisis AI
            $publicUrl = asset('storage/' . $path);
            
            
            
            $aiResult = $aiService->analyze($publicUrl, $type);
            
            

            // Download overlay + SAVE RECOMMENDATIONS
            if (!empty($aiResult['overlay_image_url'])) {
                $overlayUrl = $aiResult['overlay_image_url'];
                $overlayContent = @file_get_contents($overlayUrl);
                
                if ($overlayContent !== false && strlen($overlayContent) > 1000) {
                    $overlayFilename = 'screenings/' . uniqid('overlay_') . '.png';
                    Storage::disk('public')->put($overlayFilename, $overlayContent);
                    
                    
                    
                    // ✅ UPDATE dengan recommendations
                    $screeningImage->update([
                        'processed_path' => $overlayFilename,
                        'recommendations' => $aiResult['recommendations'] ?? [],
                    ]);
                    
                
                }
            }

            // Download crops
            if (!empty($aiResult['crop_images']) && is_array($aiResult['crop_images'])) {
                foreach ($aiResult['crop_images'] as $crop) {
                    $cropUrl = $crop['url'] ?? null;
                    $region = $crop['region'] ?? 'unknown';
                    
                    if (!$cropUrl) continue;
                    
                    $cropContent = @file_get_contents($cropUrl);
                    
                    if ($cropContent !== false && strlen($cropContent) > 500) {
                        $cropFilename = 'screenings/' . uniqid('crop_' . $region . '_') . '.png';
                        Storage::disk('public')->put($cropFilename, $cropContent);
                        
                        ScreeningImage::create([
                            'screening_id' => $screening->id,
                            'type' => 'CROP_' . strtoupper($region),
                            'path' => $cropFilename,
                            'processed_path' => null,
                            'recommendations' => null,
                        ]);
                    }
                }
            }

            // Kumpulkan scores untuk average
            $allScores[] = $aiResult['score'] ?? 0;
            $allMetrics[] = $aiResult['metrics'] ?? [];
        }

        // HITUNG AVERAGE SCORE
        $avgScore = count($allScores) > 0 ? array_sum($allScores) / count($allScores) : 0;

        // Tentukan category based on avg
        if ($avgScore >= 85) {
            $category = 'GOOD';
        } elseif ($avgScore >= 70) {
            $category = 'FAIR';
        } else {
            $category = 'ATTENTION';
        }

        // Merge metrics (ambil average juga)
        $mergedMetrics = [];
        if (count($allMetrics) > 0) {
            $keys = ['shoulder_tilt_index', 'hip_tilt_index', 'forward_head_index', 'neck_inclination_deg', 'torso_inclination_deg'];
            foreach ($keys as $key) {
                $values = array_filter(array_column($allMetrics, $key));
                if (count($values) > 0) {
                    $mergedMetrics[$key] = array_sum($values) / count($values);
                }
            }
            $mergedMetrics['raw_score'] = $avgScore;
        }

        // Update screening
        $summaryFormatter = app(\App\Services\ScreeningSummaryFormatter::class);
        $screening->update([
            'score' => $avgScore,
            'category' => $category,
            'metrics' => $mergedMetrics,
            'summary' => $summaryFormatter->makeSummary($screening),
        ]);

        $screening->load('images', 'child');

        return response()->json($screening, 201);
    }

    // Detail screening dengan RECOMMENDATIONS
    public function show(Request $request, Screening $screening)
    {
        if ($screening->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $screening->load(['child', 'images']);

        $child = $screening->child;
        $ageYears = $child->birth_date
            ? \Carbon\Carbon::parse($child->birth_date)->age
            : null;

        return response()->json([
            'id'        => $screening->id,
            'score'     => $screening->score,
            'category'  => $screening->category,
            'summary'   => $screening->summary,
            'metrics'   => $screening->metrics,
            'is_multi_view' => $screening->is_multi_view,
            'total_views'   => $screening->total_views,
            'created_at'=> $screening->created_at,

            'child' => [
                'id'          => $child->id,
                'name'        => $child->name,
                'age_years'   => $ageYears,
                'birth_date'  => $child->birth_date,
                'gender'      => $child->gender,
                'weight'      => $child->weight,
                'height'      => $child->height,
            ],

            'images' => $screening->images->map(function ($img) {
                return [
                    'id'             => $img->id,
                    'type'           => $img->type,
                    'url_original'   => asset('storage/' . $img->path),
                    'url_processed'  => $img->processed_path 
                        ? asset('storage/' . $img->processed_path) 
                        : null,
                    'recommendations' => $img->recommendations ?? [],
                ];
            }),
        ]);
    }
}

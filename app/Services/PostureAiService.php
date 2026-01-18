<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PostureAiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.posture_ai.url', 'http://localhost:8001');
    }

    public function analyze(string $imageUrl, string $view = 'FRONT'): array
    {
        try {

            $response = Http::timeout(60)->post($this->baseUrl . '/analyze-posture', [
                'image_url' => $imageUrl,
                'view' => $view,
            ]);

            if (!$response->successful()) {
                Log::error('=== AI SERVICE ERROR ===', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                
                return [
                    'score' => 0,
                    'category' => 'UNKNOWN',
                    'metrics' => [],
                    'summary' => 'Gagal menganalisis postur. Coba lagi nanti.',
                    'overlay_image_url' => null,
                    'crop_images' => [],
                    'recommendations' => [],  // ✅ DEFAULT EMPTY
                ];
            }

            $data = $response->json();

            // ✅ RETURN SEMUA DATA (termasuk recommendations & crop_images)
            return [
                'score' => $data['score'] ?? 0,
                'category' => $data['category'] ?? 'UNKNOWN',
                'metrics' => $data['metrics'] ?? [],
                'summary' => $data['summary'] ?? '',
                'overlay_image_url' => $data['overlay_image_url'] ?? null,
                'crop_images' => $data['crop_images'] ?? [],              // ✅ CROPS
                'recommendations' => $data['recommendations'] ?? [],        // ✅ RECOMMENDATIONS
            ];

        } catch (\Exception $e) {
            Log::error('=== AI SERVICE EXCEPTION ===', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'score' => 0,
                'category' => 'UNKNOWN',
                'metrics' => [],
                'summary' => 'Error: ' . $e->getMessage(),
                'overlay_image_url' => null,
                'crop_images' => [],
                'recommendations' => [],
            ];
        }
    }
}

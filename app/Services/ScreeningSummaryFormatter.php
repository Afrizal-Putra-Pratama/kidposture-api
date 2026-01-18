<?php

namespace App\Services;

use App\Models\Screening;
use Carbon\Carbon;

class ScreeningSummaryFormatter
{
    public function makeSummary(Screening $screening): string
    {
        $child = $screening->child;
        $age = $child->birth_date ? Carbon::parse($child->birth_date)->age : null;
        $category = $screening->category ?? 'UNKNOWN';  // ✅ FIX: fallback null
        $metrics = $screening->metrics ?? [];

        if (! $age) {
            return $this->genericSummary($category);
        }

        if ($age >= 4 && $age <= 6) {
            return $this->summaryFor4To6($category, $metrics);
        } elseif ($age >= 7 && $age <= 10) {
            return $this->summaryFor7To10($category, $metrics);
        }

        return $this->summaryFor11Plus($category, $metrics);
    }

    protected function genericSummary(string $category): string
    {
        // ✅ Sudah aman (match handle default)
        return match ($category) {
            'GOOD'      => 'Postur anak tampak cukup seimbang. Pertahankan kebiasaan duduk dan berdiri tegak dalam aktivitas sehari-hari.',
            'FAIR'      => 'Postur anak menunjukkan sedikit ketidakseimbangan. Perlu mulai memperhatikan posisi duduk, berdiri, dan cara membawa tas.',
            'ATTENTION' => 'Postur anak tampak perlu perhatian. Disarankan konsultasi ke fisioterapis untuk pemeriksaan lebih lanjut.',
            default     => 'Hasil skrining menunjukkan variasi postur yang perlu dipantau. Perhatikan kebiasaan postur anak sehari-hari.',
        };
    }

    protected function summaryFor4To6(string $category, array $metrics): string
    {
        // ✅ FIX: fallback default
        return match ($category) {
            'GOOD' => 'Untuk usia dini, postur anak terlihat cukup baik. Tetap dukung anak aktif bergerak dan batasi posisi duduk terlalu lama.',
            'FAIR' => 'Postur anak menunjukkan sedikit ketidakseimbangan. Ajak anak sering mengubah posisi duduk dan bermain dengan gerakan yang melatih keseimbangan.',
            'ATTENTION' => 'Postur anak tampak perlu diperhatikan. Dianjurkan konsultasi dengan fisioterapis untuk mendapatkan latihan sederhana yang bisa dilakukan di rumah.',
            default => $this->genericSummary($category),
        };
    }

    protected function summaryFor7To10(string $category, array $metrics): string
    {
        // ✅ FIX: fallback default
        return match ($category) {
            'GOOD' => 'Postur anak terlihat seimbang. Jaga posisi duduk saat belajar dan pastikan tas sekolah tidak terlalu berat dan dibawa dengan dua tali.',
            'FAIR' => 'Postur anak menunjukkan sedikit ketidakseimbangan. Perhatikan posisi duduk saat belajar dan kebiasaan membawa tas di satu sisi.',
            'ATTENTION' => 'Postur anak tampak perlu perhatian. Dianjurkan konsultasi ke fisioterapis untuk evaluasi lebih lanjut dan latihan korektif.',
            default => $this->genericSummary($category),
        };
    }

    protected function summaryFor11Plus(string $category, array $metrics): string
    {
        // ✅ FIX: fallback default
        return match ($category) {
            'GOOD' => 'Postur anak tampak baik. Pertahankan aktivitas fisik yang memperkuat otot punggung dan perut, serta kebiasaan duduk tegak.',
            'FAIR' => 'Postur anak menunjukkan kecenderungan tidak seimbang. Dianjurkan latihan peregangan dan penguatan otot punggung secara teratur.',
            'ATTENTION' => 'Postur anak tampak memerlukan perhatian khusus. Konsultasi dengan fisioterapis dianjurkan untuk mencegah keluhan nyeri di kemudian hari.',
            default => $this->genericSummary($category),
        };
    }
}

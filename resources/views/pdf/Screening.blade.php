<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"/>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Laporan Screening — {{ $child->name ?? 'Anak' }}</title>
  <style>
    /* ── RESET & BASE ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      color: #334155; 
      font-size: 11px;
      line-height: 1.6;
      background: #ffffff;
    }

    .page {
      padding: 35px 40px;
    }

    .page-break {
      page-break-before: always;
    }

    .clearfix::after { content: ''; display: table; clear: both; }

    /* ── HEADER ── */
    .header {
      margin-bottom: 25px;
      border-bottom: 1px solid #e2e8f0;
      padding-bottom: 15px;
    }

    .brand-title {
      text-align: center;
      font-size: 22px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #0f172a;
      margin-bottom: 18px;
    }

    .header-meta {
      display: block;
    }

    .header-left  { float: left;  width: 65%; }
    .header-right { float: right; width: 33%; text-align: right; }

    .report-title {
      font-size: 11px;
      font-weight: 600;
      color: #475569;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .print-date {
      font-size: 9px;
      color: #64748b;
      margin-top: 5px;
    }

    .badge {
      display: inline-block;
      padding: 4px 10px;
      border: 1px solid {{ $themeColor }};
      border-radius: 4px;
      font-size: 9px;
      font-weight: 700;
      color: {{ $themeColor }};
      letter-spacing: 0.05em;
      text-transform: uppercase;
      background-color: #ffffff;
    }

    .page-num {
      font-size: 9px;
      color: #94a3b8;
      margin-top: 6px;
    }

    /* ── PATIENT INFO SECTION ── */
    .info-section {
      margin-bottom: 25px;
    }

    .score-box {
      float: left;
      width: 20%;
      text-align: center;
      padding-top: 5px;
    }

    .score-value {
      font-size: 42px;
      font-weight: 800;
      line-height: 1;
    }

    .score-label {
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #64748b;
      margin-top: 8px;
      font-weight: 700;
    }

    .info-table-wrap {
      float: left;
      width: 80%;
      padding-left: 10px;
    }

    .info-table {
      width: 100%;
      border-collapse: collapse;
    }

    .info-table td {
      padding: 7px 0;
      border-bottom: 1px solid #f1f5f9;
      font-size: 10px;
      vertical-align: top;
    }

    .info-table td.label {
      color: #64748b;
      width: 25%;
    }

    .info-table td.value {
      color: #0f172a;
      font-weight: 600;
      width: 25%;
    }

    /* ── SUMMARY ── */
    .summary-box {
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 14px 18px;
      font-size: 11px;
      color: #334155;
      margin-bottom: 25px;
      line-height: 1.6;
    }

    /* ── SECTION TITLES ── */
    .section { margin-bottom: 25px; }

    .section-title {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #0f172a;
      padding-bottom: 6px;
      border-bottom: 1px solid #cbd5e1;
      margin-bottom: 15px;
    }

    /* ── DATA TABLES ── */
    .data-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 10px;
      border: 1px solid #cbd5e1;
    }

    .data-table th {
      background-color: #f1f5f9;
      padding: 9px 12px;
      text-align: left;
      font-weight: 700;
      color: #334155;
      border: 1px solid #cbd5e1;
      text-transform: uppercase;
      font-size: 9px;
      letter-spacing: 0.05em;
    }

    .data-table td {
      padding: 9px 12px;
      text-align: left;
      border: 1px solid #e2e8f0;
      color: #1e293b;
      vertical-align: middle;
    }

    .data-table tr:nth-child(even) td {
      background-color: #f8fafc;
    }

    /* Status Text (Tanpa Kotakan) */
    .status-text {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    .status-ok { color: #059669; }
    .status-danger { color: #e11d48; }
    .status-warning { color: #d97706; }

    /* ── REC CARDS ── */
    .rec-item {
      padding: 12px 14px;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      margin-bottom: 10px;
      background-color: #ffffff;
    }

    .rec-title {
      font-weight: 700;
      font-size: 11px;
      color: #0f172a;
      margin-bottom: 6px;
    }

    .sys-tag {
      display: inline-block;
      font-size: 8px;
      color: #475569;
      background-color: #f1f5f9;
      padding: 3px 8px;
      border-radius: 4px;
      margin-bottom: 8px;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }

    .parent-note {
      background-color: #f8fafc;
      border-left: 2px solid #cbd5e1;
      padding: 6px 10px;
      font-size: 10px;
      color: #475569;
      margin-top: 8px;
    }

    /* ── PHYSIO SECTION ── */
    .physio-info {
      font-size: 11px;
      color: #334155;
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      padding: 12px 16px;
      border-radius: 6px;
    }

    .physio-info strong {
      font-size: 12px;
      color: #0f172a;
    }

    .status-pill {
      display: inline-block;
      margin-top: 8px;
      padding: 4px 10px;
      font-size: 9px;
      font-weight: 700;
      background-color: #f1f5f9;
      color: #475569;
      border-radius: 4px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    /* ── PHOTO LAYOUT ── */
    .photo-row { margin-bottom: 20px; }

    .photo-cell-3 { float: left; width: 31%; margin-right: 3%; page-break-inside: avoid; }
    .photo-cell-3:last-child { margin-right: 0; }
    .photo-cell-2 { float: left; width: 48%; margin-right: 4%; page-break-inside: avoid; }
    .photo-cell-2:last-child { margin-right: 0; }
    .photo-cell-1 { float: left; width: 100%; page-break-inside: avoid; }

    .photo-img-wrap, .crop-img-wrap {
      width: 100%;
      background-color: #f8fafc;
      border: 1px solid #cbd5e1;
      text-align: center;
      padding: 5px;
      border-radius: 6px;
    }

    .photo-img-wrap { height: 260px; }
    .crop-img-wrap { height: 180px; }

    .photo-img-wrap img, .crop-img-wrap img {
      max-width: 100%;
      max-height: 100%;
      display: block;
      margin: 0 auto;
      object-fit: contain;
    }

    .photo-meta {
      padding-top: 8px;
      text-align: center;
    }

    .photo-label {
      font-size: 10px;
      font-weight: 700;
      color: #0f172a;
      text-transform: uppercase;
    }

    .photo-sublabel {
      font-size: 9px;
      color: #64748b;
      margin-top: 2px;
    }

    .info-note {
      font-size: 10px;
      color: #475569;
      margin-bottom: 15px;
      line-height: 1.5;
      background-color: #f8fafc;
      padding: 10px 14px;
      border-radius: 6px;
      border: 1px solid #e2e8f0;
    }

    .empty-state {
      font-size: 10px;
      color: #94a3b8;
      text-align: center;
      padding: 25px 0;
      font-style: italic;
      border: 1px dashed #cbd5e1;
      border-radius: 6px;
    }

    /* ── FOOTER ── */
    .footer {
      position: fixed;
      bottom: 20px;
      left: 40px;
      right: 40px;
      border-top: 1px solid #e2e8f0;
      padding-top: 10px;
      font-size: 8px;
      color: #94a3b8;
    }

    .footer-left  { float: left; font-weight: 600; }
    .footer-right { float: right; letter-spacing: 0.05em; }
  </style>
</head>
<body>

{{-- Menghitung Warna Skor Postur Berdasarkan Nilai --}}
@php
  $scoreVal = (float)$score;
  if ($scoreVal < 50) {
      $scoreColor = '#e11d48'; // Merah
  } elseif ($scoreVal < 80) {
      $scoreColor = '#d97706'; // Oranye
  } else {
      $scoreColor = '#059669'; // Hijau
  }
@endphp

{{-- ═══════════════════ HALAMAN 1 ═══════════════════ --}}
<div class="page">

  <div class="header">
    <div class="brand-title">POSTURELY CLINICAL REPORT</div>
    <div class="header-meta clearfix">
      <div class="header-left">
        <div class="report-title">Hasil Pemeriksaan Postur Anak</div>
        <div class="print-date">Dicetak pada {{ \Carbon\Carbon::parse($printDate)->timezone('Asia/Jakarta')->format('d F Y') }} · {{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->format('H:i') }} WIB</div>
      </div>
      <div class="header-right">
        <span class="badge">{{ $cat['label'] }}</span>
        <div class="page-num">Hal. 1 / 2</div>
      </div>
    </div>
  </div>

  {{-- Patient Info --}}
  <div class="info-section clearfix">
    <div class="score-box">
      {{-- Warna text skor diatur dinamis berdasarkan nilai ($scoreColor) --}}
      <div class="score-value" style="color: {{ $scoreColor }};">
        {{ $score !== null ? number_format((float)$score, 0) : '-' }}
      </div>
      <div class="score-label">Skor Postur</div>
    </div>

    <div class="info-table-wrap">
      <table class="info-table">
        <tr>
          <td class="label">Nama Pasien</td>
          <td class="value">{{ $child->name ?? '-' }}</td>
          <td class="label">Tanggal Pemeriksaan</td>
          <td class="value">{{ \Carbon\Carbon::parse($screenDate)->timezone('Asia/Jakarta')->format('d/m/Y') }}</td>
        </tr>
        <tr>
          <td class="label">Usia</td>
          <td class="value">{{ $ageYears ? $ageYears . ' Tahun' : '-' }}</td>
          <td class="label">Tinggi Badan</td>
          <td class="value">{{ $child->height ?? '-' }} cm</td>
        </tr>
        <tr>
          <td class="label">Jenis Kelamin</td>
          <td class="value">
            @if($child->gender === 'M') Laki-laki
            @elseif($child->gender === 'F') Perempuan
            @else - @endif
          </td>
          <td class="label">Berat Badan</td>
          <td class="value">{{ $child->weight ?? '-' }} kg</td>
        </tr>
      </table>
    </div>
  </div>

  {{-- Summary --}}
  @if($summary)
  <div class="summary-box">
    <strong style="font-size: 12px; margin-bottom: 6px; display: inline-block;">Analisis Postur AI:</strong><br>
    {{ $summary }}
  </div>
  @endif

  {{-- Detail Pengukuran --}}
  @php
    $hasMetricRows = isset($metrics['shoulder_tilt_index'])
      || isset($metrics['hip_tilt_index'])
      || isset($metrics['forward_head_index'])
      || isset($metrics['neck_inclination_deg'])
      || isset($metrics['torso_inclination_deg']);
  @endphp

  @if($hasMetricRows || !empty($findings))
  <div class="section">
    <div class="section-title">Data Pengukuran & Observasi</div>

    @if($hasMetricRows)
    <table class="data-table" style="margin-bottom: 20px;">
      <colgroup>
        <col style="width:40%"/>
        <col style="width:25%"/>
        <col style="width:35%"/>
      </colgroup>
      <thead>
        <tr><th>Parameter</th><th>Nilai Terukur</th><th>Interpretasi</th></tr>
      </thead>
      <tbody>
        @isset($metrics['shoulder_tilt_index'])
        <tr>
          <td>Kemiringan Bahu</td>
          <td><strong>{{ number_format((float)$metrics['shoulder_tilt_index'], 2) }}%</strong></td>
          <td>
            <span class="status-text {{ $metrics['shoulder_tilt_index'] < 2 ? 'status-ok' : 'status-danger' }}">
              {{ $metrics['shoulder_tilt_index'] < 2 ? 'Normal' : 'Terdapat Deviasi' }}
            </span>
          </td>
        </tr>
        @endisset
        @isset($metrics['hip_tilt_index'])
        <tr>
          <td>Kemiringan Panggul</td>
          <td><strong>{{ number_format((float)$metrics['hip_tilt_index'], 2) }}%</strong></td>
          <td>
            <span class="status-text {{ $metrics['hip_tilt_index'] < 2 ? 'status-ok' : 'status-danger' }}">
              {{ $metrics['hip_tilt_index'] < 2 ? 'Normal' : 'Terdapat Deviasi' }}
            </span>
          </td>
        </tr>
        @endisset
        @isset($metrics['forward_head_index'])
        <tr>
          <td>Postur Kepala Maju (FHP)</td>
          <td><strong>{{ number_format((float)$metrics['forward_head_index'], 2) }}</strong></td>
          <td>
            <span class="status-text {{ $metrics['forward_head_index'] < 0.2 ? 'status-ok' : 'status-danger' }}">
              {{ $metrics['forward_head_index'] < 0.2 ? 'Normal' : 'Terdeteksi' }}
            </span>
          </td>
        </tr>
        @endisset
        @isset($metrics['neck_inclination_deg'])
        <tr>
          <td>Sudut Inklinasi Leher</td>
          <td><strong>{{ number_format((float)$metrics['neck_inclination_deg'], 1) }}°</strong></td>
          <td>
            <span class="status-text {{ $metrics['neck_inclination_deg'] < 15 ? 'status-ok' : 'status-danger' }}">
              {{ $metrics['neck_inclination_deg'] < 15 ? 'Normal' : 'Terdapat Deviasi' }}
            </span>
          </td>
        </tr>
        @endisset
        @isset($metrics['torso_inclination_deg'])
        <tr>
          <td>Sudut Inklinasi Punggung</td>
          <td><strong>{{ number_format((float)$metrics['torso_inclination_deg'], 1) }}°</strong></td>
          <td>
            <span class="status-text {{ $metrics['torso_inclination_deg'] < 15 ? 'status-ok' : 'status-danger' }}">
              {{ $metrics['torso_inclination_deg'] < 15 ? 'Normal' : 'Terdapat Deviasi' }}
            </span>
          </td>
        </tr>
        @endisset
      </tbody>
    </table>
    @endif

    @if(!empty($findings))
    <table class="data-table">
      <colgroup>
        <col style="width:25%"/>
        <col style="width:50%"/>
        <col style="width:25%"/>
      </colgroup>
      <thead>
        <tr><th>Area Observasi</th><th>Detail Temuan</th><th>Tingkat Deviasi</th></tr>
      </thead>
      <tbody>
        @foreach($findings as $f)
        <tr>
          <td><strong>{{ $f['area'] ?? '-' }}</strong></td>
          <td>{{ $f['detail'] ?? '-' }}</td>
          <td>
            <span class="status-text {{ ($f['severity'] ?? '') === 'Ringan' ? 'status-ok' : 'status-warning' }}">
              {{ $f['severity'] ?? '-' }}
            </span>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>
  @endif

  {{-- Rekomendasi Terkomputasi --}}
  @if(!empty($aiRecs))
  <div class="section">
    <div class="section-title">Program Latihan (Saran Terkomputasi)</div>
    @foreach($aiRecs as $rec)
    <div class="rec-item">
      <div class="sys-tag">Kategori: {{ $rec['view'] ?? 'General' }}</div>
      <div class="rec-title">{{ $rec['issue'] ?? '-' }}</div>
      <div style="margin-bottom: 4px; color: #334155;"><strong>Intervensi Latihan:</strong> {{ $rec['exercise'] ?? '-' }}</div>
      <div style="color: #334155;"><strong>Durasi Disarankan:</strong> {{ $rec['duration'] ?? '-' }}</div>
      @if(!empty($rec['parent_note']))
      <div class="parent-note"><strong>Catatan Pendampingan:</strong> {{ $rec['parent_note'] }}</div>
      @endif
    </div>
    @endforeach
  </div>
  @endif

  {{-- Status Fisioterapis --}}
  @if($physiotherapist)
  <div class="section">
    <div class="section-title">Informasi Fisioterapis Pendamping</div>
    <div class="physio-info">
      <strong>{{ $physiotherapist->name }}</strong>
      @if($physiotherapist->clinic_name)
      <span style="color:#64748b"> · {{ $physiotherapist->clinic_name }}</span>
      @endif
      @if($physiotherapist->city)
      <span style="color:#64748b"> · {{ $physiotherapist->city }}</span>
      @endif
      <br/>
      <span class="status-pill">{{ $statusMap[$referral_status] ?? 'Dalam Peninjauan' }}</span>
    </div>
  </div>
  @endif

  {{-- Catatan Fisioterapis --}}
  <div class="section">
    <div class="section-title">Catatan Klinis Lanjutan</div>
    @if($manualRecommendations && count($manualRecommendations) > 0)
      @foreach($manualRecommendations as $rec)
      <div class="rec-item">
        <div class="rec-title">
          {{ $rec->title ?? '-' }}
          <span style="color:#64748b;font-weight:400;font-size:9px;text-transform:uppercase;margin-left:6px;background:#f1f5f9;padding:2px 6px;border-radius:3px;">{{ $rec->type ?? '' }}</span>
        </div>
        <p style="color:#334155;margin-bottom:8px;line-height:1.6;">{{ $rec->content ?? '-' }}</p>
        <div style="font-size: 9px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 6px;">
          Oleh: <strong>{{ $rec->physio->name ?? 'Tim Klinis' }}</strong> &nbsp;·&nbsp;
          {{ $rec->created_at ? \Carbon\Carbon::parse($rec->created_at)->translatedFormat('d F Y') : '-' }}
        </div>
      </div>
      @endforeach
    @else
      <div class="empty-state">Belum ada catatan klinis tambahan untuk sesi ini.</div>
    @endif
  </div>

  <div class="footer clearfix">
    <span class="footer-left">Posturely Report &nbsp;·&nbsp; Hak Cipta © {{ date('Y') }}</span>
    <span class="footer-right">DOKUMEN RAHASIA — HANYA UNTUK REKAM MEDIS & KONSULTASI</span>
  </div>

</div>

{{-- ═══════════════════ HALAMAN 2 — Lampiran Foto ═══════════════════ --}}
<div class="page page-break">

  <div class="header">
    <div class="brand-title">POSTURELY CLINICAL REPORT</div>
    <div class="header-meta clearfix">
      <div class="header-left">
        <div class="report-title">Lampiran Observasi Visual</div>
        <div class="print-date">Pasien: {{ $child->name ?? '-' }} &nbsp;·&nbsp; {{ \Carbon\Carbon::parse($screenDate)->timezone('Asia/Jakarta')->format('d/m/Y') }}</div>
      </div>
      <div class="header-right">
        <span class="badge">{{ $cat['label'] }}</span>
        <div class="page-num">Hal. 2 / 2</div>
      </div>
    </div>
  </div>

  @if(count($mainImages) > 0)
  <div class="section">
    <div class="section-title">Dokumentasi Postur Analitik</div>
    @php $chunks = $mainImages->chunk(3); @endphp
    @foreach($chunks as $chunk)
    @php $cnt = count($chunk); @endphp
    <div class="photo-row clearfix">
      @foreach($chunk as $img)
      <div class="{{ $cnt === 1 ? 'photo-cell-1' : ($cnt === 2 ? 'photo-cell-2' : 'photo-cell-3') }}">
        <div class="photo-img-wrap">
          @if(!empty($img['image_base64']))
            <img src="{{ $img['image_base64'] }}" alt="{{ $img['type_label'] }}"/>
          @else
            <span style="font-size:10px;color:#cbd5e1;display:block;padding:40px;text-align:center;">Citra tidak tersedia</span>
          @endif
        </div>
        <div class="photo-meta">
          <div class="photo-label">{{ $img['type_label'] }}</div>
          <div class="photo-sublabel">{{ $img['is_processed'] ? 'Citra Analitik (Terproses)' : 'Citra Observasi (Original)' }}</div>
        </div>
      </div>
      @endforeach
    </div>
    @endforeach
  </div>
  @else
    <div class="empty-state">Dokumentasi visual tidak disertakan dalam laporan ini.</div>
  @endif

  @if(count($cropImages) > 0)
  <div class="section" style="margin-top:25px">
    <div class="section-title">Fokus Area Deviasi</div>
    <div class="info-note">
      Gambar berikut memetakan segmen tubuh spesifik yang menunjukkan indikasi deviasi berdasarkan pemindaian sistem.
    </div>
    @php $cropChunks = $cropImages->chunk(3); @endphp
    @foreach($cropChunks as $chunk)
    @php $cnt = count($chunk); @endphp
    <div class="photo-row clearfix">
      @foreach($chunk as $crop)
      <div class="{{ $cnt === 1 ? 'photo-cell-1' : ($cnt === 2 ? 'photo-cell-2' : 'photo-cell-3') }}">
        <div class="crop-img-wrap">
          @if(!empty($crop['image_base64']))
            <img src="{{ $crop['image_base64'] }}" alt="{{ $crop['type_label'] }}"/>
          @else
            <span style="font-size:10px;color:#cbd5e1;display:block;padding:40px;text-align:center;">Citra tidak tersedia</span>
          @endif
        </div>
        <div class="photo-meta">
          <div class="photo-label">{{ $crop['type_label'] }}</div>
        </div>
      </div>
      @endforeach
    </div>
    @endforeach
  </div>
  @endif

  @if(count($mainImages) === 0 && count($cropImages) === 0)
  <div class="empty-state">Tidak ada lampiran gambar yang tersedia untuk dicetak.</div>
  @endif

  <div class="footer clearfix">
    <span class="footer-left">Posturely Report &nbsp;·&nbsp; Hak Cipta © {{ date('Y') }}</span>
    <span class="footer-right">DOKUMEN RAHASIA — HANYA UNTUK REKAM MEDIS & KONSULTASI</span>
  </div>

</div>

</body>
</html>
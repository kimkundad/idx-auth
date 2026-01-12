<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Label</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    @page { size: {{ $w }}mm {{ $h }}mm; margin: 0; }
    html, body { width: {{ $w }}mm; height: {{ $h }}mm; margin:0; padding:0; }

    body{
      font-family: 'IBM Plex Sans Thai', sans-serif;
      color:#111;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .sheet{
      width: {{ $w }}mm;
      height: {{ $h }}mm;
      display: grid;
      place-items: center;
      padding: 0;
      box-sizing: border-box;
    }

    .content{
      width: 100%;
      max-width: calc({{ $w }}mm - 12mm);
      text-align: center;
      padding: 0 6mm;
      box-sizing: border-box;
    }

    .name-line{
    font-weight: 700;
    font-size: 25px;
    line-height: 1.12;
    margin: 0;
    word-break: break-word;
    margin-top: 8px;
  }

    .phone{
      font-weight: 600;
      font-size: 13px;
      line-height: 1.2;
      margin-top: 1.2mm;
      margin-bottom: 3mm;
    }

    .divider{
      height: 0;
      border: 0;
      border-top: 1px solid #111;
      width: 48mm;
      margin: 0 auto 3mm auto;
    }

    .org{
      font-weight: 700;
      font-size: 14px;
      line-height: 1.18;
      margin: 0 0 3mm 0;
      word-break: break-word;
    }

    /* ===== Activity / Present (ตามรูป) ===== */
    .section-title{
      font-weight: 700;
      font-size: 12px;
      margin: 0;
      line-height: 1.2;
    }

    .section-items{
      font-weight: 600;
      font-size: 14px;
      margin: 0.8mm 0 2.2mm 0;
      line-height: 1.25;
      word-break: break-word;
      white-space: normal;
    }

    /* กันยาวเกิน (รวมทั้ง 2 บล็อก) */
    .clamp{
      display: -webkit-box;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .clamp-activity{ -webkit-line-clamp: 2; }
    .clamp-present{ -webkit-line-clamp: 2; }

    .no-print{ padding:10px; }
    @media print { .no-print{ display:none !important; } }
  </style>
</head>

<body>
  <div class="no-print">
    <button onclick="window.print()">พิมพ์</button>
    <button onclick="window.close()">ปิด</button>
  </div>

  @php
    // ---- Activity (เลือกเฉพาะที่เป็น true) ----
    $activity = [];
    if (!empty($attendee->activity_workshop))   $activity[] = 'Workshop';
    if (!empty($attendee->activity_conference)) $activity[] = 'Conference';
    if (!empty($attendee->activity_excursion))  $activity[] = 'Excursion';
    $activityText = $activity ? implode(' / ', $activity) : '-';

    // ---- Present (เลือกเฉพาะที่เป็น true) ----
    $present = [];
    if (!empty($attendee->presentation_conference)) $present[] = 'Conference';
    if (!empty($attendee->presentation_oral))       $present[] = 'Oral';
    if (!empty($attendee->presentation_poster))     $present[] = 'Poster';
    $presentText = $present ? implode(' / ', $present) : '-';
  @endphp

  <div class="sheet">
    <div class="content">
      <div class="name-line">{{ $attendee->first_name_th ?? '' }}</div>
<div class="name-line">{{ $attendee->last_name_th ?? '' }}</div>

      <div class="phone">
        โทร. {{ $attendee->phone ?? '-' }}
      </div>

      <hr class="divider">

      <div class="org">
        {{ $attendee->organization ?? '-' }}
      </div>

      <p class="section-title">Activity</p>
      <p class="section-items clamp clamp-activity">{{ $activityText }}</p>

      <p class="section-title">Present</p>
      <p class="section-items clamp clamp-present">{{ $presentText }}</p>

    </div>
  </div>

  <script>
    window.addEventListener('load', () => {
      const doPrint = () => {
        setTimeout(() => window.print(), 150);
        window.addEventListener('afterprint', () => setTimeout(() => window.close(), 250));
      };
      if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(doPrint).catch(doPrint);
      } else {
        doPrint();
      }
    });
  </script>
</body>
</html>

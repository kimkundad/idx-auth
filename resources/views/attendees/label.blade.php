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

    /* ใช้ grid ทำให้ “กล่องเนื้อหา” อยู่กลางบน-ล่าง + ซ้าย-ขวา */
    .sheet{
      width: {{ $w }}mm;
      height: {{ $h }}mm;
      display: grid;
      place-items: center;            /* ⭐ กลางทั้งแนวนอน/แนวตั้ง */
      padding: 0;
      box-sizing: border-box;
    }

    /* กล่องเนื้อหา: บังคับให้เป็น “คอลัมน์เดียว” ไม่แตกซ้ายขวา */
    .content{
      width: 100%;
      max-width: calc({{ $w }}mm - 12mm); /* เว้นขอบซ้ายขวา ~6mm */
      text-align: center;
      padding: 0 6mm;
      box-sizing: border-box;
    }

    .name{
      font-weight: 700;
      font-size: 18px;
      line-height: 1.12;
      margin: 0;
      word-break: break-word;
    }

    .phone{
      font-weight: 600;
      font-size: 13px;
      line-height: 1.2;
      margin-top: 1.2mm;
      margin-bottom: 3mm;
    }

    /* เส้นบางและสั้นแบบตัวอย่าง */
    .divider{
      height: 0;
      border: 0;
      border-top: 1px solid #111;
      width: 48mm;          /* ⭐ คุมความยาวเส้นให้เหมือนตัวอย่าง */
      margin: 0 auto 3mm auto;
    }

    .org{
      font-weight: 700;
      font-size: 14px;
      line-height: 1.18;
      margin: 0 0 3mm 0;
      word-break: break-word;
    }

    .act{
      font-weight: 500;
      font-size: 11px;
      line-height: 1.25;
      margin: 0;
      white-space: pre-wrap;
      word-break: break-word;

      /* กันยาวเกิน: ตัด 3 บรรทัด */
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 3;
      overflow: hidden;
    }

    .no-print{ padding:10px; }
    @media print { .no-print{ display:none !important; } }
  </style>
</head>

<body>
  <div class="no-print">
    <button onclick="window.print()">พิมพ์</button>
    <button onclick="window.close()">ปิด</button>
  </div>

  <div class="sheet">
    <div class="content">
      <div class="name">
        {{ trim(($attendee->first_name_th ?? '').' '.($attendee->last_name_th ?? '')) }}
      </div>

      <div class="phone">
        โทร. {{ $attendee->phone ?? '-' }}
      </div>

      <hr class="divider">

      <div class="org">
        {{ $attendee->organization ?? '-' }}
      </div>

      <div class="act">
        {{ $attendee->activity ?? '-' }}
      </div>
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

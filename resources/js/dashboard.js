import { Modal } from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
  const qrInput = document.getElementById('qrInput');
  const qrClearBtn = document.getElementById('qrClearBtn');

  const qrModalEl = document.getElementById('qrModal');
  if (!qrModalEl) return;

  const qrModal = new Modal(qrModalEl);

  // blocks
  const attendeeBlock = document.getElementById('attendeeBlock');
  const emptyState = document.getElementById('emptyState');

  // modal fields
  const alertBox = document.getElementById('qrModalAlert');
  const mId = document.getElementById('mAttendeeId');
  const mNameTh = document.getElementById('mNameTh');
  const mEmail = document.getElementById('mEmail');
  const mPhone = document.getElementById('mPhone');
  const mOrg = document.getElementById('mOrg');
  const mProvince = document.getElementById('mProvince');
const mTravel = document.getElementById('mTravel');
const editBtn = document.getElementById('editBtn');
//   const mRegDate = document.getElementById('mRegDate');
//   const mQr = document.getElementById('mQr');
  const mStatusBadge = document.getElementById('mStatusBadge');

  const checkinBtn = document.getElementById('checkinBtn');
  const successBlock = document.getElementById('successBlock');
  const successTime = document.getElementById('successTime');
  const printBtn = document.getElementById('printBtn');

  const mActivity = document.getElementById('mActivity');
const mPresentation = document.getElementById('mPresentation');

const mRegisterDate1 = document.getElementById('mRegisterDate1');
const mRegisterDate2 = document.getElementById('mRegisterDate2');

  let isSearching = false;
  let isCheckingIn = false;

  // ---------- UI helpers ----------
  function showAlert(type, message) {
    alertBox.className = `alert alert-${type} mb-3`;
    alertBox.textContent = message;
    alertBox.classList.remove('d-none');
  }

  function hideAlert() {
    alertBox.classList.add('d-none');
    alertBox.textContent = '';
  }

  function showFoundUI() {
    attendeeBlock?.classList.remove('d-none');
    emptyState?.classList.add('d-none');
  }

  function showNotFoundUI() {
    attendeeBlock?.classList.add('d-none');
    emptyState?.classList.remove('d-none');
  }

  // ปุ่ม check-in จากตาราง
  document.querySelectorAll('.js-open-checkin').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.attendeeId;
      if (!id) return;

      // เปิด modal
      qrModal.show();

      // เรียก lookup ด้วย id (ไม่ต้องใช้ QR)
      await lookupById(id);
    });
  });



  function clearModalData() {
    mId.value = '';
    mNameTh.textContent = '-';
    mEmail.textContent = '-';
    mPhone.textContent = '-';
    mOrg.textContent = '-';
    mProvince.textContent = '-';
    mTravel.textContent = '-';
    if (editBtn) editBtn.href = '#';
    // mRegDate.textContent = '-';
    // mQr.textContent = '-';

    mActivity.textContent = '-';
  mPresentation.textContent = '-';

    mStatusBadge.className = 'badge rounded-pill text-bg-secondary';
    mStatusBadge.textContent = '-';

    checkinBtn.disabled = false;
    checkinBtn.textContent = 'เช็คอิน';

    successBlock.classList.add('d-none');
    successTime.textContent = '';
  }

  function setStatus(status) {
    if (status === 'checked_in') {
      mStatusBadge.className = 'badge rounded-pill text-bg-success';
      mStatusBadge.textContent = 'เช็คอินแล้ว';
      checkinBtn.disabled = false;
      checkinBtn.textContent = 'เช็คอินแล้ว';
      successBlock.classList.remove('d-none');
    } else {
      mStatusBadge.className = 'badge rounded-pill text-bg-warning';
      mStatusBadge.textContent = 'รอเช็คอิน';
      checkinBtn.disabled = false;
      checkinBtn.textContent = 'เช็คอิน';
      successBlock.classList.add('d-none');
    }
  }

  // ---------- Beep + shake ----------
  function beep() {
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      const ctx = new AudioCtx();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();

      osc.type = 'sine';
      osc.frequency.value = 880;
      gain.gain.value = 0.06;

      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();

      setTimeout(() => {
        osc.stop();
        ctx.close();
      }, 120);
    } catch (e) {
      // ignore if browser blocks audio
    }
  }

  function shakeInput() {
    if (!qrInput) return;
    qrInput.classList.add('is-invalid');
    qrInput.style.animation = 'qrshake 250ms ease-in-out 0s 1';
    setTimeout(() => {
      qrInput.style.animation = '';
      qrInput.classList.remove('is-invalid');
    }, 300);
  }

  // inject keyframes once
  (function ensureShakeStyle() {
    if (document.getElementById('qrshake-style')) return;
    const style = document.createElement('style');
    style.id = 'qrshake-style';
    style.textContent = `
      @keyframes qrshake {
        0% { transform: translateX(0); }
        25% { transform: translateX(-6px); }
        50% { transform: translateX(6px); }
        75% { transform: translateX(-4px); }
        100% { transform: translateX(0); }
      }
    `;
    document.head.appendChild(style);
  })();

  function cleanQr(raw) {
  if (!raw) return '';

  let s = String(raw);

  // 1) บางปืนส่งเป็นตัวอักษร "\000026" (literal)
  s = s.replace(/\\0{1,}(\d{1,3})/g, '');

  // 2) บางปืนส่งเป็น control char จริง (ASCII 0-31, 127)
  s = s.replace(/[\x00-\x1F\x7F]/g, '');

  // 3) ตัดช่องว่าง
  s = s.trim();

  // 4) ถ้าหลงเหลือ prefix อื่น ๆ ให้ดึงตั้งแต่ "QR-" เป็นต้นไป (กันเหนียว)
  const idx = s.indexOf('QR-');
  if (idx > 0) s = s.slice(idx);

  return s;
}


async function lookupById(id) {
  if (!id) return;

  hideAlert();
  clearModalData();
  showNotFoundUI();

  try {
    const res = await fetch(`/attendees/${id}/lookup`, {
      headers: { Accept: 'application/json' }
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok || !data.ok) {
      showAlert('danger', data.message || 'ไม่พบข้อมูล');
      showNotFoundUI();
      return;
    }

    const a = data.data;

    // fill modal (เหมือน lookupQr)
    mId.value = a.id ?? '';
    mNameTh.textContent = a.full_name_th ?? '-';
    mEmail.textContent = a.email ?? '-';
    mPhone.textContent = a.phone ?? '-';
    mOrg.textContent = a.organization ?? '-';

    mProvince.textContent = a.province ?? '-';

    const travelRaw = (a.travel_from_province ?? '').trim();
    mTravel.textContent = travelRaw
      ? travelRaw.split(/\r\n|\n|\r/).join(' / ')
      : '-';

    mActivity.textContent = a.activity_th ?? '-';
    mPresentation.textContent = a.presentation_th ?? '-';

    if (editBtn) editBtn.href = a.edit_url ?? '#';

    showFoundUI();
    setStatus(a.status);

  } catch (e) {
    showAlert('danger', 'เกิดข้อผิดพลาด');
  }
}



  // ---------- Main actions ----------
  async function lookupQr(qr) {
    if (isSearching) return;
    const clean = (qr || '').trim();
    if (!clean) return;

    isSearching = true;
    hideAlert();
    clearModalData();
    showNotFoundUI();

    // เปิด modal เพื่อให้ผู้ใช้เห็นผลทันที
    qrModal.show();

    try {
      const res = await fetch(`/attendees/lookup?qr=${encodeURIComponent(clean)}`, {
        headers: { Accept: 'application/json' }
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok || !data.ok) {
        showAlert('danger', data.message || 'ไม่พบข้อมูลจาก QR Code นี้');
        showNotFoundUI();
        checkinBtn.disabled = false;

        // feedback
        beep();
        shakeInput();
        qrInput?.focus();
        qrInput?.select();
        return;
      }

      const a = data.data;

      // fill
      mId.value = a.id ?? '';
      mNameTh.textContent = a.full_name_th ?? '-';
      mEmail.textContent = a.email ?? '-';
      mPhone.textContent = a.phone ?? '-';
      mOrg.textContent = a.organization ?? '-';
      mRegisterDate1.textContent = a.register_date1 ?? '-';
      mRegisterDate2.textContent = a.register_date2 ?? '-';
    //   mRegDate.textContent = a.register_date ?? '-';
    //   mQr.textContent = a.qr_code ?? '-';

    mProvince.textContent = a.province ?? '-';

// วิธีการเดินทาง (ถ้ามีหลายบรรทัด ให้แสดงเป็น /)
const travelRaw = (a.travel_from_province ?? '').trim();
mTravel.textContent = travelRaw
  ? travelRaw.split(/\r\n|\n|\r/).map(s => s.trim()).filter(Boolean).join(' / ')
  : '-';

    mActivity.textContent = a.activity_th ?? '-';
mPresentation.textContent = a.presentation_th ?? '-';

if (editBtn) editBtn.href = a.edit_url ?? '#';

      showFoundUI();
      setStatus(a.status);

      if (a.status === 'checked_in') {
        const t = a.register_date2 || a.register_date1; // ให้ความสำคัญกับวันที่ 15 ก่อน
        if (t) {
            successTime.textContent = `เวลาเช็คอิน: ${t}`;
            successBlock.classList.remove('d-none');
        }
        }

      // focus ปุ่มเช็คอิน (ถ้ายังไม่เช็คอิน)
      if (a.status !== 'checked_in') {
        checkinBtn?.focus();
      }
    } finally {
      isSearching = false;
    }
  }

  let isPrinting = false;


printBtn?.addEventListener('click', () => {
  const id = (mId.value || '').trim(); // ✅
  if (!id || isPrinting) return;

  isPrinting = true;

  const win = window.open(`/attendees/${id}/label`, '_blank', 'width=520,height=740');

  if (!win) {
    alert('เบราว์เซอร์บล็อคป๊อปอัป กรุณาอนุญาต pop-up เพื่อพิมพ์');
  }

  setTimeout(() => { isPrinting = false; }, 1000);
});

async function checkin() {
  if (isCheckingIn) return;
  const id = (mId.value || '').trim();
  if (!id) return;

  isCheckingIn = true;
  hideAlert();

  checkinBtn.disabled = false;
  const oldText = checkinBtn.textContent;
  checkinBtn.textContent = 'กำลังเช็คอิน...';

  try {
    const res = await fetch(`/attendees/${id}/checkin`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
      }
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok || !data.ok) {
      showAlert('danger', data.message || 'เช็คอินไม่สำเร็จ');
      checkinBtn.disabled = false;
      checkinBtn.textContent = oldText;
      return;
    }

    // ✅ success UI
    setStatus('checked_in');
    successBlock.classList.remove('d-none');
    const t = data.data?.register_date2 || data.data?.register_date1;
    successTime.textContent = t
    ? `เวลาเช็คอิน: ${t}`
    : 'ได้ลงทะเบียนเรียบร้อยแล้ว';

    // ✅ เคลียร์ช่องสแกนทันที (ตามที่ต้องการ)
    if (qrInput) {
      qrInput.value = '';
      qrInput.focus();
    }

    // ✅ สั่งพิมพ์ label (เปิดแท็บใหม่)
    // กันเปิดซ้ำ (เช่นกดปุ่มรัว ๆ หรือ API ตอบซ้ำ)
    // if (!isPrinting) {
    //   isPrinting = true;

    //   // เปิดหน้า label ที่มี window.print() auto อยู่แล้ว
    //   const printWin = window.open(`/attendees/${id}/label`, '_blank', 'width=520,height=740');

    //   // เผื่อบาง browser block popup ให้แจ้งเตือน
    //   if (!printWin) {
    //     showAlert('warning', 'เบราว์เซอร์บล็อคป๊อปอัป กรุณาอนุญาต pop-up เพื่อพิมพ์สติ๊กเกอร์');
    //   }

    //   // ปลดล็อคหลังสั้น ๆ
    //   setTimeout(() => { isPrinting = false; }, 1200);
    // }

    // ✅ beep success (โทนต่ำ)
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      const ctx = new AudioCtx();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = 'sine';
      osc.frequency.value = 520;
      gain.gain.value = 0.05;
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      setTimeout(() => { osc.stop(); ctx.close(); }, 120);
    } catch (e) {}

  } finally {
    isCheckingIn = false;
  }
}


  // ---------- Events ----------
qrInput?.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();

    const raw = qrInput.value;
    const cleaned = cleanQr(raw);

    // 👉 ล้างช่องทันทีหลังรับค่า
    qrInput.value = '';

    if (!cleaned) {
      beep();
      shakeInput();
      return;
    }

    lookupQr(cleaned);
  }
});

  qrClearBtn?.addEventListener('click', () => {
    qrInput.value = '';
    qrInput.focus();
  });

  checkinBtn?.addEventListener('click', checkin);

  // ปิด modal แล้วเคลียร์ + โฟกัสช่องสแกนทันที
  qrModalEl.addEventListener('hidden.bs.modal', () => {
    hideAlert();
    clearModalData();
    showNotFoundUI();
    qrInput?.focus();
  });
});

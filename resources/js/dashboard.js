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
  const mNameEn = document.getElementById('mNameEn');
  const mEmail = document.getElementById('mEmail');
  const mPhone = document.getElementById('mPhone');
  const mOrg = document.getElementById('mOrg');
  const mRegDate = document.getElementById('mRegDate');
  const mQr = document.getElementById('mQr');
  const mStatusBadge = document.getElementById('mStatusBadge');

  const checkinBtn = document.getElementById('checkinBtn');
  const successBlock = document.getElementById('successBlock');
  const successTime = document.getElementById('successTime');

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

  function clearModalData() {
    mId.value = '';
    mNameTh.textContent = '-';
    mNameEn.textContent = '-';
    mEmail.textContent = '-';
    mPhone.textContent = '-';
    mOrg.textContent = '-';
    mRegDate.textContent = '-';
    mQr.textContent = '-';

    mStatusBadge.className = 'badge rounded-pill text-bg-secondary';
    mStatusBadge.textContent = '-';

    checkinBtn.disabled = true;
    checkinBtn.textContent = 'เช็คอิน';

    successBlock.classList.add('d-none');
    successTime.textContent = '';
  }

  function setStatus(status) {
    if (status === 'checked_in') {
      mStatusBadge.className = 'badge rounded-pill text-bg-success';
      mStatusBadge.textContent = 'เช็คอินแล้ว';
      checkinBtn.disabled = true;
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
        checkinBtn.disabled = true;

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
      mNameEn.textContent = a.full_name_en ?? '-';
      mEmail.textContent = a.email ?? '-';
      mPhone.textContent = a.phone ?? '-';
      mOrg.textContent = a.organization ?? '-';
      mRegDate.textContent = a.register_date ?? '-';
      mQr.textContent = a.qr_code ?? '-';

      showFoundUI();
      setStatus(a.status);

      if (a.status === 'checked_in' && a.checked_in_at) {
        successTime.textContent = `เวลาเช็คอิน: ${a.checked_in_at}`;
        successBlock.classList.remove('d-none');
      }

      // focus ปุ่มเช็คอิน (ถ้ายังไม่เช็คอิน)
      if (a.status !== 'checked_in') {
        checkinBtn?.focus();
      }
    } finally {
      isSearching = false;
    }
  }

  async function checkin() {
    if (isCheckingIn) return;
    const id = (mId.value || '').trim();
    if (!id) return;

    isCheckingIn = true;
    hideAlert();

    checkinBtn.disabled = true;
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

      // success UI
      setStatus('checked_in');
      successBlock.classList.remove('d-none');
      successTime.textContent = data.data?.checked_in_at
        ? `เวลาเช็คอิน: ${data.data.checked_in_at}`
        : 'ได้ลงทะเบียนเรียบร้อยแล้ว';

      // beep success (โทนต่ำ)
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
      lookupQr(qrInput.value);
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

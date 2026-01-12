document.addEventListener('DOMContentLoaded', () => {
  const provinceSelect = document.getElementById('provinceSelect');
  const p1 = document.getElementById('province_type_1');
  const p2 = document.getElementById('province_type_2');

  function syncProvinceTypeByProvince() {
    const pv = (provinceSelect?.value || '').trim();
    if (!p1 || !p2) return;

    if (pv === 'กรุงเทพมหานคร') {
      p1.value = '1';
      p2.value = '0';
    } else if (pv !== '') {
      p1.value = '0';
      p2.value = '1';
    } else {
      // ไม่เลือกจังหวัด -> ไม่บังคับ (ปล่อยเป็น 0,0)
      p1.value = '0';
      p2.value = '0';
    }
  }

  provinceTypeUi?.addEventListener('change', syncProvinceType);
  syncProvinceType();

  // ---------- travel checkbox multi -> hidden (newline) ----------
  const travelHidden = document.getElementById('travel_from_province');
  const travelOtherCb = document.getElementById('travel_other_cb');
  const travelOtherWrap = document.getElementById('travelOtherWrap');
  const travelOtherInput = document.getElementById('travelOtherInput');

  function collectTravelLines() {
    const checked = Array.from(document.querySelectorAll('input[name="travel_methods[]"]:checked'))
      .map(el => el.value);

    // other
    if (travelOtherCb?.checked) {
      if (travelOtherWrap) travelOtherWrap.style.display = '';
      const t = (travelOtherInput?.value || '').trim();
      checked.push(t ? `อื่นๆ: ${t}` : 'อื่นๆ:');
    } else {
      if (travelOtherWrap) travelOtherWrap.style.display = 'none';
    }

    // เก็บเป็นหลายบรรทัดเหมือนใน Excel
    if (travelHidden) travelHidden.value = checked.join("\n");
  }

  // bind events
  document.querySelectorAll('input[name="travel_methods[]"]').forEach(el => {
    el.addEventListener('change', collectTravelLines);
  });

  travelOtherCb?.addEventListener('change', collectTravelLines);
  travelOtherInput?.addEventListener('input', collectTravelLines);

  // init
  collectTravelLines();
});

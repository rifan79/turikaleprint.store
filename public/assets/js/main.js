document.addEventListener("DOMContentLoaded", function () {
  const calcBtn = document.getElementById("hitungHargaBtn");
  const calcResult = document.getElementById("hasilHarga");

  // Count-up animation helper
  function animateCountUp(el, target, duration) {
    let start = 0;
    const step = 16; // ~60fps
    const steps = Math.ceil(duration / step);
    const increment = target / steps;
    let current = 0;
    const timer = setInterval(() => {
      current += increment;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      el.textContent = "Rp " + Math.round(current).toLocaleString("id-ID");
    }, step);
  }

  if (calcBtn && calcResult) {
    calcBtn.addEventListener("click", function () {
      const pInput = document.getElementById("panjang");
      const lInput = document.getElementById("lebar");
      const p = Number(pInput.value); // cm
      const l = Number(lInput.value); // cm

      if (isNaN(p) || isNaN(l) || p <= 0 || l <= 0) {
        // Shake animation on invalid input
        calcResult.classList.remove("calc-shake");
        calcResult.innerHTML =
          '<p style="color:#ef4444;font-weight:600;">⚠️ Masukkan lebar dan tinggi yang valid!</p>';
        calcResult.style.display = "block";
        void calcResult.offsetWidth; // reflow to restart animation
        calcResult.classList.add("calc-shake");
        return;
      }

      let luas = (p / 100) * (l / 100);

      // Logic untuk < 1 m²
      if (luas < 1) {
        const floor = Math.floor(luas);
        const decimal = luas - floor;
        if (decimal > 0) {
          if (decimal < 0.6) luas = floor + 0.6;
          else if (decimal < 0.8) luas = floor + 0.8;
          else luas = floor + 1.0;
        }
      }

      // Logic untuk ≥ 1 m²
      else {
        const lebarMeter = p / 100;
        const tinggiMeter = l / 100;
        let ukuranTerkecil = Math.min(lebarMeter, tinggiMeter);
        let ukuranFinal;
        if (ukuranTerkecil === 1) ukuranFinal = 1;
        else if (ukuranTerkecil <= 1.5) ukuranFinal = 1.5;
        else if (ukuranTerkecil <= 2) ukuranFinal = 2;
        else if (ukuranTerkecil <= 3) ukuranFinal = 3;
        else ukuranFinal = 3;
        let sisiTerbesar = Math.max(lebarMeter, tinggiMeter);
        luas = sisiTerbesar * ukuranFinal;
      }

      const total = Math.ceil((luas * 25000) / 1000) * 1000;

      // Build result HTML with a placeholder span for count-up
      calcResult.classList.remove("calc-pop", "calc-shake");
      calcResult.innerHTML = `
<div style="
    text-align:center;
    background: linear-gradient(135deg, rgba(16,185,129,0.10), rgba(19,109,236,0.08));
    padding:18px 28px;
    border-radius:14px;
    display:inline-block;
    margin-top:10px;
    border: 1.5px solid rgba(16,185,129,0.25);
    box-shadow: 0 4px 24px rgba(16,185,129,0.10);
">
    <p style="font-size:12px;color:#64748b;margin-bottom:4px;letter-spacing:0.05em;text-transform:uppercase;">Estimasi Harga</p>
    <span id="resultPrice" style="
        font-size:32px;
        font-weight:800;
        background: linear-gradient(90deg,#10b981,#136dec);
        -webkit-background-clip:text;
        -webkit-text-fill-color:transparent;
        background-clip:text;
    ">Rp 0</span>
    
</div>`;

      calcResult.style.display = "block";

      // Trigger pop animation
      void calcResult.offsetWidth;
      calcResult.classList.add("calc-pop");

      // Count-up on the price span
      const priceEl = document.getElementById("resultPrice");
      animateCountUp(priceEl, total, 700);
    });
  }
  //salam dari saya, klo masih bingung boleh tanya saya hehehe
});

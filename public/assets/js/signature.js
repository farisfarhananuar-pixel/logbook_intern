// Simple canvas-based digital signature pad (tiada dependency luar)
function initSignaturePad(canvasId, hiddenInputId, clearBtnId) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const hidden = document.getElementById(hiddenInputId);

  function resize() {
    const ratio = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * ratio;
    canvas.height = rect.height * ratio;
    ctx.scale(ratio, ratio);
    ctx.lineWidth = 2.4;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#064e3b';
  }
  resize();
  window.addEventListener('resize', resize);

  let drawing = false, lastX = 0, lastY = 0;

  function pos(e) {
    const rect = canvas.getBoundingClientRect();
    const t = e.touches ? e.touches[0] : e;
    return { x: t.clientX - rect.left, y: t.clientY - rect.top };
  }
  function start(e) {
    drawing = true;
    const p = pos(e);
    lastX = p.x; lastY = p.y;
    e.preventDefault();
  }
  function move(e) {
    if (!drawing) return;
    const p = pos(e);
    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
    lastX = p.x; lastY = p.y;
    if (hidden) hidden.value = canvas.toDataURL('image/png');
    e.preventDefault();
  }
  function end() { drawing = false; }

  canvas.addEventListener('mousedown', start);
  canvas.addEventListener('mousemove', move);
  window.addEventListener('mouseup', end);
  canvas.addEventListener('touchstart', start, { passive: false });
  canvas.addEventListener('touchmove', move, { passive: false });
  canvas.addEventListener('touchend', end);

  const clearBtn = document.getElementById(clearBtnId);
  if (clearBtn) {
    clearBtn.addEventListener('click', function (e) {
      e.preventDefault();
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      if (hidden) hidden.value = '';
    });
  }
}

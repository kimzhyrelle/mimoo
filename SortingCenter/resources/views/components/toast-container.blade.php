{{--
  <x-toast-container />
  Place once per page (or in the layout). Creates a fixed container for toast notifications.

  API (vanilla JS, globally available):
    showToast(message, { type, undoLabel, undoUrl, undoMethod, duration })
      type: 'success' | 'error' | 'info'  (default: 'success')
      undoLabel: text for undo link (default: 'Undo')
      undoUrl: URL to POST when undo is clicked (optional)
      undoMethod: 'POST' or 'GET' (default: 'POST')
      duration: ms before auto-dismiss (default: 10000 for undo, 4000 without)

  The undo button calls the undo endpoint with the CSRF token within the duration window.

  // TODO(antigravity): wire $routes.undo for toast undo calls
--}}
<div id="toastContainer" class="toast-container" role="region" aria-live="polite" aria-label="Notifications"></div>

<script>
(function() {
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

  window.showToast = function(message, opts = {}) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const {
      type      = 'success',
      undoLabel = 'Undo',
      undoUrl   = null,
      undoMethod= 'POST',
      duration  = undoUrl ? 10000 : 4000,
    } = opts;

    const id = 'toast-' + Date.now() + '-' + Math.random().toString(36).slice(2, 6);
    const typeClass = {
      success: 'toast-success',
      error:   'toast-error',
      info:    'toast-info',
    }[type] || 'toast-success';

    const iconSvg = {
      success: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>',
      error:   '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>',
      info:    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>',
    }[type] || '';

    const toast = document.createElement('div');
    toast.className = 'toast-item ' + typeClass;
    toast.id = id;
    toast.innerHTML = `
      <div class="toast-icon">${iconSvg}</div>
      <div class="toast-message">${escToast(message)}</div>
      ${undoUrl ? `
        <div class="toast-undo-wrap">
          <button class="toast-undo-btn" onclick="dismissToast('${id}'); doUndo('${undoUrl}','${undoMethod}')">${escToast(undoLabel)}</button>
          <div class="toast-countdown-bar"><div class="toast-countdown-fill" id="${id}-bar"></div></div>
        </div>
      ` : ''}
      <button class="toast-close" onclick="dismissToast('${id}')" aria-label="Dismiss">✕</button>
    `;

    container.appendChild(toast);
    // Trigger entrance animation
    requestAnimationFrame(() => toast.classList.add('toast-visible'));

    // Countdown bar animation
    if (undoUrl) {
      const bar = toast.querySelector('.toast-countdown-fill');
      if (bar) {
        bar.style.transition = `width ${duration}ms linear`;
        requestAnimationFrame(() => { bar.style.width = '0%'; });
      }
    }

    // Auto-dismiss
    const timeoutId = setTimeout(() => dismissToast(id), duration);
    toast.dataset.timeoutId = timeoutId;
  };

  window.dismissToast = function(id) {
    const toast = document.getElementById(id);
    if (!toast) return;
    clearTimeout(parseInt(toast.dataset.timeoutId));
    toast.classList.remove('toast-visible');
    toast.classList.add('toast-leaving');
    setTimeout(() => toast.remove(), 300);
  };

  window.doUndo = function(url, method) {
    const form = document.createElement('form');
    form.method = method === 'GET' ? 'GET' : 'POST';
    form.action = url;
    if (method !== 'GET') {
      form.innerHTML = '<input type="hidden" name="_token" value="' + CSRF + '">';
    }
    document.body.appendChild(form);
    form.submit();
  };

  // Show a toast from session flash data (set by Laravel)
  @php
    $toastMsg   = session('toast');
    $toastType  = session('toast_type', 'success');
    $toastUndo = session('toast_undo');
    $toastUndoUrl = session('toast_undo_url');
  @endphp
  @if ($toastMsg)
    window.addEventListener('DOMContentLoaded', () => {
      showToast(@json($toastMsg), {
        type: @json($toastType),
        undoLabel: @json($toastUndo),
        undoUrl: @json($toastUndoUrl),
      });
    });
  @endif

  function escToast(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
</script>

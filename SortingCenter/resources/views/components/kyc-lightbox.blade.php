{{--
  <x-kyc-lightbox />
  A click-to-zoom lightbox for document thumbnails.

  API:
    <img class="kyc-thumb" src="..." data-caption="OR/CR Document" onclick="openKycLightbox(this)">
    Place <x-kyc-lightbox /> once per page.
--}}
<div id="kycLightbox" class="kyc-lightbox-backdrop hidden" role="dialog" aria-modal="true" aria-label="Document viewer" onclick="if(event.target===this)closeKycLightbox()">
  <div class="kyc-lightbox-inner" onclick="if(event.target===this)closeKycLightbox()">
    <button onclick="closeKycLightbox()" class="kyc-lightbox-close" aria-label="Close">✕</button>
    <div class="kyc-lightbox-content">
      <img id="kycLightboxImg" src="" alt="" class="kyc-lightbox-img">
      <div id="kycLightboxCaption" class="kyc-lightbox-caption"></div>
    </div>
  </div>
</div>

<script>
  function openKycLightbox(img) {
    const box   = document.getElementById('kycLightbox');
    const large = document.getElementById('kycLightboxImg');
    const cap   = document.getElementById('kycLightboxCaption');
    large.src   = img.src;
    large.alt   = img.alt || img.dataset.caption || 'Document';
    cap.textContent = img.dataset.caption || img.alt || '';
    box.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
  function closeKycLightbox() {
    document.getElementById('kycLightbox').classList.add('hidden');
    document.body.style.overflow = '';
  }
  // Esc to close
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeKycLightbox();
  });
</script>

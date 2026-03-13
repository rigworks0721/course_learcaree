document.addEventListener('DOMContentLoaded', () => {
  setupImagePreviews();
  setupDeleteModal();
});

function setupImagePreviews() {
  const fileInputs = document.querySelectorAll('[data-image-input]');
  fileInputs.forEach((input) => {
    const previewSelector = input.getAttribute('data-preview-target');
    const previewEl = document.querySelector(previewSelector);
    if (!previewEl) return;

    input.addEventListener('change', (event) => {
      const file = event.target.files?.[0];
      if (!file) {
        previewEl.innerHTML = '<span>画像が選択されていません</span>';
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        previewEl.innerHTML = '';
        const img = document.createElement('img');
        img.src = String(e.target?.result || '');
        img.alt = '選択中のプレビュー画像';
        previewEl.appendChild(img);
      };
      reader.readAsDataURL(file);
    });
  });
}

function setupDeleteModal() {
  const modal = document.querySelector('[data-delete-modal]');
  if (!modal) return;

  const deleteUrl = modal.getAttribute('data-delete-url') || './delete.php';
  const openButtons = document.querySelectorAll('[data-open-delete]');
  const cancelButtons = modal.querySelectorAll('[data-close-delete]');
  const confirmButton = modal.querySelector('[data-confirm-delete]');
  let selectedId = null;

  openButtons.forEach((button) => {
    button.addEventListener('click', () => {
      selectedId = button.getAttribute('data-id');
      modal.classList.add('show');
    });
  });

  cancelButtons.forEach((button) => {
    button.addEventListener('click', () => {
      modal.classList.remove('show');
      selectedId = null;
    });
  });

  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.classList.remove('show');
      selectedId = null;
    }
  });

  confirmButton?.addEventListener('click', () => {
    if (selectedId) {
      window.location.href = `${deleteUrl}?id=${encodeURIComponent(selectedId)}`;
      return;
    }

    modal.classList.remove('show');
  });
}

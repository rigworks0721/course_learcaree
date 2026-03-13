(function () {
  'use strict';

  var currentPath = window.location.pathname;
  if (currentPath === '/' || currentPath === '/index.html') {
    return;
  }

  var hasClosed = false;
  var modalElements = null;
  var escHandler = null;

  function closePopup() {
    if (!modalElements) {
      return;
    }

    hasClosed = true;

    if (escHandler) {
      document.removeEventListener('keydown', escHandler);
      escHandler = null;
    }

    modalElements.overlay.classList.remove('lc-popup-overlay--visible');

    window.setTimeout(function () {
      if (modalElements && modalElements.overlay.parentNode) {
        modalElements.overlay.parentNode.removeChild(modalElements.overlay);
      }
      modalElements = null;
    }, 180);
  }

  function createPopup(data) {
    if (!data || !data.image_path) {
      return;
    }

    var overlay = document.createElement('div');
    overlay.className = 'lc-popup-overlay';

    var modal = document.createElement('div');
    modal.className = 'lc-popup-modal';

    var closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'lc-popup-close';
    closeButton.setAttribute('aria-label', '閉じる');
    closeButton.textContent = '×';

    var image = document.createElement('img');
    image.className = 'lc-popup-image';
    image.src = data.image_path;
    image.alt = data.title || 'popup banner';
    image.loading = 'lazy';

    if (data.link_url) {
      var anchor = document.createElement('a');
      anchor.className = 'lc-popup-link';
      anchor.href = data.link_url;
      anchor.appendChild(image);
      modal.appendChild(anchor);
    } else {
      modal.appendChild(image);
    }

    modal.appendChild(closeButton);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    modalElements = {
      overlay: overlay,
      modal: modal,
    };

    requestAnimationFrame(function () {
      if (modalElements) {
        modalElements.overlay.classList.add('lc-popup-overlay--visible');
      }
    });

    closeButton.addEventListener('click', closePopup);
    overlay.addEventListener('click', function (event) {
      if (event.target === overlay) {
        closePopup();
      }
    });

    escHandler = function (event) {
      if (event.key === 'Escape') {
        closePopup();
      }
    };
    document.addEventListener('keydown', escHandler);
  }

  function getDelaySeconds(value) {
    var seconds = Number(value);
    if (!Number.isFinite(seconds) || seconds < 0) {
      return 0;
    }
    return seconds;
  }

  fetch('/popup_api.php?path=' + encodeURIComponent(currentPath), {
    method: 'GET',
    credentials: 'same-origin',
    headers: {
      'Accept': 'application/json'
    }
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error('API request failed');
      }
      return response.json();
    })
    .then(function (json) {
      if (!json || json.success !== true || !json.data || !json.data.image_path) {
        return;
      }

      var delay = getDelaySeconds(json.data.display_delay) * 1000;

      window.setTimeout(function () {
        if (!hasClosed) {
          createPopup(json.data);
        }
      }, delay);
    })
    .catch(function () {
      // APIが取得できない場合は何も表示しない
    });
})();

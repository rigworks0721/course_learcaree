(function () {
  function getRootPath() {
    const segments = window.location.pathname.split('/').filter(Boolean);
    if (!segments.length) return '.';
    return '../'.repeat(Math.max(0, segments.length - 1)).replace(/\/$/, '') || '.';
  }

  function renderFooter() {
    if (document.querySelector('[data-common-site-footer]')) return;

    const rootPath = getRootPath();
    const links = [
      { href: `${rootPath}/policies/leaglnotice/`, label: '特定商取引法に基づく表記' },
      { href: `${rootPath}/policies/refund/`, label: '返金ポリシー' },
      { href: `${rootPath}/policies/shipping/`, label: '配送ポリシー' },
      { href: `${rootPath}/policies/terms/`, label: '利用規約' }
    ];

    const footer = document.createElement('footer');
    footer.setAttribute('data-common-site-footer', '');
    footer.style.background = '#111827';
    footer.style.color = '#e5e7eb';
    footer.style.padding = '24px 16px';
    footer.style.marginTop = '0';

    const wrapper = document.createElement('div');
    wrapper.style.maxWidth = '960px';
    wrapper.style.margin = '0 auto';
    wrapper.style.textAlign = 'center';

    const nav = document.createElement('nav');
    nav.setAttribute('aria-label', 'フッターナビゲーション');
    nav.style.display = 'flex';
    nav.style.flexWrap = 'wrap';
    nav.style.gap = '12px 20px';
    nav.style.justifyContent = 'center';
    nav.style.marginBottom = '12px';

    links.forEach((item) => {
      const a = document.createElement('a');
      a.href = item.href;
      a.textContent = item.label;
      a.style.color = '#e5e7eb';
      a.style.fontSize = '14px';
      a.style.textDecoration = 'none';
      a.addEventListener('mouseenter', function () { a.style.textDecoration = 'underline'; });
      a.addEventListener('mouseleave', function () { a.style.textDecoration = 'none'; });
      nav.appendChild(a);
    });

    const copy = document.createElement('p');
    copy.textContent = '© Learcaree';
    copy.style.margin = '0';
    copy.style.fontSize = '12px';
    copy.style.opacity = '0.8';

    wrapper.appendChild(nav);
    wrapper.appendChild(copy);
    footer.appendChild(wrapper);
    document.body.appendChild(footer);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderFooter);
  } else {
    renderFooter();
  }
})();

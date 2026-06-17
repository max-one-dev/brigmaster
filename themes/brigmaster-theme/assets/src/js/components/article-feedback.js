/**
 * Article feedback widget ("Статья была полезна?") — stores a yes/no vote via
 * admin-ajax, marks the chosen button as pressed and locks the widget. Also wires
 * the hero "Поделиться" button to copy the article link (Web Share API when
 * available, with a clipboard / execCommand fallback that also works over http).
 */
import { registerComponent } from '../core/bootstrap.js';

function readCookie(name) {
  const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
  return match ? decodeURIComponent(match[1]) : '';
}

function writeCookie(name, value, days = 365) {
  const expires = new Date(Date.now() + days * 864e5).toUTCString();
  document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; samesite=lax`;
}

function initFeedback(root) {
  const ajaxUrl = root.dataset.ajaxUrl;
  const postId = root.dataset.postId;
  const nonce = root.dataset.nonce;
  if (!ajaxUrl || !postId) return;

  const cookieName = `bm_voted_${postId}`;
  const buttons = Array.from(root.querySelectorAll('[data-vote]'));
  const text = root.querySelector('.bm-article-feedback__text');

  const paint = (vote) => {
    buttons.forEach((b) => {
      const on = b.dataset.vote === vote;
      b.classList.toggle('is-selected', on);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  };

  // Restore a returning visitor's previous choice (one vote per visitor, cookie-based).
  let current = readCookie(cookieName);
  if (current === 'yes' || current === 'no') {
    paint(current);
    if (text) text.textContent = 'Спасибо за ваш отзыв!';
  }

  buttons.forEach((btn) => {
    btn.addEventListener('click', async () => {
      const vote = btn.dataset.vote;
      if (vote === current) return; // already the active choice

      const previous = current;
      current = vote;
      writeCookie(cookieName, vote);
      paint(vote);
      if (text) text.textContent = 'Спасибо за ваш отзыв!';

      try {
        const body = new URLSearchParams({
          action: 'bm_article_feedback',
          post_id: postId,
          vote,
          previous,
          nonce: nonce || '',
        });
        await fetch(ajaxUrl, { method: 'POST', body, credentials: 'same-origin' });
      } catch (err) {
        // Vote is best-effort; the UI already reflects the choice.
        console.warn('[bm] feedback vote failed', err);
      }
    });
  });
}

registerComponent('article-feedback', initFeedback);

// Copies text to the clipboard. Uses the async Clipboard API in secure contexts
// and falls back to a hidden textarea + execCommand (works over plain http, e.g.
// *.local), so "Поделиться" works in local development too.
async function copyToClipboard(text) {
  if (navigator.clipboard && window.isSecureContext) {
    try {
      await navigator.clipboard.writeText(text);
      return true;
    } catch {
      /* fall through to legacy path */
    }
  }
  try {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.position = 'fixed';
    ta.style.top = '-1000px';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    const ok = document.execCommand('copy');
    ta.remove();
    return ok;
  } catch {
    return false;
  }
}

function initShareButtons(root = document) {
  root.querySelectorAll('[data-bm-share]').forEach((btn) => {
    if (btn.dataset.bmShareBound === '1') return;
    btn.dataset.bmShareBound = '1';

    const label = btn.querySelector('.bm-share__label');
    const original = label ? label.textContent : '';

    btn.addEventListener('click', async () => {
      const url = btn.dataset.shareUrl || window.location.href;
      const title = btn.dataset.shareTitle || document.title;

      if (navigator.share) {
        try {
          await navigator.share({ title, url });
          return;
        } catch {
          return; // user dismissed the share sheet
        }
      }

      const copied = await copyToClipboard(url);
      if (label) {
        label.textContent = copied ? 'Ссылка скопирована' : 'Скопируйте ссылку из адресной строки';
        btn.classList.add('is-copied');
        window.setTimeout(() => {
          label.textContent = original;
          btn.classList.remove('is-copied');
        }, 2000);
      }
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => initShareButtons());
} else {
  initShareButtons();
}

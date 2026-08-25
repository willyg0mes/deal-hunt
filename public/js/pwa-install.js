/**
 * Banner de instalação PWA (atalho na tela inicial).
 * Chrome/Android: beforeinstallprompt. iOS Safari: dica manual.
 */
(function () {
  const STORAGE_KEY = "dealhunt-pwa-install-dismissed";
  const DAYS = 14;

  function standalone() {
    return (
      window.matchMedia("(display-mode: standalone)").matches ||
      Boolean(window.navigator.standalone)
    );
  }

  function dismissed() {
    try {
      const until = Number(localStorage.getItem(STORAGE_KEY) || 0);
      return Number.isFinite(until) && Date.now() < until;
    } catch {
      return false;
    }
  }

  function dismiss() {
    try {
      localStorage.setItem(
        STORAGE_KEY,
        String(Date.now() + DAYS * 24 * 60 * 60 * 1000),
      );
    } catch {
      // ignore
    }
  }

  function isIos() {
    return /iphone|ipad|ipod/i.test(navigator.userAgent);
  }

  function mountBanner({ deferred, ios }) {
    if (document.getElementById("dealhunt-pwa-install")) {
      return;
    }

    const root = document.createElement("div");
    root.id = "dealhunt-pwa-install";
    root.setAttribute("role", "dialog");
    root.setAttribute("aria-label", "Instalar DealHunt");
    root.innerHTML = `
      <div class="dh-pwa-card">
        <img class="dh-pwa-icon" src="/images/brand/icon.png" alt="" width="40" height="40" />
        <div class="dh-pwa-body">
          <p class="dh-pwa-title">Instalar DealHunt</p>
          <p class="dh-pwa-text">${
            ios
              ? "No Safari: Compartilhar → Adicionar à Tela de Início."
              : "Acesse ofertas mais rápido, como um app no celular."
          }</p>
          <div class="dh-pwa-actions">
            ${
              !ios && deferred
                ? '<button type="button" class="dh-pwa-primary" data-action="install">Instalar</button>'
                : ""
            }
            <button type="button" class="dh-pwa-ghost" data-action="dismiss">Agora não</button>
          </div>
        </div>
        <button type="button" class="dh-pwa-close" data-action="dismiss" aria-label="Fechar">×</button>
      </div>
    `;

    const style = document.createElement("style");
    style.textContent = `
      #dealhunt-pwa-install {
        position: fixed;
        inset: auto 0 0 auto;
        z-index: 90;
        padding: 12px;
        padding-bottom: max(12px, env(safe-area-inset-bottom));
        pointer-events: none;
        display: flex;
        justify-content: flex-end;
        width: 100%;
      }
      #dealhunt-pwa-install .dh-pwa-card {
        pointer-events: auto;
        width: min(100%, 22rem);
        display: flex;
        gap: 12px;
        align-items: flex-start;
        background: linear-gradient(145deg, rgba(127,29,29,.92), rgba(15,23,42,.96));
        color: #fff;
        border: 1px solid rgba(248,113,113,.35);
        border-radius: 1rem;
        padding: 14px;
        box-shadow: 0 18px 40px rgba(0,0,0,.45);
        backdrop-filter: blur(10px);
      }
      #dealhunt-pwa-install .dh-pwa-icon {
        flex: 0 0 auto;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: .75rem;
        object-fit: cover;
        ring: 1px solid rgba(255,255,255,.15);
      }
      #dealhunt-pwa-install .dh-pwa-body { flex: 1; min-width: 0; }
      #dealhunt-pwa-install .dh-pwa-title {
        margin: 0;
        font-size: .875rem;
        font-weight: 700;
      }
      #dealhunt-pwa-install .dh-pwa-text {
        margin: .2rem 0 0;
        font-size: .75rem;
        line-height: 1.35;
        opacity: .78;
      }
      #dealhunt-pwa-install .dh-pwa-actions {
        display: flex;
        gap: .5rem;
        margin-top: .75rem;
      }
      #dealhunt-pwa-install .dh-pwa-primary {
        border: 0;
        border-radius: .5rem;
        padding: .4rem .85rem;
        font-size: .75rem;
        font-weight: 700;
        background: #dc2626;
        color: #fff;
        cursor: pointer;
      }
      #dealhunt-pwa-install .dh-pwa-ghost,
      #dealhunt-pwa-install .dh-pwa-close {
        border: 0;
        background: transparent;
        color: rgba(255,255,255,.78);
        cursor: pointer;
        font-size: .75rem;
        font-weight: 600;
        border-radius: .5rem;
        padding: .35rem .6rem;
      }
      #dealhunt-pwa-install .dh-pwa-close {
        font-size: 1.1rem;
        line-height: 1;
        padding: .15rem .4rem;
        opacity: .65;
      }
    `;

    document.head.appendChild(style);
    document.body.appendChild(root);

    root.addEventListener("click", async (event) => {
      const target = event.target.closest("[data-action]");
      if (!target) return;
      const action = target.getAttribute("data-action");
      if (action === "dismiss") {
        dismiss();
        root.remove();
        return;
      }
      if (action === "install" && deferred) {
        deferred.prompt();
        const choice = await deferred.userChoice;
        if (choice.outcome !== "accepted") {
          dismiss();
        }
        root.remove();
      }
    });
  }

  function boot() {
    if (standalone() || dismissed()) {
      return;
    }

    if ("serviceWorker" in navigator) {
      navigator.serviceWorker.register("/sw.js").catch(() => {});
    }

    if (isIos()) {
      mountBanner({ deferred: null, ios: true });
      return;
    }

    window.addEventListener("beforeinstallprompt", (event) => {
      event.preventDefault();
      mountBanner({ deferred: event, ios: false });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();

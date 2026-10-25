/* E.E.S.T. N.º 5 "Amancio Williams" — interacciones */

/* ---- Modo Claro / Modo Oscuro con persistencia ---- */
(function () {
  "use strict";

  var STORAGE_KEY = "eest5_theme";
  var toggleBtns = document.querySelectorAll(".theme-toggle");

  function getTheme() {
    var saved = localStorage.getItem(STORAGE_KEY);
    if (saved === "dark" || saved === "light") return saved;
    if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
      return "dark";
    }
    return "light";
  }

  function applyTheme(theme) {
    if (theme === "dark") {
      document.documentElement.setAttribute("data-theme", "dark");
    } else {
      document.documentElement.removeAttribute("data-theme");
    }

    toggleBtns.forEach(function (btn) {
      var isDark = theme === "dark";
      btn.setAttribute(
        "aria-label",
        isDark ? "Cambiar a modo claro" : "Cambiar a modo oscuro"
      );
      btn.setAttribute(
        "title",
        isDark ? "Activar modo claro" : "Activar modo oscuro"
      );
    });
  }

  applyTheme(getTheme());

  toggleBtns.forEach(function (btn) {
    btn.addEventListener("click", function () {
      var current = document.documentElement.getAttribute("data-theme") === "dark" ? "dark" : "light";
      var next = current === "dark" ? "light" : "dark";
      localStorage.setItem(STORAGE_KEY, next);
      applyTheme(next);
    });
  });

  if (window.matchMedia) {
    window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", function (e) {
      if (!localStorage.getItem(STORAGE_KEY)) {
        applyTheme(e.matches ? "dark" : "light");
      }
    });
  }
})();

(function () {
  "use strict";

  var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ---- Header: estado al hacer scroll ---- */
  var header = document.getElementById("header");
  function onScroll() {
    if (!header) return;
    header.classList.toggle("is-scrolled", window.scrollY > 24);
  }
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });

  /* ---- Menú mobile ---- */
  var burger = document.getElementById("burger");
  var nav = document.getElementById("nav");

  function closeNav() {
    if (!nav || !burger) return;
    nav.classList.remove("is-open");
    burger.classList.remove("is-open");
    burger.setAttribute("aria-expanded", "false");
    burger.setAttribute("aria-label", "Abrir menú");
  }

  if (burger && nav) {
    burger.addEventListener("click", function () {
      var open = nav.classList.toggle("is-open");
      burger.classList.toggle("is-open", open);
      burger.setAttribute("aria-expanded", open ? "true" : "false");
      burger.setAttribute("aria-label", open ? "Cerrar menú" : "Abrir menú");
    });

    nav.addEventListener("click", function (e) {
      if (e.target && e.target.tagName === "A") closeNav();
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeNav();
    });
  }

  /* ---- Aparición progresiva ---- */
  var items = Array.prototype.slice.call(document.querySelectorAll(".reveal"));
  if (reduced || !("IntersectionObserver" in window)) {
    items.forEach(function (el) {
      el.classList.add("is-visible");
    });
  } else {
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-visible");
          io.unobserve(entry.target);
        });
      },
      { rootMargin: "0px 0px -12% 0px", threshold: 0.12 }
    );
    items.forEach(function (el) {
      io.observe(el);
    });
  }

  /* ---- Navegación suave con offset de header ---- */
  document.addEventListener("click", function (e) {
    var link = e.target && e.target.closest ? e.target.closest('a[href^="#"]') : null;
    if (!link) return;
    var id = link.getAttribute("href");
    if (!id || id === "#") return;
    var target = document.querySelector(id);
    if (!target) return;
    e.preventDefault();
    var top = target.getBoundingClientRect().top + window.scrollY - 72;
    window.scrollTo({ top: top, behavior: reduced ? "auto" : "smooth" });
    target.setAttribute("tabindex", "-1");
    target.focus({ preventScroll: true });
  });
})();

/* ---- Línea de tiempo: hitos v2 desplegables ---- */
(function () {
  "use strict";
  var hitos = document.querySelectorAll(".hito-v2");
  var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  Array.prototype.forEach.call(hitos, function (hito) {
    var row = hito.querySelector(".hito-v2-row");
    var panel = hito.querySelector(".hito-v2-card");
    var closeBtn = hito.querySelector(".hito-v2-btn-close");
    if (!row || !panel) return;

    function scrollToHito() {
      // Dejamos tiempo para que el DOM colapse los demás hitos y calcule la posición real
      setTimeout(function () {
        var header = document.getElementById("header");
        var headerHeight = header ? header.offsetHeight : 76;
        var rect = hito.getBoundingClientRect();
        var currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        var targetY = currentScroll + rect.top - headerHeight - 16;

        window.scrollTo({
          top: Math.max(0, targetY),
          behavior: reduced ? "auto" : "smooth"
        });
      }, 80);
    }

    function collapseHito() {
      hito.classList.remove("is-expanded");
      row.setAttribute("aria-expanded", "false");
      panel.hidden = true;
    }

    function expandHito() {
      // Cerrar los demás hitos
      Array.prototype.forEach.call(hitos, function (other) {
        if (other === hito) return;
        other.classList.remove("is-expanded");
        var otherRow = other.querySelector(".hito-v2-row");
        var otherPanel = other.querySelector(".hito-v2-card");
        if (otherRow) otherRow.setAttribute("aria-expanded", "false");
        if (otherPanel) otherPanel.hidden = true;
      });

      hito.classList.add("is-expanded");
      row.setAttribute("aria-expanded", "true");
      panel.hidden = false;

      // Desplazar automáticamente para que el proyecto ampliado se vea completo desde arriba
      scrollToHito();
    }

    row.addEventListener("click", function () {
      if (hito.classList.contains("is-expanded")) {
        collapseHito();
      } else {
        expandHito();
      }
    });

    row.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        if (hito.classList.contains("is-expanded")) {
          collapseHito();
        } else {
          expandHito();
        }
      }
    });

    if (closeBtn) {
      closeBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        collapseHito();
      });
    }
  });

  // Vinculación de Pop-up Modal a las fotos de los Hitos Históricos
  Array.prototype.forEach.call(hitos, function (hito) {
    var cardFoto = hito.querySelector(".hito-v2-card-foto");
    var thumb = hito.querySelector(".hito-v2-thumb");

    function triggerHitoModal(e) {
      if (e) e.stopPropagation();
      var title = (hito.querySelector(".hito-v2-card-title") || hito.querySelector(".hito-v2-row-title") || {}).textContent || "Hito Histórico";
      var year = (hito.querySelector(".hito-v2-year") || {}).textContent || "";
      var badge = (hito.querySelector(".hito-v2-badge") || {}).textContent || "Proyecto Destacado";

      var img = cardFoto ? cardFoto.querySelector("img") : (thumb || null);
      if (!img) return;
      var imgSrc = img.getAttribute("src") || img.src;
      var imgAlt = img.getAttribute("alt") || title;

      var captionEl = cardFoto ? cardFoto.querySelector("figcaption") : null;
      var caption = captionEl ? captionEl.textContent.trim() : "";

      var descEl = hito.querySelector(".hito-v2-card-desc") || hito.querySelector(".hito-v2-row-summary");
      var descHtml = descEl ? descEl.innerHTML : "";

      var pills = hito.querySelectorAll(".hito-v2-pill");
      var pillsHtml = "";
      if (pills && pills.length) {
        pillsHtml = '<div class="news-modal-hitos-pills">';
        Array.prototype.forEach.call(pills, function (pill) {
          var label = (pill.querySelector(".pill-label") || {}).textContent || "";
          var val = (pill.querySelector(".pill-val") || {}).textContent || "";
          if (val) {
            pillsHtml += '<div class="news-modal-hitos-pill"><strong>' + label + '</strong><span>' + val + '</span></div>';
          }
        });
        pillsHtml += '</div>';
      }

      var linkEl = hito.querySelector(".btn-hito-detalles");
      var linkHref = linkEl ? linkEl.getAttribute("href") : "";

      var isSubdir = window.location.pathname.includes("/pages/");
      var logoSrc = isSubdir ? "../img/logo.png" : "img/logo.png";

      var html =
        '<div class="news-modal-media">' +
          '<img src="' + imgSrc + '" alt="' + imgAlt + '" />' +
        '</div>' +
        '<div class="news-modal-info">' +
          '<div class="news-modal-header">' +
            '<img src="' + logoSrc + '" alt="EEST5" />' +
            '<div>' +
              '<p class="author-name">' + title + '</p>' +
              '<p class="post-date">E.E.S.T. N.º 5 "Amancio Williams"' + (year ? ' · Año ' + year : '') + '</p>' +
            '</div>' +
          '</div>' +
          (caption ? '<div class="news-modal-hitos-caption">📷 ' + caption + '</div>' : '') +
          '<div class="news-modal-caption">' + descHtml + '</div>' +
          pillsHtml +
          '<div class="news-modal-footer">' +
            '<span class="news-modal-likes">' + badge + '</span>' +
            (linkHref ? '<a href="' + linkHref + '" target="_blank" rel="noopener noreferrer" class="news-modal-link">Ver más detalles ↗</a>' : '') +
          '</div>' +
        '</div>';

      EESTModal.open(html);
    }

    if (cardFoto) {
      cardFoto.addEventListener("click", triggerHitoModal);
      cardFoto.setAttribute("role", "button");
      cardFoto.setAttribute("tabindex", "0");
      cardFoto.setAttribute("title", "Clic para ampliar fotografía y detalles");
      cardFoto.addEventListener("keydown", function (e) {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          triggerHitoModal(e);
        }
      });
    }

    if (thumb) {
      thumb.addEventListener("click", function (e) {
        e.stopPropagation();
        triggerHitoModal(e);
      });
    }
  });
})();

/* ===========================================================
   MODAL POPUP GLOBAL (NOTICIAS / COMUNICADOS / HITOS / IMÁGENES)
   =========================================================== */
var EESTModal = (function () {
  "use strict";

  var modal = document.getElementById("newsModal");
  if (!modal) {
    modal = document.createElement("div");
    modal.id = "newsModal";
    modal.className = "news-modal";
    modal.setAttribute("aria-hidden", "true");
    modal.setAttribute("role", "dialog");
    modal.setAttribute("aria-modal", "true");
    modal.innerHTML =
      '<div class="news-modal-overlay" id="newsModalOverlay"></div>' +
      '<div class="news-modal-container">' +
        '<button type="button" class="news-modal-close" id="newsModalClose" aria-label="Cerrar modal">&times;</button>' +
        '<div class="news-modal-content" id="newsModalContent"></div>' +
      '</div>';
    document.body.appendChild(modal);
  }

  var modalOverlay = document.getElementById("newsModalOverlay");
  var modalClose = document.getElementById("newsModalClose");
  var modalContent = document.getElementById("newsModalContent");

  function open(html) {
    if (!modal || !modalContent) return;
    modalContent.innerHTML = html;
    modal.classList.add("is-active");
    modal.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  function close() {
    if (!modal) return;
    modal.classList.remove("is-active");
    modal.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    if (modalContent) {
      var video = modalContent.querySelector("video");
      if (video) video.pause();
    }
  }

  if (modalClose) modalClose.addEventListener("click", close);
  if (modalOverlay) modalOverlay.addEventListener("click", close);

  window.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && modal && modal.classList.contains("is-active")) {
      close();
    }
  });

  return { open: open, close: close };
})();

/* ---- Carrusel dinámico de Instagram (Behold API) ---- */
(function () {
  "use strict";

  var FEED_URL = "https://feeds.behold.so/kYahsnwH7WymWcYH98BW";
  var CACHE_KEY = "eest5_behold_cache";
  var track = document.getElementById("igCarouselTrack");
  var prevBtn = document.getElementById("igPrevBtn");
  var nextBtn = document.getElementById("igNextBtn");

  if (!track) return;

  function updateArrows() {
    if (!prevBtn || !nextBtn) return;
    var maxScroll = track.scrollWidth - track.clientWidth - 8;
    prevBtn.disabled = track.scrollLeft <= 8;
    nextBtn.disabled = track.scrollLeft >= maxScroll;
  }

  if (prevBtn && nextBtn) {
    prevBtn.addEventListener("click", function () {
      var scrollAmount = track.clientWidth * 0.75;
      track.scrollBy({ left: -scrollAmount, behavior: "smooth" });
    });

    nextBtn.addEventListener("click", function () {
      var scrollAmount = track.clientWidth * 0.75;
      track.scrollBy({ left: scrollAmount, behavior: "smooth" });
    });

    track.addEventListener("scroll", updateArrows, { passive: true });
    window.addEventListener("resize", updateArrows, { passive: true });
  }

  function escapeHtml(text) {
    if (!text) return "";
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function formatDate(isoStr) {
    if (!isoStr) return "";
    try {
      var d = new Date(isoStr);
      return d.toLocaleDateString("es-AR", { day: "numeric", month: "short" });
    } catch (e) {
      return "";
    }
  }

  var currentPosts = [];

  function openModal(postIndex) {
    var post = currentPosts[postIndex];
    if (!post) return;

    var isVideo = post.mediaType === "VIDEO";
    var primaryUrl = isVideo ? (post.mediaUrl || post.thumbnailUrl) : post.mediaUrl;
    var caption = post.caption || post.prunedCaption || "Sin descripción disponible.";
    var dateStr = formatDate(post.timestamp);
    var likes = post.likeCount ? "❤️ " + post.likeCount + " Me gusta" : "";

    var mediaHtml = "";
    if (isVideo && post.mediaUrl) {
      mediaHtml = '<video src="' + escapeHtml(post.mediaUrl) + '" controls autoplay playsinline></video>';
    } else {
      mediaHtml = '<img src="' + escapeHtml(primaryUrl) + '" alt="Publicación completa" />';
    }

    var html =
      '<div class="news-modal-media">' +
        mediaHtml +
      '</div>' +
      '<div class="news-modal-info">' +
        '<div class="news-modal-header">' +
          '<img src="img/logo.png" alt="EEST5" />' +
          '<div>' +
            '<p class="author-name">E.E.S.T. N.º 5 "Amancio Williams"</p>' +
            (dateStr ? '<p class="post-date">Publicado el ' + dateStr + '</p>' : '') +
          '</div>' +
        '</div>' +
        '<div class="news-modal-caption">' + escapeHtml(caption) + '</div>' +
        '<div class="news-modal-footer">' +
          (likes ? '<span class="news-modal-likes">' + likes + '</span>' : '<span></span>') +
          '<a href="' + escapeHtml(post.permalink) + '" target="_blank" rel="noopener noreferrer" class="news-modal-link">Ver en Instagram ↗</a>' +
        '</div>' +
      '</div>';

    EESTModal.open(html);
  }

  function renderPosts(posts) {
    if (!posts || !posts.length) return;
    currentPosts = posts;

    var html = posts
      .map(function (post, index) {
        var isVideo = post.mediaType === "VIDEO";
        // Usar la imagen original sin recorte para no cortar tablas ni texto
        var primaryUrl = isVideo ? (post.thumbnailUrl || post.mediaUrl) : post.mediaUrl;
        var fallbackUrl =
          (post.sizes && post.sizes.medium && post.sizes.medium.mediaUrl) ||
          (post.sizes && post.sizes.small && post.sizes.small.mediaUrl) ||
          primaryUrl;

        var caption = post.prunedCaption || post.caption || "Ver comunicado institucional";
        var dateStr = formatDate(post.timestamp);
        var likesHtml =
          post.likeCount && post.likeCount > 0
            ? '<span class="ig-card-likes">❤️ ' + post.likeCount + "</span>"
            : "";

        return (
          '<div role="button" tabindex="0" class="ig-card" data-index="' + index + '" style="cursor: pointer;">' +
            '<div class="ig-card-media">' +
              '<div class="ig-card-bg-blur" style="background-image: url(\'' + escapeHtml(fallbackUrl) + '\');"></div>' +
              '<img class="ig-card-img" src="' + escapeHtml(primaryUrl) + '" alt="' + escapeHtml(caption.slice(0, 90)) + '" loading="lazy" onerror="this.onerror=null;this.src=\'' + escapeHtml(fallbackUrl) + '\';" />' +
              (isVideo ? '<span class="ig-card-badge-video" style="position: absolute; top: 10px; left: 10px; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; display: flex; align-items: center; gap: 4px; z-index: 2;"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 21"></polygon></svg> Video</span>' : '') +
              (dateStr ? '<span class="ig-card-date">' + dateStr + "</span>" : "") +
            '</div>' +
            '<div class="ig-card-body">' +
              '<p class="ig-card-caption">' + escapeHtml(caption) + '</p>' +
              '<div class="ig-card-footer">' +
                '<span class="ig-card-link">Leer noticia completa 🔍</span>' +
                likesHtml +
              '</div>' +
            '</div>' +
          '</div>'
        );
      })
      .join("");

    track.innerHTML = html;

    // Vincular clic para abrir modal
    var cards = track.querySelectorAll(".ig-card");
    cards.forEach(function (card) {
      card.addEventListener("click", function (e) {
        e.preventDefault();
        var idx = parseInt(card.getAttribute("data-index"), 10);
        openModal(idx);
      });
      card.addEventListener("keydown", function (e) {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          var idx = parseInt(card.getAttribute("data-index"), 10);
          openModal(idx);
        }
      });
    });

    setTimeout(updateArrows, 150);
  }

  // Carga instantánea desde caché local para evitar pantalla en blanco o skeletons
  try {
    var cached = localStorage.getItem(CACHE_KEY);
    if (cached) {
      var parsedCache = JSON.parse(cached);
      if (Array.isArray(parsedCache) && parsedCache.length > 0) {
        renderPosts(parsedCache);
      }
    }
  } catch (e) {}

  // Sincronización automática con Behold
  function loadBeholdFeed(url) {
    var feedUrl = url || FEED_URL;
    fetch(feedUrl)
      .then(function (res) {
        if (!res.ok) throw new Error("HTTP error " + res.status);
        return res.json();
      })
      .then(function (data) {
        var posts = Array.isArray(data) ? data : data.posts || [];
        if (posts.length > 0) {
          try {
            localStorage.setItem(CACHE_KEY, JSON.stringify(posts));
          } catch (e) {}
          renderPosts(posts);
        }
      })
      .catch(function (err) {
        console.warn("Aviso cargando feed de Behold:", err);
        // Si no teníamos nada en caché y falló el fetch, mostrar aviso amigable
        if (!currentPosts || currentPosts.length === 0) {
          track.innerHTML =
            '<div class="ig-card-empty"><p>Enterate de los comunicados en <a href="https://www.instagram.com/tecnicacinco_mdq/" target="_blank" rel="noopener">@tecnicacinco_mdq</a></p></div>';
        }
      });
  }

  window.loadBeholdFeed = loadBeholdFeed;
  loadBeholdFeed(FEED_URL);
})();

/* ---- Modal para otras imágenes institucionales (Especialidades, ExpoTec) ---- */
(function () {
  "use strict";

  // Especialidades (index.html)
  document.querySelectorAll(".esp").forEach(function (card) {
    var foto = card.querySelector(".esp-foto");
    if (!foto) return;
    foto.style.cursor = "pointer";
    foto.setAttribute("title", "Clic para ampliar imagen");
    foto.addEventListener("click", function () {
      var img = foto.querySelector("img");
      if (!img) return;
      var title = (card.querySelector("h3") || {}).textContent || "Especialidad Técnica";
      var subtitle = (card.querySelector(".esp-titulo") || {}).textContent || "";
      var desc = (card.querySelector(".esp-desc") || {}).textContent || "";
      var num = (card.querySelector(".esp-num") || {}).textContent || "";
      var isSubdir = window.location.pathname.includes("/pages/");
      var logoSrc = isSubdir ? "../img/logo.png" : "img/logo.png";

      var html =
        '<div class="news-modal-media">' +
          '<img src="' + img.src + '" alt="' + (img.alt || title) + '" />' +
        '</div>' +
        '<div class="news-modal-info">' +
          '<div class="news-modal-header">' +
            '<img src="' + logoSrc + '" alt="EEST5" />' +
            '<div>' +
              '<p class="author-name">' + title + '</p>' +
              '<p class="post-date">' + subtitle + '</p>' +
            '</div>' +
          '</div>' +
          '<div class="news-modal-caption">' + desc + '</div>' +
          '<div class="news-modal-footer">' +
            '<span class="news-modal-likes">Especialidad ' + num + '</span>' +
            '<a href="#especialidades" class="news-modal-link" onclick="if(window.EESTModal) window.EESTModal.close()">7 años de formación</a>' +
          '</div>' +
        '</div>';
      if (window.EESTModal) window.EESTModal.open(html);
    });
  });

  // ExpoTec Sectores (expotec.html)
  document.querySelectorAll(".expo-card").forEach(function (card) {
    var img = card.querySelector("img");
    if (!img) return;
    img.style.cursor = "pointer";
    img.setAttribute("title", "Clic para ampliar fotografía");
    img.addEventListener("click", function () {
      var title = (card.querySelector("h3") || {}).textContent || "Sector ExpoTec";
      var desc = (card.querySelector("p") || {}).textContent || "";
      var num = (card.querySelector(".expo-num") || {}).textContent || "";
      var isSubdir = window.location.pathname.includes("/pages/");
      var logoSrc = isSubdir ? "../img/logo.png" : "img/logo.png";

      var html =
        '<div class="news-modal-media">' +
          '<img src="' + img.src + '" alt="' + (img.alt || title) + '" />' +
        '</div>' +
        '<div class="news-modal-info">' +
          '<div class="news-modal-header">' +
            '<img src="' + logoSrc + '" alt="EEST5" />' +
            '<div>' +
              '<p class="author-name">' + title + '</p>' +
              '<p class="post-date">ExpoTec · Muestra Anual de Proyectos</p>' +
            '</div>' +
          '</div>' +
          '<div class="news-modal-caption">' + desc + '</div>' +
          '<div class="news-modal-footer">' +
            '<span class="news-modal-likes">Sector ' + num + '</span>' +
            '<span class="news-modal-link">E.E.S.T. N.º 5</span>' +
          '</div>' +
        '</div>';
      if (window.EESTModal) window.EESTModal.open(html);
    });
  });
})();



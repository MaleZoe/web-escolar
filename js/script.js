/* E.E.S.T. N.º 5 "Amancio Williams" — interacciones */
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

/* ---- Línea de tiempo: hitos desplegables ---- */
(function () {
  "use strict";
  var heads = document.querySelectorAll(".hito-head");
  Array.prototype.forEach.call(heads, function (head) {
    head.addEventListener("click", function () {
      var panel = document.getElementById(head.getAttribute("aria-controls"));
      if (!panel) return;
      var open = head.getAttribute("aria-expanded") === "true";
      // cerrar los demás
      Array.prototype.forEach.call(heads, function (other) {
        if (other === head) return;
        other.setAttribute("aria-expanded", "false");
        var p = document.getElementById(other.getAttribute("aria-controls"));
        if (p) p.hidden = true;
      });
      head.setAttribute("aria-expanded", open ? "false" : "true");
      panel.hidden = open;
    });
  });
})();


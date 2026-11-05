/* Integración dinámica con MySQL / TiDB Cloud via API */
(function () {
  "use strict";

  var isSubdir = window.location.pathname.includes("/pages/");
  var apiEndpoint = (function () {
    try {
      if (document.currentScript && document.currentScript.src) {
        return new URL("../api/get_public_data.php", document.currentScript.src).href + "?_t=" + Date.now();
      }
    } catch (e) {}
    return (isSubdir ? "../api/get_public_data.php" : "api/get_public_data.php") + "?_t=" + Date.now();
  })();

  // El feed de Instagram se pide una sola vez: con la URL del panel o con la predeterminada.
  var beholdRequested = false;
  function requestBehold(url) {
    if (beholdRequested || typeof window.loadBeholdFeed !== "function") return;
    beholdRequested = true;
    window.loadBeholdFeed(url);
  }

  fetch(apiEndpoint, { cache: "no-store" })
    .then(function (res) {
      if (!res.ok) throw new Error("HTTP error " + res.status);
      return res.json();
    })
    .then(function (data) {
      if (!data || !data.success) {
        requestBehold();
        return;
      }

      var c = data.contenido || {};
      var imgPrefix = (function () {
        try {
          if (document.currentScript && document.currentScript.src) {
            return new URL("../img/", document.currentScript.src).href;
          }
        } catch (e) {}
        return isSubdir ? "../img/" : "img/";
      })();

      function sanitizeEncoding(str) {
        if (!str || typeof str !== "string") return str;
        return str
          .replace(/Dise\?o/g, "Diseño")
          .replace(/planificaci\?n/g, "planificación")
          .replace(/representaci\?n/g, "representación")
          .replace(/ejecuci\?n/g, "ejecución")
          .replace(/construcci\?n/g, "construcción")
          .replace(/t\?cnico/gi, function(m) { return m[0] === 'T' ? "Técnico" : "técnico"; })
          .replace(/c\?mputo/gi, function(m) { return m[0] === 'C' ? "Cómputo" : "cómputo"; })
          .replace(/direcci\?n/gi, function(m) { return m[0] === 'D' ? "Dirección" : "dirección"; })
          .replace(/electr\?nic/gi, function(m) { return m[0] === 'E' ? "Electrónic" : "electrónic"; })
          .replace(/inform\?tic/gi, function(m) { return m[0] === 'I' ? "Informátic" : "informátic"; })
          .replace(/a\?o/gi, function(m) { return m[0] === 'A' ? "Año" : "año"; });
      }

      function isSafeUrl(url) {
        return /^(https?:\/\/|mailto:)/i.test(String(url || ""));
      }

      // Texto de la base con el formato mínimo que usa el sitio: negrita, cursiva y saltos de línea.
      // Todo lo demás queda como texto, así que nunca se ejecuta como código.
      function setRichText(el, value) {
        el.innerHTML = String(value)
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/&lt;(\/?)(strong|em|b|i)&gt;/gi, "<$1$2>")
          .replace(/&lt;br\s*\/?&gt;/gi, "<br>")
          .replace(/\n/g, "<br>");
      }

      function resolveImg(val) {
        if (!val) return "";
        if (val.startsWith("data:") || val.startsWith("http://") || val.startsWith("https://") || val.startsWith("/") || val.startsWith("../")) {
          return val;
        }
        return imgPrefix + val;
      }

      // ================= 1. HERO, PORTADA & LOGO =================
      if (c.hero_titulo) {
        var heroTitle = document.querySelector(".hero-title");
        if (heroTitle) {
          setRichText(heroTitle, c.hero_titulo);
        }
      }

      if (c.hero_foto) {
        var heroImg = document.querySelector(".hero-media img");
        if (heroImg) heroImg.src = resolveImg(c.hero_foto);
      }

      if (c.site_logo) {
        var brandImgs = document.querySelectorAll(".brand img, .footer-brand-header img");
        brandImgs.forEach(function (img) {
          img.src = resolveImg(c.site_logo);
        });
      }

      if (c.hero_slogan) {
        var sloganEl = document.querySelector(".hero-slogan");
        if (sloganEl) setRichText(sloganEl, c.hero_slogan);
      }

      if (c.hero_subtitulo) {
        var heroSub = document.querySelector(".hero-sub");
        if (heroSub) heroSub.textContent = c.hero_subtitulo;
        var heroLead = document.querySelector(".hero-lead");
        if (heroLead) heroLead.textContent = c.hero_subtitulo;
      }

      if (c.expotec_anuncio) {
        document.querySelectorAll(".expo-anuncio").forEach(function (el) {
          el.textContent = c.expotec_anuncio;
          el.hidden = false;
        });
      }

      // Footer general info
      var fInfo = document.querySelector(".footer-info");
      if (fInfo) {
        var dir = c.direccion || "Juan B. Justo 4287, Mar del Plata";
        var mail = c.contacto_email || "infot5@eest5mdp.edu.ar";
        fInfo.textContent = dir + " · " + mail;
      }

      // WhatsApp links
      var waLinks = document.querySelectorAll("a[href*='wa.me']");
      waLinks.forEach(function (a) {
        a.remove();
      });

      // ================= 2. PÁGINA "LA ESCUELA" =================
      if (c.sobre_nosotros) {
        var sobreP = document.querySelector(".escuela-copy .lead");
        if (sobreP) sobreP.textContent = c.sobre_nosotros;
      }

      if (c.escuela_egresado_desc) {
        var egresadoP = document.querySelector(".escuela-copy .body");
        if (egresadoP) egresadoP.textContent = c.escuela_egresado_desc;
      }

      // Datos Rápidos (dl.datos)
      var dlItems = document.querySelectorAll(".escuela-copy dl.datos div");
      dlItems.forEach(function (div) {
        var dt = div.querySelector("dt");
        var dd = div.querySelector("dd");
        if (!dt || !dd) return;
        var text = dt.textContent.trim().toLowerCase();
        if (text === "nivel" && c.escuela_nivel) dd.textContent = c.escuela_nivel;
        if (text === "gestión" && c.escuela_gestion) dd.textContent = c.escuela_gestion;
        if (text === "título" && c.escuela_titulo) dd.textContent = c.escuela_titulo;
        if (text === "ciudad" && c.escuela_ciudad) dd.textContent = c.escuela_ciudad;
      });

      // Específico para escuela.html
      if (window.location.pathname.includes("escuela")) {
        if (c.escuela_hero_tag) {
          var ehTag = document.querySelector(".page-hero .section-tag");
          if (ehTag) ehTag.textContent = c.escuela_hero_tag;
        }
        if (c.escuela_hero_titulo) {
          var ehTitle = document.querySelector(".page-hero .page-title");
          if (ehTitle) ehTitle.textContent = c.escuela_hero_titulo;
        }
        if (c.escuela_foto_fachada) {
          var fImg = document.querySelector(".escuela-foto img");
          if (fImg) fImg.src = resolveImg(c.escuela_foto_fachada);
        }
        if (c.escuela_foto_caption) {
          var fCap = document.querySelector(".escuela-foto figcaption");
          if (fCap) {
            fCap.textContent = "";
            var capDot = document.createElement("span");
            capDot.className = "dot";
            capDot.setAttribute("aria-hidden", "true");
            fCap.appendChild(capDot);
            fCap.appendChild(document.createTextNode(c.escuela_foto_caption));
          }
        }

        // Taller
        if (c.escuela_taller_tag) {
          var tTag = document.querySelector(".taller .section-tag");
          if (tTag) tTag.textContent = c.escuela_taller_tag;
        }
        if (c.escuela_taller_titulo) {
          var tTitle = document.querySelector(".taller .h2");
          if (tTitle) tTitle.textContent = c.escuela_taller_titulo;
        }
        if (c.escuela_taller_desc) {
          var tDesc = document.querySelector(".taller .taller-panel .body");
          if (tDesc) tDesc.textContent = c.escuela_taller_desc;
        }
        if (c.escuela_foto_taller) {
          var tImg = document.querySelector(".taller-media img");
          if (tImg) tImg.src = resolveImg(c.escuela_foto_taller);
        }

        // Pasos formativos (01 al 04)
        var pasos = document.querySelectorAll(".taller .pasos li p");
        if (pasos.length >= 4) {
          if (c.escuela_paso_1) pasos[0].textContent = c.escuela_paso_1;
          if (c.escuela_paso_2) pasos[1].textContent = c.escuela_paso_2;
          if (c.escuela_paso_3) pasos[2].textContent = c.escuela_paso_3;
          if (c.escuela_paso_4) pasos[3].textContent = c.escuela_paso_4;
        }

        // Autoridades
        if (c.escuela_auth_tag) {
          var aTag = document.querySelector(".autoridades .section-tag");
          if (aTag) aTag.textContent = c.escuela_auth_tag;
        }
        if (c.escuela_auth_titulo) {
          var aTitle = document.querySelector(".autoridades .h2");
          if (aTitle) aTitle.textContent = c.escuela_auth_titulo;
        }
        if (c.escuela_dir_nombre) {
          var dirName = document.querySelector(".auth-director-name");
          if (dirName) dirName.textContent = c.escuela_dir_nombre;
        }
        if (c.escuela_dir_cargo) {
          var dirCargo = document.querySelector(".auth-director-role");
          if (dirCargo) dirCargo.textContent = c.escuela_dir_cargo;
        }

        // Departamentos
        var deptCards = document.querySelectorAll(".auth-dept-card");
        if (deptCards.length >= 3) {
          // Vicedirección
          var viceItems = deptCards[0].querySelectorAll(".auth-person-name");
          if (viceItems.length >= 3) {
            if (c.escuela_vice_tm) viceItems[0].textContent = c.escuela_vice_tm;
            if (c.escuela_vice_tt) viceItems[1].textContent = c.escuela_vice_tt;
            if (c.escuela_vice_tn) viceItems[2].textContent = c.escuela_vice_tn;
          }
          // Jefatura de Área
          var jefeItems = deptCards[1].querySelectorAll(".auth-person-name");
          if (jefeItems.length >= 3) {
            if (c.escuela_jefe_tm) jefeItems[0].textContent = c.escuela_jefe_tm;
            if (c.escuela_jefe_tt) jefeItems[1].textContent = c.escuela_jefe_tt;
            if (c.escuela_jefe_tn) jefeItems[2].textContent = c.escuela_jefe_tn;
          }
          // Secretaría y Prosecretaría
          var secItems = deptCards[2].querySelectorAll(".auth-person-name");
          if (secItems.length >= 2) {
            if (c.escuela_sec_tm) secItems[0].textContent = c.escuela_sec_tm;
            if (c.escuela_sec_tt) secItems[1].textContent = c.escuela_sec_tt;
          }
          if (secItems.length >= 3 && c.escuela_prosec) {
            secItems[2].textContent = c.escuela_prosec;
          }
        }

        // Marco Normativo & QRs
        if (c.escuela_qr_ra_url) {
          var raLinks = document.querySelectorAll("a[href*='seccion=regimen-academico']");
          raLinks.forEach(function (a) { if (!/^\s*javascript:/i.test(c.escuela_qr_ra_url)) a.href = c.escuela_qr_ra_url; });
        }
        if (c.escuela_qr_aic_url) {
          var aicLinks = document.querySelectorAll("a[href*='seccion=acuerdo-institucional']");
          aicLinks.forEach(function (a) { if (!/^\s*javascript:/i.test(c.escuela_qr_aic_url)) a.href = c.escuela_qr_aic_url; });
        }
      }

      // ================= 3. ANUNCIOS =================
      if (c.anuncios_titulo) {
        var anTitle = document.querySelector("#anuncios .section-title, #anuncios h2");
        if (anTitle) anTitle.textContent = c.anuncios_titulo;
      }
      if (c.anuncios_subtitulo) {
        var anSub = document.querySelector("#anuncios .section-sub, #anuncios .anuncios-sub");
        if (anSub) anSub.textContent = c.anuncios_subtitulo;
      }
      requestBehold(isSafeUrl(c.behold_feed_url) ? c.behold_feed_url : undefined);

      // ================= 4. EXPOTEC =================
      if (window.location.pathname.includes("expotec")) {
        if (c.expotec_tag) {
          var expTag = document.querySelector(".page-hero .section-tag");
          if (expTag) expTag.textContent = c.expotec_tag;
        }
        if (c.expotec_titulo) {
          var expTitle = document.querySelector(".page-hero .page-title");
          if (expTitle) expTitle.textContent = c.expotec_titulo;
        }
        if (c.expotec_desc) {
          var expLead = document.querySelector(".expo-intro .lead");
          if (expLead) setRichText(expLead, c.expotec_desc);
        }
        if (c.expotec_desc_secundaria) {
          var expBody = document.querySelector(".expo-intro .body");
          if (expBody) expBody.textContent = c.expotec_desc_secundaria;
        }

        // ExpoTec Datos Rápidos
        var expoDatos = document.querySelectorAll(".expo-datos li");
        expoDatos.forEach(function (li) {
          var span = li.querySelector("span");
          var p = li.querySelector("p");
          if (!span || !p) return;
          var label = span.textContent.trim().toLowerCase();
          if (label === "frecuencia" && c.expotec_frecuencia) p.textContent = c.expotec_frecuencia;
          if (label === "participan" && c.expotec_participan) p.textContent = c.expotec_participan;
          if (label === "abierta a" && c.expotec_abierta) p.textContent = c.expotec_abierta;
          if (label === "sede" && c.expotec_sede) p.textContent = c.expotec_sede;
        });

        // ExpoTec Sectores
        var expoSec = document.querySelector(".expo-grid");
        if (expoSec) {
          var expoParent = expoSec.closest("section");
          if (expoParent) {
            if (c.expotec_sectores_titulo) {
              var secTitle = expoParent.querySelector(".section-head .h2");
              if (secTitle) secTitle.textContent = c.expotec_sectores_titulo;
            }
            if (c.expotec_sectores_tag) {
              var secTag = expoParent.querySelector(".section-head .section-tag");
              if (secTag) secTag.textContent = c.expotec_sectores_tag;
            }
          }

          var expoCards = document.querySelectorAll(".expo-card");
          if (expoCards.length >= 3) {
            // 01: Construcciones
            if (c.expotec_s1_titulo) { var h1 = expoCards[0].querySelector("h3"); if (h1) h1.textContent = c.expotec_s1_titulo; }
            if (c.expotec_s1_desc) { var p1 = expoCards[0].querySelector("p"); if (p1) p1.textContent = c.expotec_s1_desc; }
            if (c.expotec_s1_img) { var img1 = expoCards[0].querySelector("img"); if (img1) img1.src = resolveImg(c.expotec_s1_img); }

            // 02: Informática
            if (c.expotec_s2_titulo) { var h2 = expoCards[1].querySelector("h3"); if (h2) h2.textContent = c.expotec_s2_titulo; }
            if (c.expotec_s2_desc) { var p2 = expoCards[1].querySelector("p"); if (p2) p2.textContent = c.expotec_s2_desc; }
            if (c.expotec_s2_img) { var img2 = expoCards[1].querySelector("img"); if (img2) img2.src = resolveImg(c.expotec_s2_img); }

            // 03: Electrónica
            if (c.expotec_s3_titulo) { var h3 = expoCards[2].querySelector("h3"); if (h3) h3.textContent = c.expotec_s3_titulo; }
            if (c.expotec_s3_desc) { var p3 = expoCards[2].querySelector("p"); if (p3) p3.textContent = c.expotec_s3_desc; }
            if (c.expotec_s3_img) { var img3 = expoCards[2].querySelector("img"); if (img3) img3.src = resolveImg(c.expotec_s3_img); }
          }
        }
      }

      // ================= ESPECIALIDADES TÉCNICAS (INDEX) =================
      var espSec = document.querySelector("#especialidades");
      if (espSec) {
        if (c.especialidades_tagline) {
          var espTag = espSec.querySelector(".section-head .h2");
          if (espTag) espTag.textContent = c.especialidades_tagline;
        }

        // La tarjeta visible es la de la base: se agregan o quitan para coincidir con ella.
        var espList = espSec.querySelector(".esp-list");
        if (espList && Array.isArray(data.especialidades) && data.especialidades.length > 0) {
          var espCards = Array.prototype.slice.call(espList.querySelectorAll(".esp"));
          var espTemplate = espCards[espCards.length - 1];

          data.especialidades.forEach(function (esp, idx) {
            var isNew = idx >= espCards.length;
            var card = espCards[idx];
            if (isNew) {
              card = espTemplate.cloneNode(true);
              card.className = "esp " + (/^esp--[a-z]+$/.test(esp.color || "") ? esp.color : "esp--verde") + " reveal is-visible";
              var newImg = card.querySelector(".esp-foto img");
              if (newImg) { newImg.removeAttribute("src"); newImg.alt = esp.nombre || ""; }
              espList.appendChild(card);
              if (window.EESTBindEspecialidad) window.EESTBindEspecialidad(card);
            }
            var h3 = card.querySelector(".esp-body h3");
            if (h3 && (esp.titulo || isNew)) h3.textContent = sanitizeEncoding(esp.titulo || esp.nombre || "");
            var tit = card.querySelector(".esp-titulo");
            if (tit && (esp.nombre || isNew)) tit.textContent = sanitizeEncoding(esp.nombre || "");
            var desc = card.querySelector(".esp-desc");
            if (desc && (esp.descripcion || isNew)) desc.textContent = sanitizeEncoding(esp.descripcion || "");
            var img = card.querySelector(".esp-foto img");
            if (img && esp.imagen) img.src = resolveImg(esp.imagen);
            var foto = card.querySelector(".esp-foto");
            if (foto && isNew && !esp.imagen) foto.hidden = true;
            var num = card.querySelector(".esp-num");
            if (num) num.textContent = esp.numero && !isNew ? esp.numero : ("0" + (idx + 1)).slice(-2);
          });

          espCards.slice(data.especialidades.length).forEach(function (card) { card.remove(); });
        }
      }

      // ================= 4.5. INSTITUCIONALES (institucionales.html) =================
      if (window.location.pathname.includes("institucionales")) {
        // Hero / Encabezado
        if (c.inst_hero_tag) {
          var hTag = document.querySelector(".page-hero .section-tag");
          if (hTag) hTag.textContent = c.inst_hero_tag;
        }
        if (c.inst_hero_titulo) {
          var hTitle = document.querySelector(".page-hero .page-title");
          if (hTitle) hTitle.textContent = c.inst_hero_titulo;
        }
        if (c.inst_hero_subtitulo) {
          var hSub = document.querySelector(".page-hero .hero-sub");
          if (hSub) hSub.textContent = c.inst_hero_subtitulo;
        }
        if (c.inst_tab_aic_label) {
          var btnAIC = document.getElementById("tabBtnAIC");
          if (btnAIC) btnAIC.textContent = c.inst_tab_aic_label;
          var optAIC = document.getElementById("optAIC");
          if (optAIC) optAIC.textContent = c.inst_tab_aic_label;
        }
        if (c.inst_tab_ra_label) {
          var btnRA = document.getElementById("tabBtnRA");
          if (btnRA) btnRA.textContent = c.inst_tab_ra_label;
          var optRA = document.getElementById("optRA");
          if (optRA) optRA.textContent = c.inst_tab_ra_label;
        }

        // Pestaña Régimen Académico
        var paneRA = document.getElementById("paneRA");
        if (paneRA) {
          if (c.inst_ra_titulo) {
            var raH2 = paneRA.querySelector("article h2");
            if (raH2) raH2.textContent = c.inst_ra_titulo;
          }
          if (c.inst_ra_fecha) {
            var raFecha = paneRA.querySelector("article p:nth-of-type(1)");
            if (raFecha) raFecha.textContent = c.inst_ra_fecha;
          }
          if (c.inst_ra_desc) {
            var raDesc = paneRA.querySelector("article p:nth-of-type(2)");
            if (raDesc) raDesc.textContent = c.inst_ra_desc;
          }

          // Video
          var raVid = paneRA.querySelector("video");
          var raSource = paneRA.querySelector("video source");
          if (c.inst_ra_video_file) {
            var vFileUrl = resolveImg(c.inst_ra_video_file);
            if (raSource) raSource.src = vFileUrl;
            if (raVid) raVid.load();
          } else if (isSafeUrl(c.inst_ra_video_url)) {
            if (raSource) raSource.src = c.inst_ra_video_url;
            if (raVid) raVid.load();
          }

          // Pilares: cada contenido va en el primer párrafo después de su título.
          var raH3s = paneRA.querySelectorAll("article h3");
          [1, 2, 3, 4, 5].forEach(function (n, i) {
            var h3 = raH3s[i];
            if (!h3) return;
            if (c["inst_ra_p" + n + "_titulo"]) h3.textContent = c["inst_ra_p" + n + "_titulo"];
            var body = c["inst_ra_p" + n + "_desc"];
            if (!body) return;
            var node = h3.nextElementSibling;
            while (node && node.tagName !== "P" && node.tagName !== "H3") node = node.nextElementSibling;
            if (node && node.tagName === "P") node.textContent = body;
          });
        }

        // Pestaña AIC
        var paneAIC = document.getElementById("paneAIC");
        if (paneAIC) {
          var aicCards = paneAIC.querySelectorAll(".inst-card");

          // Resumen editable de un capítulo. No reemplaza los artículos del reglamento.
          var setSummary = function (card, value, afterEl) {
            if (!card || !value) return;
            var anchor = afterEl || card.querySelector("h2");
            if (!anchor) return;
            var box = card.querySelector(".inst-chapter-summary");
            if (!box) {
              box = document.createElement("p");
              box.className = "inst-chapter-summary";
              anchor.insertAdjacentElement("afterend", box);
            }
            box.textContent = value;
          };

          if (aicCards[0]) {
            if (c.inst_aic_pres_titulo) {
              var aicH2 = aicCards[0].querySelector("h2");
              if (aicH2) aicH2.textContent = c.inst_aic_pres_titulo;
            }
            var presPs = aicCards[0].querySelectorAll("p");
            if (c.inst_aic_pres_texto && presPs[0]) presPs[0].textContent = c.inst_aic_pres_texto;
            if (c.inst_aic_familias_texto && presPs[2]) presPs[2].textContent = c.inst_aic_familias_texto;
            setSummary(aicCards[0], c.inst_aic_principios_texto, aicCards[0].querySelector("h3"));
          }
          if (aicCards[1] && c.inst_aic_marco_legal_texto) {
            var mlP = aicCards[1].querySelector("p");
            if (mlP) mlP.textContent = c.inst_aic_marco_legal_texto;
          }
          setSummary(aicCards[2], c.inst_aic_cap1_texto);
          setSummary(aicCards[3], c.inst_aic_cap2_texto);
          setSummary(aicCards[4], c.inst_aic_cap3_texto);
          setSummary(aicCards[5], c.inst_aic_cap4_texto);
          setSummary(aicCards[6], c.inst_aic_cap5_texto);
          setSummary(aicCards[7], c.inst_aic_cic_texto);

          // PDF oficial del AIC, si el panel cargó uno.
          var aicPdf = c.inst_aic_pdf_file || c.inst_aic_pdf_url;
          if (aicPdf) {
            var pdfHref = /^[a-z][a-z0-9+.-]*:/i.test(aicPdf) ? (isSafeUrl(aicPdf) ? aicPdf : "") : resolveImg(aicPdf);
            if (pdfHref) {
              var pdfLink = document.getElementById("aicPdfLink");
              if (!pdfLink) {
                pdfLink = document.createElement("a");
                pdfLink.id = "aicPdfLink";
                pdfLink.className = "btn btn-primary";
                pdfLink.target = "_blank";
                pdfLink.rel = "noopener noreferrer";
                pdfLink.textContent = "Descargar el AIC completo (PDF)";
                pdfLink.style.marginBottom = "20px";
                paneAIC.insertBefore(pdfLink, paneAIC.firstChild);
              }
              pdfLink.href = pdfHref;
            }
          }
        }
      }

      // ================= 4.8. HITOS HISTÓRICOS (PROYECTOS EMBLEMÁTICOS) =================
      if (c.hitos_tag) {
        var htTag = document.querySelector(".hitos-tag");
        if (htTag) {
          htTag.textContent = "";
          var tagLine = document.createElement("span");
          tagLine.className = "hitos-tag-line";
          htTag.appendChild(tagLine);
          htTag.appendChild(document.createTextNode(c.hitos_tag));
        }
      }
      if (c.hitos_titulo) {
        var htTitle = document.querySelector(".hitos-title");
        if (htTitle) htTitle.textContent = c.hitos_titulo;
      }
      if (c.hitos_descripcion) {
        var htSub = document.querySelector(".hitos-sub");
        if (htSub) htSub.textContent = c.hitos_descripcion;
      }

      var hitoNodes = Array.prototype.slice.call(document.querySelectorAll(".hito-v2"));
      if (hitoNodes.length > 0 && Array.isArray(data.hitos)) {
        var hitoTemplate = hitoNodes[hitoNodes.length - 1];
        var hitoParent = hitoTemplate.parentNode;

        data.hitos.forEach(function (hito, idx) {
          var isNew = idx >= hitoNodes.length;
          var el = hitoNodes[idx];
          if (isNew) {
            el = hitoTemplate.cloneNode(true);
            var panelId = "hito-panel-db-" + hito.id;
            el.id = "hito-db-" + hito.id;
            el.className = "hito-v2 reveal is-visible";
            var newRow = el.querySelector(".hito-v2-row");
            var newPanel = el.querySelector(".hito-v2-card");
            if (newRow) { newRow.setAttribute("aria-expanded", "false"); newRow.setAttribute("aria-controls", panelId); }
            if (newPanel) { newPanel.id = panelId; newPanel.hidden = true; }
            hitoParent.appendChild(el);
            if (window.EESTBindHito) window.EESTBindHito(el);
          }

          var yr = el.querySelector(".hito-v2-year");
          if (yr && hito.anio) {
            yr.textContent = "";
            String(hito.anio).split(/\s*[-–]\s*/).forEach(function (part, i) {
              if (i) yr.appendChild(document.createElement("br"));
              yr.appendChild(document.createTextNode(part));
            });
          }
          var rTit = el.querySelector(".hito-v2-row-title");
          if (rTit && (hito.titulo || isNew)) rTit.textContent = hito.titulo || "";
          var cTit = el.querySelector(".hito-v2-card-title");
          if (cTit && (hito.titulo || isNew)) cTit.textContent = hito.titulo || "";
          var rSum = el.querySelector(".hito-v2-row-summary");
          if (rSum && (hito.resumen || isNew)) rSum.textContent = hito.resumen || "";
          var cDesc = el.querySelector(".hito-v2-card-desc");
          if (cDesc && (hito.descripcion || isNew)) cDesc.textContent = hito.descripcion || "";

          var thumb = el.querySelector(".hito-v2-thumb");
          var cardFoto = el.querySelector(".hito-v2-card-foto");
          var cardImg = cardFoto ? cardFoto.querySelector("img") : null;
          if (hito.imagen) {
            var hitoImg = resolveImg(hito.imagen);
            if (thumb) { thumb.src = hitoImg; thumb.alt = hito.titulo || ""; }
            if (cardImg) { cardImg.src = hitoImg; cardImg.alt = hito.titulo || ""; }
          } else if (isNew) {
            if (thumb) thumb.remove();
            if (cardFoto) cardFoto.remove();
          }
          if (cardFoto && isNew) {
            var figCap = cardFoto.querySelector("figcaption");
            if (figCap) figCap.textContent = hito.titulo || "";
          }

          // Cada pastilla muestra su propio rótulo y su propio dato.
          var pillData = [
            ["ESPECIALIDAD", hito.especialidad],
            ["DESTINO", hito.destino],
            ["PREMIO", hito.premio]
          ];
          Array.prototype.forEach.call(el.querySelectorAll(".hito-v2-pill"), function (pill, i) {
            var item = pillData[i];
            if (!item) return;
            var label = pill.querySelector(".hito-v2-pill-label");
            var val = pill.querySelector(".hito-v2-pill-val");
            if (item[1]) {
              if (label) label.textContent = item[0];
              if (val) val.textContent = item[1];
              pill.hidden = false;
            } else if (isNew) {
              pill.hidden = true;
            }
          });

          var actA = el.querySelector(".btn-hito-detalles");
          if (actA) {
            if (hito.enlace && isSafeUrl(hito.enlace)) {
              actA.href = hito.enlace;
              actA.hidden = false;
            } else if (isNew) {
              actA.hidden = true;
              actA.removeAttribute("href");
            }
          }
        });

        hitoNodes.slice(data.hitos.length).forEach(function (el) { el.remove(); });
      }

      // ================= CATÁLOGO DE PROYECTOS (EXPOTEC) =================
      var proyGrid = document.getElementById("proyectosGrid");
      var proySection = document.getElementById("proyectosExpotec");
      if (proyGrid && proySection && Array.isArray(data.proyectos)) {
        proyGrid.textContent = "";
        data.proyectos.forEach(function (proy) {
          var card = document.createElement("article");
          card.className = "expo-card proyecto-card";

          if (proy.imagen) {
            var pImg = document.createElement("img");
            pImg.src = resolveImg(proy.imagen);
            pImg.alt = proy.titulo || "";
            pImg.loading = "lazy";
            pImg.addEventListener("error", function () { pImg.remove(); });
            card.appendChild(pImg);
          }

          var body = document.createElement("div");
          body.className = "expo-card-body";

          var meta = document.createElement("span");
          meta.className = "proyecto-meta";
          meta.textContent = [proy.especialidad_nombre, proy.anio].filter(Boolean).join(" · ");
          body.appendChild(meta);

          var pTitle = document.createElement("h3");
          pTitle.textContent = proy.titulo || "";
          body.appendChild(pTitle);

          if (proy.subtitulo) {
            var pSub = document.createElement("p");
            pSub.className = "proyecto-subtitulo";
            pSub.textContent = proy.subtitulo;
            body.appendChild(pSub);
          }

          var pDesc = document.createElement("p");
          pDesc.textContent = proy.descripcion || "";
          body.appendChild(pDesc);

          if (proy.estado) {
            var pEstado = document.createElement("span");
            pEstado.className = "proyecto-estado";
            pEstado.textContent = proy.estado;
            body.appendChild(pEstado);
          }

          card.appendChild(body);
          proyGrid.appendChild(card);
        });
        // Se publica solo cuando el panel lo habilita, despues de revisar los proyectos cargados.
        proySection.hidden = data.proyectos.length === 0 || c.expotec_catalogo_visible !== "1";
      }

      // ================= 5. CONTACTO Y FOOTER =================
      if (window.location.pathname.includes("contacto")) {
        if (c.contacto_hero_tag) {
          var heroTag = document.querySelector(".page-hero .section-tag");
          if (heroTag) heroTag.textContent = c.contacto_hero_tag;
        }
        if (c.contacto_hero_titulo) {
          var heroTitle = document.querySelector(".page-hero .page-title");
          if (heroTitle) heroTitle.textContent = c.contacto_hero_titulo;
        }
        if (c.contacto_seccion_titulo) {
          var secTitle = document.querySelector(".section.contacto .h2");
          if (secTitle) secTitle.textContent = c.contacto_seccion_titulo;
        }

        var cList = document.querySelector(".contacto-list");
        if (cList) {
          var items = cList.querySelectorAll("li");
          items.forEach(function (li) {
            var rot = li.querySelector(".rot");
            if (!rot) return;
            var label = rot.textContent.trim().toLowerCase();
            if (label.includes("direcci") && c.direccion) {
              var pDir = li.querySelectorAll("p")[1];
              if (pDir) setRichText(pDir, c.direccion);
            }
            if (label.includes("correo") && c.contacto_email) {
              var aMail = li.querySelector("a");
              if (aMail) {
                aMail.textContent = c.contacto_email;
                aMail.href = "mailto:" + encodeURIComponent(c.contacto_email).replace(/%40/g, "@");
              }
            }
            if (label.includes("atenci") && c.contacto_horarios) {
              var pAten = li.querySelectorAll("p")[1];
              if (pAten) setRichText(pAten, c.contacto_horarios);
            }
          });
        }

        if (c.contacto_nota) {
          var cNota = document.querySelector(".section.contacto .reveal > .nota");
          if (cNota) cNota.textContent = c.contacto_nota;
        }

        if (c.contacto_mapa_url) {
          var mapIframe = document.querySelector(".section.contacto .map iframe");
          if (mapIframe && /^https:\/\//i.test(c.contacto_mapa_url)) mapIframe.src = c.contacto_mapa_url;
        }
        if (c.contacto_mapa_nota) {
          var mapNota = document.querySelector(".section.contacto .map .nota");
          if (mapNota) mapNota.textContent = c.contacto_mapa_nota;
        }
      }

      // Footer institucional en todas las páginas
      var footInfo = document.querySelector(".footer-info");
      if (footInfo && (c.direccion || c.contacto_email)) {
        var dFirst = (c.direccion || "Juan B. Justo 4287").split("\n")[0].trim();
        var mFirst = c.contacto_email || "infot5@eest5mdp.edu.ar";
        footInfo.textContent = dFirst + " · " + mFirst;
      }

      if (c.direccion) {
        var addressEls = document.querySelectorAll(".footer-address, .contact-address");
        addressEls.forEach(function (el) { el.textContent = c.direccion; });
      }
      var telEls = document.querySelectorAll(".footer-tel, .contact-tel");
      telEls.forEach(function (el) { el.remove(); });
      if (c.contacto_email) {
        var mailEls = document.querySelectorAll(".footer-email, .contact-email");
        mailEls.forEach(function (el) { 
          el.textContent = c.contacto_email;
          if (el.tagName === 'A') el.href = 'mailto:' + c.contacto_email;
        });
        var emailIcons = document.querySelectorAll("a[aria-label='Email'], a[title='Correo Electrónico']");
        emailIcons.forEach(function (a) { a.href = 'mailto:' + c.contacto_email; });
      }
      if (c.instagram_handle) {
        var igHandles = document.querySelectorAll(".ig-badge-user");
        igHandles.forEach(function (el) { el.textContent = c.instagram_handle; });
      }
      if (c.instagram_url) {
        var igLinks = document.querySelectorAll("a[href*='instagram.com'], a[aria-label='Instagram']");
        if (isSafeUrl(c.instagram_url)) igLinks.forEach(function (el) { el.href = c.instagram_url; });
      }
      if (c.facebook_url) {
        var fbLinks = document.querySelectorAll("a[href*='facebook.com'], a[aria-label='Facebook']");
        if (isSafeUrl(c.facebook_url)) fbLinks.forEach(function (el) { el.href = c.facebook_url; });
      }
    })
    .catch(function (err) {
      console.log("Modo estático activo o sin conexión a backend PHP:", err);
      requestBehold();
    });
})();

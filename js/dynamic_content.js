/* Integración dinámica con MySQL / TiDB Cloud via API */
(function () {
  "use strict";

  var isSubdir = window.location.pathname.includes("/pages/");
  var apiEndpoint = (isSubdir ? "../api/get_public_data.php" : "api/get_public_data.php") + "?_t=" + Date.now();

  fetch(apiEndpoint, { cache: "no-store" })
    .then(function (res) {
      if (!res.ok) throw new Error("HTTP error " + res.status);
      return res.json();
    })
    .then(function (data) {
      if (!data || !data.success) return;

      var c = data.contenido || {};
      var imgPrefix = isSubdir ? "../img/" : "img/";

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
          heroTitle.innerHTML = c.hero_titulo.replace(/\n/g, "<br>");
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
        if (sloganEl) sloganEl.innerHTML = c.hero_slogan;
      }

      if (c.hero_subtitulo) {
        var heroSub = document.querySelector(".hero-sub");
        if (heroSub) heroSub.textContent = c.hero_subtitulo;
        var heroLead = document.querySelector(".hero-lead");
        if (heroLead) heroLead.textContent = c.hero_subtitulo;
      }

      // Footer general info
      var fInfo = document.querySelector(".footer-info");
      if (fInfo) {
        var dir = c.direccion || "Juan B. Justo 4287, Mar del Plata";
        var mail = c.contacto_email || "infoT5@eest5mdp.edu.ar";
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
          if (fCap) fCap.innerHTML = '<span class="dot" aria-hidden="true"></span>' + c.escuela_foto_caption;
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
          // Secretaría
          var secItems = deptCards[2].querySelectorAll(".auth-person-name");
          if (secItems.length >= 2) {
            if (c.escuela_sec_tm) secItems[0].textContent = c.escuela_sec_tm;
            if (c.escuela_sec_tt) secItems[1].textContent = c.escuela_sec_tt;
          }
        }

        // Marco Normativo & QRs
        if (c.escuela_qr_ra_url) {
          var raLinks = document.querySelectorAll("a[href*='seccion=regimen-academico']");
          raLinks.forEach(function (a) { a.href = c.escuela_qr_ra_url; });
        }
        if (c.escuela_qr_aic_url) {
          var aicLinks = document.querySelectorAll("a[href*='seccion=acuerdo-institucional']");
          aicLinks.forEach(function (a) { a.href = c.escuela_qr_aic_url; });
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
      if (c.behold_feed_url && typeof window.loadBeholdFeed === "function") {
        window.loadBeholdFeed(c.behold_feed_url);
      }

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
          if (expLead) expLead.innerHTML = c.expotec_desc;
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

        if (data.especialidades && data.especialidades.length > 0) {
          var espCards = espSec.querySelectorAll(".esp-list .esp");
          if (espCards.length > 0) {
            data.especialidades.forEach(function (esp, idx) {
              if (idx < espCards.length) {
                var card = espCards[idx];
                if (esp.titulo) {
                  var h3 = card.querySelector(".esp-body h3");
                  if (h3) h3.textContent = sanitizeEncoding(esp.titulo);
                }
                if (esp.nombre) {
                  var tit = card.querySelector(".esp-titulo");
                  if (tit) tit.textContent = sanitizeEncoding(esp.nombre);
                }
                if (esp.descripcion) {
                  var desc = card.querySelector(".esp-desc");
                  if (desc) desc.textContent = sanitizeEncoding(esp.descripcion);
                }
                if (esp.imagen) {
                  var img = card.querySelector(".esp-foto img");
                  if (img) img.src = resolveImg(esp.imagen);
                }
                if (esp.numero) {
                  var num = card.querySelector(".esp-num");
                  if (num) num.textContent = esp.numero;
                }
              }
            });
          }
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
          } else if (c.inst_ra_video_url) {
            if (raSource) raSource.src = c.inst_ra_video_url;
            if (raVid) raVid.load();
          }

          // Pilares
          var raH3s = paneRA.querySelectorAll("article h3");
          var raPs = paneRA.querySelectorAll("article p");
          if (raH3s[0] && c.inst_ra_p1_titulo) raH3s[0].textContent = c.inst_ra_p1_titulo;
          if (c.inst_ra_p1_desc && raPs[2]) raPs[2].textContent = c.inst_ra_p1_desc;

          if (raH3s[1] && c.inst_ra_p2_titulo) raH3s[1].textContent = c.inst_ra_p2_titulo;
          if (c.inst_ra_p2_desc && raPs[3]) raPs[3].textContent = c.inst_ra_p2_desc;

          if (raH3s[2] && c.inst_ra_p3_titulo) raH3s[2].textContent = c.inst_ra_p3_titulo;
          if (c.inst_ra_p3_desc && raPs[4]) raPs[4].textContent = c.inst_ra_p3_desc;

          if (raH3s[3] && c.inst_ra_p4_titulo) raH3s[3].textContent = c.inst_ra_p4_titulo;
          if (c.inst_ra_p4_desc && raPs[5]) raPs[5].textContent = c.inst_ra_p4_desc;

          if (raH3s[4] && c.inst_ra_p5_titulo) raH3s[4].textContent = c.inst_ra_p5_titulo;
          if (c.inst_ra_p5_desc && raPs[6]) raPs[6].textContent = c.inst_ra_p5_desc;
        }

        // Pestaña AIC
        var paneAIC = document.getElementById("paneAIC");
        if (paneAIC) {
          var aicCards = paneAIC.querySelectorAll(".inst-card");
          if (aicCards[0]) {
            if (c.inst_aic_pres_titulo) {
              var aicH2 = aicCards[0].querySelector("h2");
              if (aicH2) aicH2.textContent = c.inst_aic_pres_titulo;
            }
            var presPs = aicCards[0].querySelectorAll("p");
            if (c.inst_aic_pres_texto && presPs[0]) presPs[0].textContent = c.inst_aic_pres_texto;
            if (c.inst_aic_familias_texto && presPs[2]) presPs[2].textContent = c.inst_aic_familias_texto;
          }
          if (aicCards[1] && c.inst_aic_marco_legal_texto) {
            var mlP = aicCards[1].querySelector("p");
            if (mlP) mlP.textContent = c.inst_aic_marco_legal_texto;
          }
          if (aicCards[2] && c.inst_aic_cap1_texto) {
            var c1P = aicCards[2].querySelector("p:last-of-type");
            if (c1P) c1P.textContent = c.inst_aic_cap1_texto;
          }
          if (aicCards[3] && c.inst_aic_cap2_texto) {
            var c2P = aicCards[3].querySelector("p:last-of-type");
            if (c2P) c2P.textContent = c.inst_aic_cap2_texto;
          }
          if (aicCards[4] && c.inst_aic_cap3_texto) {
            var c3P = aicCards[4].querySelector("p:last-of-type");
            if (c3P) c3P.textContent = c.inst_aic_cap3_texto;
          }
          if (aicCards[5] && c.inst_aic_cap4_texto) {
            var c4P = aicCards[5].querySelector("p:last-of-type");
            if (c4P) c4P.textContent = c.inst_aic_cap4_texto;
          }
          if (aicCards[6] && c.inst_aic_cap5_texto) {
            var c5P = aicCards[6].querySelector("p:last-of-type");
            if (c5P) c5P.textContent = c.inst_aic_cap5_texto;
          }
          if (aicCards[7] && c.inst_aic_cic_texto) {
            var cicP = aicCards[7].querySelector("p:last-of-type");
            if (cicP) cicP.textContent = c.inst_aic_cic_texto;
          }
        }
      }

      // ================= 4.8. HITOS HISTÓRICOS (PROYECTOS EMBLEMÁTICOS) =================
      if (c.hitos_tag) {
        var htTag = document.querySelector(".hitos-tag");
        if (htTag) htTag.innerHTML = '<span class="hitos-tag-line"></span>' + c.hitos_tag;
      }
      if (c.hitos_titulo) {
        var htTitle = document.querySelector(".hitos-title");
        if (htTitle) htTitle.textContent = c.hitos_titulo;
      }
      if (c.hitos_descripcion) {
        var htSub = document.querySelector(".hitos-sub");
        if (htSub) htSub.textContent = c.hitos_descripcion;
      }

      if (data.hitos && data.hitos.length > 0) {
        var hitoArticles = document.querySelectorAll(".hito-v2");
        data.hitos.forEach(function (hito, idx) {
          if (idx < hitoArticles.length) {
            var el = hitoArticles[idx];
            if (hito.anio) {
              var yr = el.querySelector(".hito-v2-year");
              if (yr) yr.innerHTML = hito.anio.replace(/\s*[-–]\s*/g, "<br />");
            }
            if (hito.titulo) {
              var rTit = el.querySelector(".hito-v2-row-title");
              if (rTit) rTit.textContent = hito.titulo;
              var cTit = el.querySelector(".hito-v2-card-title");
              if (cTit) cTit.textContent = hito.titulo;
            }
            if (hito.resumen) {
              var rSum = el.querySelector(".hito-v2-row-summary");
              if (rSum) rSum.textContent = hito.resumen;
            }
            if (hito.descripcion) {
              var cDesc = el.querySelector(".hito-v2-card-desc");
              if (cDesc) cDesc.textContent = hito.descripcion;
            }
            if (hito.imagen) {
              var thumb = el.querySelector(".hito-v2-thumb");
              if (thumb) thumb.src = resolveImg(hito.imagen);
              var cardImg = el.querySelector(".hito-v2-card-foto img");
              if (cardImg) cardImg.src = resolveImg(hito.imagen);
            }
            if (hito.especialidad) {
              var espVal = el.querySelector(".hito-v2-pill-val");
              if (espVal) espVal.textContent = hito.especialidad;
            }
            if (hito.enlace) {
              var actA = el.querySelector(".btn-hito-detalles");
              if (actA) actA.href = hito.enlace;
            }
          }
        });
      }

      // ================= 5. CONTACTO Y FOOTER =================
      if (window.location.pathname.includes("contacto")) {
        var cList = document.querySelector(".contacto-list");
        if (cList) {
          var items = cList.querySelectorAll("li");
          items.forEach(function (li) {
            var rot = li.querySelector(".rot");
            if (!rot) return;
            var label = rot.textContent.trim().toLowerCase();
            if (label.includes("direcci") && c.direccion) {
              var pDir = li.querySelectorAll("p")[1];
              if (pDir) pDir.innerHTML = c.direccion.replace(/\n/g, "<br>");
            }
            if (label.includes("correo") && c.contacto_email) {
              var aMail = li.querySelector("a");
              if (aMail) {
                aMail.textContent = c.contacto_email;
                aMail.href = "mailto:" + c.contacto_email;
              }
            }
          });
        }
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
      }
      if (c.instagram_handle) {
        var igHandles = document.querySelectorAll(".ig-badge-user");
        igHandles.forEach(function (el) { el.textContent = c.instagram_handle; });
      }
      if (c.instagram_url) {
        var igLinks = document.querySelectorAll("a[href*='instagram.com']");
        igLinks.forEach(function (el) { el.href = c.instagram_url; });
      }
    })
    .catch(function (err) {
      console.log("Modo estático activo o sin conexión a backend PHP:", err);
    });
})();

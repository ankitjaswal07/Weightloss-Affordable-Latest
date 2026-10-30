(function () {
  'use strict';

  var CHECK_SVG =
    '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l4 4L19 6" /></svg>';

  var STAGE_STOPS = [0.13, 0.38, 0.63, 0.9];
  var dataStore = window.WLA_SITE_DATA || { enToEs: {} };
  var cfg = window.WLA_CONFIG || {};

  var enToEs = Object.assign(
    {},
    dataStore.enToEs || {},
    cfg.cachedPairs || {},
    cfg.customOverrides || {}
  );
  var esToEn = {};
  Object.keys(enToEs).forEach(function (enKey) {
    esToEn[enToEs[enKey]] = enKey;
  });

  function readSavedLang() {
    try {
      var params = new URLSearchParams(window.location.search);
      var p = params.get('lang');
      if (p === 'en' || p === 'es') return p;
      var saved = window.localStorage.getItem('wla_lang');
      if (saved === 'en' || saved === 'es') return saved;
    } catch (e) {}
    return cfg.defaultLang === 'es' ? 'es' : 'en';
  }

  var currentLang = readSavedLang();

  function tr(text) {
    if (!text || typeof text !== 'string') return text;
    var trimmed = text.trim();
    if (!trimmed) return text;
    if (currentLang === 'es') {
      if (enToEs[trimmed]) return text.replace(trimmed, enToEs[trimmed]);
    } else if (esToEn[trimmed]) {
      return text.replace(trimmed, esToEn[trimmed]);
    }
    return text;
  }

  function showToast(msg) {
    var toast = document.querySelector('[data-wla-toast]');
    var textEl = document.querySelector('[data-wla-toast-text]');
    if (!toast || !textEl) return;
    textEl.textContent = msg;
    toast.hidden = false;
    clearTimeout(toast.__timer);
    toast.__timer = setTimeout(function () {
      toast.hidden = true;
    }, 2600);
  }

  function translateDOM(showNotification) {
    document.documentElement.lang = currentLang;
    document.documentElement.dataset.translating = 'true';
    setTimeout(function () {
      delete document.documentElement.dataset.translating;
    }, 220);

    document.querySelectorAll('[data-set-lang]').forEach(function (btn) {
      var isMatch = btn.getAttribute('data-set-lang') === currentLang;
      btn.classList.toggle('is-active', isMatch);
      btn.classList.toggle('is-current-lang', isMatch);
      btn.setAttribute('aria-pressed', String(isMatch));
    });

    var missingEn = [];
    var walker = document.createTreeWalker(
      document.body,
      NodeFilter.SHOW_TEXT,
      {
        acceptNode: function (node) {
          var parent = node.parentElement;
          if (!parent) return NodeFilter.FILTER_REJECT;
          var tag = parent.tagName;
          if (
            tag === 'SCRIPT' ||
            tag === 'STYLE' ||
            tag === 'NOSCRIPT' ||
            tag === 'TEXTAREA' ||
            parent.hasAttribute('data-no-translate')
          ) {
            return NodeFilter.FILTER_REJECT;
          }
          if (!node.nodeValue || !node.nodeValue.trim()) {
            return NodeFilter.FILTER_REJECT;
          }
          return NodeFilter.FILTER_ACCEPT;
        },
      }
    );

    var textNodes = [];
    while (walker.nextNode()) {
      textNodes.push(walker.currentNode);
    }

    textNodes.forEach(function (node) {
      if (typeof node.__wlaEn !== 'string') {
        var raw = node.nodeValue;
        var t = raw.trim();
        node.__wlaLead = raw.slice(0, raw.indexOf(t));
        node.__wlaTrail = raw.slice(raw.indexOf(t) + t.length);
        node.__wlaEn = esToEn[t] || t;
      }
      var targetStr =
        currentLang === 'es' ? enToEs[node.__wlaEn] : node.__wlaEn;
      if (targetStr) {
        node.nodeValue = node.__wlaLead + targetStr + node.__wlaTrail;
      } else if (
        currentLang === 'es' &&
        node.__wlaEn.length > 2 &&
        /[a-zA-Z]/.test(node.__wlaEn)
      ) {
        missingEn.push(node.__wlaEn);
      }
    });

    document.querySelectorAll('[placeholder]').forEach(function (el) {
      if (!el.__wlaPhEn) {
        el.__wlaPhEn = el.getAttribute('placeholder');
      }
      el.setAttribute(
        'placeholder',
        currentLang === 'es'
          ? enToEs[el.__wlaPhEn] || el.__wlaPhEn
          : el.__wlaPhEn
      );
    });

    document.dispatchEvent(
      new CustomEvent('wla:langchange', { detail: { lang: currentLang } })
    );

    if (showNotification) {
      showToast(
        currentLang === 'es'
          ? 'Todo el sitio se tradujo al español al instante'
          : 'Translated entire website to English'
      );
    }

    if (currentLang === 'es' && missingEn.length && cfg.ajaxUrl) {
      var uniqueMissing = Array.from(new Set(missingEn)).slice(0, 40);
      var fd = new FormData();
      fd.append('action', 'wla_translate_batch');
      fd.append('nonce', cfg.nonce || '');
      fd.append('target', 'es');
      fd.append('texts', JSON.stringify(uniqueMissing));
      fetch(cfg.ajaxUrl, { method: 'POST', body: fd })
        .then(function (r) {
          return r.json();
        })
        .then(function (res) {
          if (res && res.success && res.data && res.data.translations) {
            var added = false;
            Object.keys(res.data.translations).forEach(function (k) {
              if (res.data.translations[k] && res.data.translations[k] !== k) {
                enToEs[k] = res.data.translations[k];
                esToEn[res.data.translations[k]] = k;
                added = true;
              }
            });
            if (added && currentLang === 'es') translateDOM(false);
          }
        })
        .catch(function () {});
    }
  }

  function setLanguage(lang) {
    if (lang !== 'en' && lang !== 'es') return;
    currentLang = lang;
    try {
      window.localStorage.setItem('wla_lang', lang);
      document.cookie =
        'wla_lang=' + lang + '; path=/; max-age=31536000; SameSite=Lax';
    } catch (e) {}
    translateDOM(true);
  }

  function initHeaderAndMenus() {
    var header = document.querySelector('[data-wla-header]');
    var menuBtn = document.querySelector('[data-wla-menu-toggle]');
    var nav = document.querySelector('[data-wla-nav]');
    var dropWrap = document.querySelector('[data-wla-dropdown]');
    var dropBtn = document.querySelector('[data-wla-dropdown-btn]');
    var dropPanel = document.querySelector('[data-wla-dropdown-panel]');

    if (menuBtn && nav) {
      menuBtn.addEventListener('click', function () {
        var open = menuBtn.getAttribute('aria-expanded') === 'true';
        menuBtn.setAttribute('aria-expanded', String(!open));
        nav.classList.toggle('is-open', !open);
      });
      nav.addEventListener('click', function (e) {
        if (e.target.closest('a') && window.innerWidth <= 900) {
          menuBtn.setAttribute('aria-expanded', 'false');
          nav.classList.remove('is-open');
        }
      });
    }

    if (dropBtn && dropPanel) {
      dropBtn.addEventListener('click', function () {
        var open = !dropPanel.hidden;
        dropPanel.hidden = open;
        dropBtn.classList.toggle('is-open', !open);
        dropBtn.setAttribute('aria-expanded', String(!open));
      });
      document.addEventListener('mousedown', function (e) {
        if (dropWrap && !dropWrap.contains(e.target)) {
          dropPanel.hidden = true;
          dropBtn.classList.remove('is-open');
          dropBtn.setAttribute('aria-expanded', 'false');
        }
      });
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        if (dropPanel) {
          dropPanel.hidden = true;
          dropBtn.classList.remove('is-open');
        }
        if (nav && nav.classList.contains('is-open')) {
          nav.classList.remove('is-open');
          if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
        }
      }
    });

    document.addEventListener('click', function (e) {
      var langBtn = e.target.closest('[data-set-lang]');
      if (langBtn) {
        e.preventDefault();
        setLanguage(langBtn.getAttribute('data-set-lang'));
      }
    });

    if (header) {
      var onScrollHeader = function () {
        header.classList.toggle('is-scrolled', window.scrollY > 24);
      };
      onScrollHeader();
      window.addEventListener('scroll', onScrollHeader, { passive: true });
    }
  }

  function initScrollAndTilt() {
    var reveals = Array.from(document.querySelectorAll('[data-reveal]'));
    if (!('IntersectionObserver' in window)) {
      reveals.forEach(function (el) {
        el.classList.add('is-visible');
      });
    } else {
      var obs = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              entry.target.classList.add('is-visible');
              obs.unobserve(entry.target);
            }
          });
        },
        { rootMargin: '0px 0px -6% 0px', threshold: 0.08 }
      );
      reveals.forEach(function (el) {
        obs.observe(el);
      });

      var secObs = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting && entry.target.id) {
              var id = '#' + entry.target.id;
              document
                .querySelectorAll('.main-nav > a[href^="#"]')
                .forEach(function (a) {
                  a.classList.toggle('is-active', a.getAttribute('href') === id);
                });
            }
          });
        },
        { rootMargin: '-32% 0px -56% 0px', threshold: 0 }
      );
      document.querySelectorAll('main section[id]').forEach(function (sec) {
        secObs.observe(sec);
      });
    }

    var parallaxEls = Array.from(document.querySelectorAll('[data-parallax]'));
    var raf = 0;
    var updateScroll = function () {
      var top = window.scrollY;
      var max = Math.max(
        1,
        document.documentElement.scrollHeight - window.innerHeight
      );
      document.documentElement.style.setProperty(
        '--page-progress',
        Math.min(1, top / max).toFixed(4)
      );
      parallaxEls.forEach(function (el) {
        var r = el.getBoundingClientRect();
        if (r.bottom < -100 || r.top > window.innerHeight + 100) return;
        var speed = parseFloat(el.getAttribute('data-parallax') || '0.02');
        var offset = r.top + r.height / 2 - window.innerHeight / 2;
        el.style.setProperty('--parallax-y', (-offset * speed).toFixed(1) + 'px');
      });
      raf = 0;
    };
    updateScroll();
    window.addEventListener(
      'scroll',
      function () {
        if (!raf) raf = window.requestAnimationFrame(updateScroll);
      },
      { passive: true }
    );

    if (
      window.matchMedia('(hover: hover) and (pointer: fine)').matches &&
      !window.matchMedia('(prefers-reduced-motion: reduce)').matches
    ) {
      document.querySelectorAll('[data-tilt]').forEach(function (card) {
        card.addEventListener(
          'pointermove',
          function (e) {
            var r = card.getBoundingClientRect();
            var x = (e.clientX - r.left) / r.width;
            var y = (e.clientY - r.top) / r.height;
            card.style.setProperty('--spot-x', (x * 100).toFixed(1) + '%');
            card.style.setProperty('--spot-y', (y * 100).toFixed(1) + '%');
            card.style.setProperty('--tilt-x', ((0.5 - y) * 5).toFixed(2) + 'deg');
            card.style.setProperty('--tilt-y', ((x - 0.5) * 5).toFixed(2) + 'deg');
          },
          { passive: true }
        );
        card.addEventListener('pointerleave', function () {
          card.style.setProperty('--tilt-x', '0deg');
          card.style.setProperty('--tilt-y', '0deg');
        });
      });
    }
  }

  function initJourneys() {
    document.querySelectorAll('[data-wla-journey]').forEach(function (section) {
      var steps = Array.from(section.querySelectorAll('.journey-step'));
      var counter = section.querySelector('[data-journey-counter]');
      var bar = section.querySelector('[data-journey-bar]');
      if (!steps.length) return;

      var active = 0;
      var paused = false;
      var inView = false;

      var render = function (idx) {
        active = ((idx % steps.length) + steps.length) % steps.length;
        var ratio = STAGE_STOPS[active] || 0.25;
        section.style.setProperty('--journey-progress', ratio.toFixed(3));
        steps.forEach(function (li, i) {
          var isAct = i === active;
          var isComp = i < active;
          li.classList.toggle('is-active', isAct);
          li.classList.toggle('is-complete', isComp);
          var btn = li.querySelector('.journey-number-button');
          var num = li.getAttribute('data-step-num') || '0' + (i + 1);
          if (btn) {
            btn.setAttribute('aria-pressed', String(isAct));
            btn.innerHTML = isComp
              ? CHECK_SVG
              : '<span class="step-num-text">' + num + '</span>';
          }
        });
        if (counter) {
          counter.textContent = '0' + (active + 1) + ' / 0' + steps.length;
        }
        if (bar) {
          bar.style.transform = 'scaleX(' + (active + 1) / steps.length + ')';
        }
      };

      steps.forEach(function (li, i) {
        li.addEventListener('mouseenter', function () {
          paused = true;
          render(i);
        });
        li.addEventListener('click', function () {
          paused = true;
          render(i);
        });
        // Keyboard parity: tabbing to the visible number button previews the
        // step exactly like hovering the card does with a mouse.
        var innerBtn = li.querySelector('.journey-number-button');
        if (innerBtn) {
          innerBtn.addEventListener('focus', function () {
            paused = true;
            render(i);
          });
        }
      });

      section.addEventListener('mouseleave', function () {
        paused = false;
      });
      section.addEventListener('focusout', function (e) {
        if (!section.contains(e.relatedTarget)) {
          paused = false;
        }
      });

      if ('IntersectionObserver' in window) {
        new IntersectionObserver(
          function (entries) {
            entries.forEach(function (entry) {
              inView = entry.isIntersecting;
            });
          },
          { threshold: 0.2 }
        ).observe(section);
      } else {
        inView = true;
      }

      setInterval(function () {
        if (
          inView &&
          !paused &&
          !document.hidden &&
          !window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
          render(active + 1);
        }
      }, 2800);
    });
  }

  function initExplorersAndModal() {
    var modal = document.querySelector('[data-wla-guide-modal]');
    var modalImg = modal && modal.querySelector('[data-modal-img]');
    var modalCat = modal && modal.querySelector('[data-modal-cat]');
    var modalRead = modal && modal.querySelector('[data-modal-read]');
    var modalTitle = modal && modal.querySelector('[data-modal-title]');
    var modalPrompt = modal && modal.querySelector('[data-modal-prompt]');
    var modalParas = modal && modal.querySelector('[data-modal-paragraphs]');
    var modalQs = modal && modal.querySelector('[data-modal-questions]');
    var modalDisc = modal && modal.querySelector('[data-modal-disclaimer]');
    var modalAsk = modal && modal.querySelector('[data-modal-ask]');
    var currentModalTopic = 'general';

    var closeModal = function () {
      if (!modal) return;
      modal.hidden = true;
      document.body.style.overflow = '';
    };

    var openModal = function (guide) {
      if (!modal || !guide) return;
      currentModalTopic = guide.topicValue || 'general';
      if (modalImg) {
        modalImg.src = guide.resolvedImage || guide.image || '';
        modalImg.alt = guide.alt || '';
      }
      if (modalCat) modalCat.textContent = tr(guide.category || '');
      if (modalRead) modalRead.textContent = tr(guide.readTime || '');
      if (modalTitle) modalTitle.textContent = tr(guide.title || '');
      if (modalPrompt) modalPrompt.textContent = tr(guide.prompt || '');
      if (modalParas) {
        modalParas.innerHTML = '';
        (guide.paragraphs || [guide.text || '']).forEach(function (p) {
          var pEl = document.createElement('p');
          pEl.textContent = tr(p);
          modalParas.appendChild(pEl);
        });
      }
      if (modalQs) {
        modalQs.innerHTML = '';
        (guide.questions || []).forEach(function (q, qi) {
          var li = document.createElement('li');
          li.innerHTML =
            '<span>0' + (qi + 1) + '</span><span>' + tr(q) + '</span>';
          modalQs.appendChild(li);
        });
      }
      if (modalDisc) modalDisc.textContent = tr(guide.disclaimer || '');
      modal.hidden = false;
      document.body.style.overflow = 'hidden';
    };

    if (modal) {
      modal.addEventListener('click', function (e) {
        if (e.target === modal || e.target.closest('[data-close-modal]')) {
          closeModal();
        }
      });
      if (modalAsk) {
        modalAsk.addEventListener('click', function () {
          closeModal();
          var sel = document.querySelector('[data-wla-topic-select]');
          if (sel) sel.value = currentModalTopic;
          var target = document.getElementById('contact');
          if (target) target.scrollIntoView({ behavior: 'smooth' });
        });
      }
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hidden) closeModal();
      });
    }

    document.querySelectorAll('[data-wla-explorer]').forEach(function (section) {
      var raw = section.getAttribute('data-guides');
      var guides = [];
      try {
        guides = JSON.parse(raw || '[]');
      } catch (e) {}
      if (!guides.length) return;

      var activeIdx = 0;
      var hovered = false;
      var autoplay = true;
      var inView = false;

      var imgEl = section.querySelector('[data-stage-img]');
      var idxEl = section.querySelector('[data-stage-idx]');
      var wmEl = section.querySelector('[data-stage-watermark]');
      var catEl = section.querySelector('[data-stage-category]');
      var rtEl = section.querySelector('[data-stage-readtime]');
      var prEl = section.querySelector('[data-stage-prompt]');
      var tiEl = section.querySelector('[data-stage-title]');
      var txEl = section.querySelector('[data-stage-text]');
      var qList = section.querySelector('[data-stage-questions]');
      var playlistBtns = Array.from(
        section.querySelectorAll('[data-playlist-idx]')
      );
      var toggleBtn = section.querySelector('[data-explorer-toggle]');
      var toggleLabel = section.querySelector('[data-explorer-toggle-label]');

      // Restart the stage's CSS entrance keyframes (fade/scale-in on the photo,
      // headline, watermark number, and question card) every time the guide
      // changes, so switching via the playlist tabs or prev/next arrows feels
      // alive instead of an instant content swap.
      var replayEntranceAnimations = function () {
        var animatedTargets = Array.prototype.slice.call(
          section.querySelectorAll(
            '.resource-slide, .resource-stage-image, .resource-stage-number, .resource-stage-copy > *, .resource-question-card'
          )
        );
        animatedTargets.forEach(function (el) {
          el.style.animation = 'none';
        });
        // Force a reflow so the browser registers "animation: none" before we
        // clear the inline override below — without this the animation would
        // not restart from its first frame.
        void section.offsetWidth;
        animatedTargets.forEach(function (el) {
          el.style.animation = '';
        });
      };

      var renderGuide = function (next) {
        activeIdx = ((next % guides.length) + guides.length) % guides.length;
        var g = guides[activeIdx];
        if (imgEl) {
          imgEl.src = g.resolvedImage || g.image || '';
          imgEl.alt = g.alt || '';
        }
        if (idxEl) idxEl.textContent = '0' + (activeIdx + 1);
        if (wmEl) wmEl.textContent = '0' + (activeIdx + 1);
        if (catEl) catEl.textContent = tr(g.category || '');
        if (rtEl) rtEl.textContent = tr(g.readTime || '');
        if (prEl) prEl.textContent = tr(g.prompt || '');
        if (tiEl) tiEl.textContent = tr(g.title || '');
        if (txEl) txEl.textContent = tr(g.text || '');
        if (qList) {
          qList.innerHTML = '';
          (g.questions || []).forEach(function (q, qi) {
            var li = document.createElement('li');
            li.innerHTML =
              '<span>0' + (qi + 1) + '</span><span>' + tr(q) + '</span>';
            qList.appendChild(li);
          });
        }
        playlistBtns.forEach(function (btn, bi) {
          var isAct = bi === activeIdx;
          btn.classList.toggle('is-active', isAct);
          btn.setAttribute('aria-pressed', String(isAct));
          btn.setAttribute('aria-current', isAct ? 'true' : 'false');
          var prog = btn.querySelector('.resource-playlist-progress');
          if (prog) {
            prog.innerHTML = isAct ? '<i></i>' : '';
          }
        });
        replayEntranceAnimations();
      };

      playlistBtns.forEach(function (btn, bi) {
        btn.addEventListener('click', function () {
          renderGuide(bi);
        });
      });

      var prevBtn = section.querySelector('[data-explorer-prev]');
      var nextBtn = section.querySelector('[data-explorer-next]');
      var readBtn = section.querySelector('[data-open-guide-modal]');
      var askBtn = section.querySelector('[data-ask-guide-topic]');

      if (prevBtn) {
        prevBtn.addEventListener('click', function () {
          renderGuide(activeIdx - 1);
        });
      }
      if (nextBtn) {
        nextBtn.addEventListener('click', function () {
          renderGuide(activeIdx + 1);
        });
      }
      if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
          autoplay = !autoplay;
          section.classList.toggle('is-paused', hovered || !autoplay);
          toggleBtn.setAttribute('aria-pressed', String(!autoplay));
          if (toggleLabel) {
            toggleLabel.textContent = autoplay ? tr('Pause') : tr('Play');
          }
        });
      }
      if (readBtn) {
        readBtn.addEventListener('click', function () {
          openModal(guides[activeIdx]);
        });
      }
      if (askBtn) {
        askBtn.addEventListener('click', function () {
          var g = guides[activeIdx];
          var sel = document.querySelector('[data-wla-topic-select]');
          if (sel && g.topicValue) sel.value = g.topicValue;
          var c = document.getElementById('contact');
          if (c) c.scrollIntoView({ behavior: 'smooth' });
        });
      }

      section.addEventListener('mouseenter', function () {
        hovered = true;
        section.classList.add('is-paused');
      });
      section.addEventListener('mouseleave', function () {
        hovered = false;
        section.classList.toggle('is-paused', !autoplay);
      });

      document.addEventListener('wla:langchange', function () {
        renderGuide(activeIdx);
      });

      if ('IntersectionObserver' in window) {
        new IntersectionObserver(
          function (entries) {
            entries.forEach(function (entry) {
              inView = entry.isIntersecting;
            });
          },
          { threshold: 0.2 }
        ).observe(section);
      } else {
        inView = true;
      }

      setInterval(function () {
        if (
          inView &&
          autoplay &&
          !hovered &&
          !document.hidden &&
          !window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
          renderGuide(activeIdx + 1);
        }
      }, 5200);
    });
  }

  function initFaqAccordions() {
    document.querySelectorAll('[data-wla-faq]').forEach(function (sec) {
      var items = Array.from(sec.querySelectorAll('[data-faq-item]'));
      items.forEach(function (item) {
        var btn = item.querySelector('button');
        var ans = item.querySelector('.faq-answer');
        var tog = item.querySelector('.faq-toggle');
        if (!btn || !ans) return;
        btn.addEventListener('click', function () {
          var willOpen = !item.classList.contains('is-open');
          items.forEach(function (other) {
            other.classList.remove('is-open');
            var b = other.querySelector('button');
            var a = other.querySelector('.faq-answer');
            var t = other.querySelector('.faq-toggle');
            if (b) b.setAttribute('aria-expanded', 'false');
            if (a) a.hidden = true;
            if (t) t.textContent = '+';
          });
          if (willOpen) {
            item.classList.add('is-open');
            btn.setAttribute('aria-expanded', 'true');
            ans.hidden = false;
            if (tog) tog.textContent = '−';
          }
        });
      });
    });
  }

  function initContactForms() {
    document.querySelectorAll('[data-wla-contact-form]').forEach(function (form) {
      if (!cfg.ajaxUrl) return;
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = form.querySelector('button[type="submit"]');
        var label = form.querySelector('[data-submit-label]');
        var okEl = form.querySelector('[data-form-success]');
        var errEl = form.querySelector('[data-form-error]');
        var origText = label ? label.textContent : '';
        if (okEl) okEl.hidden = true;
        if (errEl) errEl.hidden = true;
        if (btn) btn.disabled = true;
        if (label) label.textContent = tr('Sending inquiry…');

        var fd = new FormData(form);
        fd.set('action', 'wla_ajax_general_contact');
        fetch(cfg.ajaxUrl, { method: 'POST', body: fd })
          .then(function (r) {
            return r.json();
          })
          .then(function (res) {
            if (res && res.success) {
              form.reset();
              if (okEl) okEl.hidden = false;
            } else if (errEl) {
              errEl.hidden = false;
            }
          })
          .catch(function () {
            if (errEl) errEl.hidden = false;
          })
          .finally(function () {
            if (btn) btn.disabled = false;
            if (label) label.textContent = origText;
          });
      });
    });
  }

  function boot() {
    initHeaderAndMenus();
    initScrollAndTilt();
    initJourneys();
    initExplorersAndModal();
    initFaqAccordions();
    initContactForms();
    if (currentLang === 'es') {
      translateDOM(false);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();

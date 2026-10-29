/**
 * WeightLossAffordable front-end behaviour.
 *
 * No build step, no dependencies. Everything degrades gracefully: sections
 * render server-side, forms post normally without JavaScript, and motion is
 * disabled when the visitor prefers reduced motion.
 */
(function () {
  'use strict';

  var CHECK_SVG =
    '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l4 4L19 6" /></svg>';

  var STAGE_STOPS = [0.13, 0.38, 0.63, 0.9];
  var cfg = window.WLA_CONFIG || {};

  /** Placeholder for text normalisation (single-language build). */
  function text(value) {
    return value;
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

    var closeModal = function () {
      if (!modal) return;
      modal.hidden = true;
      document.body.style.overflow = '';
    };

    var openModal = function (guide) {
      if (!modal || !guide) return;
      if (modalImg) {
        modalImg.src = guide.resolvedImage || guide.image || '';
        modalImg.alt = guide.alt || '';
      }
      if (modalCat) modalCat.textContent = text(guide.category || '');
      if (modalRead) modalRead.textContent = text(guide.readTime || '');
      if (modalTitle) modalTitle.textContent = text(guide.title || '');
      if (modalPrompt) modalPrompt.textContent = text(guide.prompt || '');
      if (modalParas) {
        modalParas.innerHTML = '';
        (guide.paragraphs || [guide.text || '']).forEach(function (p) {
          var pEl = document.createElement('p');
          pEl.textContent = text(p);
          modalParas.appendChild(pEl);
        });
      }
      if (modalQs) {
        modalQs.innerHTML = '';
        (guide.questions || []).forEach(function (q, qi) {
          var li = document.createElement('li');
          li.innerHTML =
            '<span>0' + (qi + 1) + '</span><span>' + text(q) + '</span>';
          modalQs.appendChild(li);
        });
      }
      if (modalDisc) modalDisc.textContent = text(guide.disclaimer || '');
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
        if (catEl) catEl.textContent = text(g.category || '');
        if (rtEl) rtEl.textContent = text(g.readTime || '');
        if (prEl) prEl.textContent = text(g.prompt || '');
        if (tiEl) tiEl.textContent = text(g.title || '');
        if (txEl) txEl.textContent = text(g.text || '');
        if (qList) {
          qList.innerHTML = '';
          (g.questions || []).forEach(function (q, qi) {
            var li = document.createElement('li');
            li.innerHTML =
              '<span>0' + (qi + 1) + '</span><span>' + text(q) + '</span>';
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
            toggleLabel.textContent = autoplay ? text('Pause') : text('Play');
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

  /**
   * Progressive enhancement: forms post to admin-post.php and redirect back
   * with a status even without JavaScript. When AJAX is available we submit in
   * place, show inline feedback, and keep the no-JS redirect working.
   */
  function initForms() {
    document.querySelectorAll('[data-wla-form]').forEach(function (form) {
      if (!cfg.ajaxUrl) return;
      form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) return;
        e.preventDefault();
        var btn = form.querySelector('button[type="submit"]');
        var label = form.querySelector('[data-submit-label]');
        var okEl = form.querySelector('[data-form-success]');
        var errEl = form.querySelector('[data-form-error]');
        var origText = label ? label.textContent : '';
        if (okEl) okEl.hidden = true;
        if (errEl) errEl.hidden = true;
        if (btn) btn.disabled = true;
        if (label) label.textContent = cfg.sendingLabel || 'Sending…';

        var fd = new FormData(form);
        fd.set('action', 'wla_ajax_form');

        fetch(cfg.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function (r) {
            return r.json();
          })
          .then(function (res) {
            if (res && res.success) {
              form.reset();
              if (okEl) {
                if (res.data && res.data.message) okEl.textContent = res.data.message;
                okEl.hidden = false;
              }
            } else if (errEl) {
              if (res && res.data && res.data.message) errEl.textContent = res.data.message;
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

    // "Ask about this topic" jumps to the form and preselects a topic.
    document.querySelectorAll('[data-ask-guide-topic]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = document.getElementById('contact');
        if (target) target.scrollIntoView({ behavior: 'smooth' });
      });
    });
  }

  function boot() {
    initHeaderAndMenus();
    initScrollAndTilt();
    initJourneys();
    initExplorersAndModal();
    initFaqAccordions();
    initForms();
  }


  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();

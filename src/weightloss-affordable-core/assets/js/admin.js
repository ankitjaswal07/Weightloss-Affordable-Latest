/**
 * Admin behaviour: page-section editor, repeaters, media picker and import guard.
 */
(function () {
  'use strict';

  var counter = 0;

  function uniqueIndex() {
    counter += 1;
    return 'n' + Date.now().toString(36) + counter;
  }

  function reindexSection(section) {
    var idx = uniqueIndex();
    section.querySelectorAll('[name]').forEach(function (input) {
      input.name = input.name.replace(/wla_sections\[[^\]]+\]/, 'wla_sections[' + idx + ']');
    });
    section.dataset.index = idx;
  }

  function initMediaPicker(scope) {
    scope.querySelectorAll('.wla-media-pick').forEach(function (button) {
      if (button.dataset.bound) return;
      button.dataset.bound = '1';
      button.addEventListener('click', function (e) {
        e.preventDefault();
        if (!window.wp || !window.wp.media) return;
        var input = button.parentNode.querySelector('input[type="text"]');
        var frame = window.wp.media({
          title: button.textContent,
          multiple: false,
          library: { type: 'image' }
        });
        frame.on('select', function () {
          var attachment = frame.state().get('selection').first().toJSON();
          if (input) input.value = attachment.id;
        });
        frame.open();
      });
    });

    scope.querySelectorAll('.wla-media-clear').forEach(function (button) {
      if (button.dataset.bound) return;
      button.dataset.bound = '1';
      button.addEventListener('click', function (e) {
        e.preventDefault();
        var input = button.parentNode.querySelector('input[type="text"]');
        if (input) input.value = '';
      });
    });
  }

  function initRepeater(repeater) {
    var rows = repeater.querySelector('[data-wla-repeater-rows]');
    if (!rows) return;
    var blueprint = rows.querySelector('.wla-repeater__row[style*="display:none"]') ||
      rows.lastElementChild;

    var addButton = repeater.querySelector('.wla-repeater__add');
    if (addButton && !addButton.dataset.bound) {
      addButton.dataset.bound = '1';
      addButton.addEventListener('click', function (e) {
        e.preventDefault();
        if (!blueprint) return;
        var clone = blueprint.cloneNode(true);
        clone.style.display = '';
        var rowIndex = uniqueIndex();
        clone.querySelectorAll('[name]').forEach(function (input) {
          input.name = input.name.replace(/\[__r__\]|\[[^\]]+\](?=\[)/, function () {
            return '[' + rowIndex + ']';
          });
          // replace the row index segment (the part right after the repeater name)
          input.name = input.name.replace(/\]\[(?:__r__|n[a-z0-9]+)\]/, '][' + rowIndex + ']');
        });
        clone.querySelectorAll('input[type="text"], textarea').forEach(function (input) {
          input.value = '';
        });
        rows.appendChild(clone);
        initRepeater(repeater);
      });
    }

    rows.querySelectorAll('.wla-row-remove').forEach(function (button) {
      if (button.dataset.bound) return;
      button.dataset.bound = '1';
      button.addEventListener('click', function (e) {
        e.preventDefault();
        var row = button.closest('.wla-repeater__row');
        if (row && row !== blueprint) row.remove();
        else if (row) {
          row.querySelectorAll('input, textarea, select').forEach(function (input) {
            input.value = '';
          });
        }
      });
    });

    rows.querySelectorAll('.wla-row-up').forEach(function (button) {
      if (button.dataset.bound) return;
      button.dataset.bound = '1';
      button.addEventListener('click', function (e) {
        e.preventDefault();
        var row = button.closest('.wla-repeater__row');
        if (row && row.previousElementSibling) {
          rows.insertBefore(row, row.previousElementSibling);
        }
      });
    });

    rows.querySelectorAll('.wla-row-down').forEach(function (button) {
      if (button.dataset.bound) return;
      button.dataset.bound = '1';
      button.addEventListener('click', function (e) {
        e.preventDefault();
        var row = button.closest('.wla-repeater__row');
        if (row && row.nextElementSibling && row.nextElementSibling !== blueprint) {
          rows.insertBefore(row.nextElementSibling, row);
        }
      });
    });
  }

  function initSectionEditor() {
    var wrap = document.querySelector('[data-wla-sections]');
    if (!wrap) return;

    var list = wrap.querySelector('[data-wla-section-list]');
    var addType = wrap.querySelector('[data-wla-add-type]');
    var addButton = wrap.querySelector('[data-wla-add-section]');

    if (addButton && addType && list) {
      addButton.addEventListener('click', function (e) {
        e.preventDefault();
        var type = addType.value;
        var template = wrap.querySelector('[data-wla-template="' + type + '"]');
        if (!template) return;
        var holder = document.createElement('div');
        holder.innerHTML = template.innerHTML;
        var section = holder.firstElementChild;
        var index = uniqueIndex();
        section.querySelectorAll('[name]').forEach(function (input) {
          input.name = input.name.split('__i__').join(index);
        });
        section.dataset.index = index;
        var empty = list.querySelector('.wla-sections__empty');
        if (empty) empty.remove();
        list.appendChild(section);
        initSection(section);
        var body = section.querySelector('.wla-section__body');
        var toggle = section.querySelector('.wla-toggle');
        if (body) body.hidden = false;
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
        section.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
    }

    function initSection(section) {
      var toggle = section.querySelector('.wla-toggle');
      var body = section.querySelector('.wla-section__body');
      if (toggle && body && !toggle.dataset.bound) {
        toggle.dataset.bound = '1';
        toggle.addEventListener('click', function (e) {
          e.preventDefault();
          body.hidden = !body.hidden;
          toggle.setAttribute('aria-expanded', String(!body.hidden));
        });
      }

      var up = section.querySelector('.wla-move-up');
      var down = section.querySelector('.wla-move-down');
      var remove = section.querySelector('.wla-remove');

      if (up && !up.dataset.bound) {
        up.dataset.bound = '1';
        up.addEventListener('click', function (e) {
          e.preventDefault();
          if (section.previousElementSibling) {
            list.insertBefore(section, section.previousElementSibling);
          }
        });
      }
      if (down && !down.dataset.bound) {
        down.dataset.bound = '1';
        down.addEventListener('click', function (e) {
          e.preventDefault();
          if (section.nextElementSibling) {
            list.insertBefore(section.nextElementSibling, section);
          }
        });
      }
      if (remove && !remove.dataset.bound) {
        remove.dataset.bound = '1';
        remove.addEventListener('click', function (e) {
          e.preventDefault();
          if (window.confirm('Remove this section from the page?')) {
            section.remove();
          }
        });
      }

      section.querySelectorAll('[data-wla-repeater]').forEach(initRepeater);
      initMediaPicker(section);
    }

    list.querySelectorAll('[data-wla-section]').forEach(initSection);
  }

  function initImportGuard() {
    var form = document.querySelector('.wla-admin__import');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      var reset = form.querySelector('[data-wla-reset]');
      if (reset && reset.checked) {
        var ok = window.confirm(
          'Reset will delete records created by a previous demo import, then re-import them. Your own content is not touched. Continue?'
        );
        if (!ok) {
          e.preventDefault();
          return;
        }
      }
      var button = form.querySelector('input[type="submit"]');
      if (button) {
        button.value = 'Importing… this can take a moment';
        button.disabled = true;
      }
    });
  }

  function boot() {
    initSectionEditor();
    initImportGuard();
    if (window.wp && window.wp.media) {
      document.querySelectorAll('#wla_treatment_details, #wla_page_sections').forEach(initMediaPicker);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();

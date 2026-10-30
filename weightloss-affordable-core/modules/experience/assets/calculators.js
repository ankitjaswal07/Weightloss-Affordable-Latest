(function (root) {
  'use strict';
  function positiveNumber(value) {
    var number = Number(value);
    return Number.isFinite(number) && number > 0 ? number : null;
  }
  function bmiMetric(weightKg, heightCm) {
    var weight = positiveNumber(weightKg), height = positiveNumber(heightCm);
    if (weight === null || height === null) return null;
    var meters = height / 100;
    var result = weight / (meters * meters);
    return Number.isFinite(result) ? result : null;
  }
  function bmiImperial(weightLb, feet, inches) {
    var weight = positiveNumber(weightLb), ft = Number(feet), inch = Number(inches);
    if (weight === null || feet === null || inches === null || !Number.isFinite(ft) || ft < 0 || !Number.isFinite(inch) || inch < 0 || inch >= 12) return null;
    var totalInches = ft * 12 + inch;
    if (totalInches <= 0) return null;
    var result = (weight / (totalInches * totalInches)) * 703;
    return Number.isFinite(result) ? result : null;
  }
  function weightChange(starting, current) {
    var start = positiveNumber(starting), now = positiveNumber(current);
    if (start === null || now === null) return null;
    var difference = now - start;
    var percent = (difference / start) * 100;
    if (!Number.isFinite(percent)) return null;
    return { difference: Math.abs(difference), percent: percent, direction: difference < 0 ? 'loss' : (difference > 0 ? 'gain' : 'unchanged') };
  }
  var api = { bmiMetric: bmiMetric, bmiImperial: bmiImperial, weightChange: weightChange };
  root.WLACalculators = api;
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
  if (!root.document) return;

  function spanish() { return /^es(?:-|$)/i.test(root.document.documentElement.lang || ''); }
  function numberFrom(form, name) {
    var field = form.elements.namedItem(name);
    if (!field || field.value.trim() === '') return null;
    var number = Number(field.value);
    return Number.isFinite(number) ? number : null;
  }
  function updateUnitLabels(form) {
    var changeUnit = form.elements.namedItem('change_unit');
    if (!changeUnit) return;
    var suffix = changeUnit.value === 'kg' ? 'kg' : 'lb';
    form.querySelector('[data-weight-label="starting"]').textContent = spanish() ? 'Peso inicial (' + suffix + ')' : 'Starting weight (' + suffix + ')';
    form.querySelector('[data-weight-label="current"]').textContent = spanish() ? 'Peso actual o de comparación (' + suffix + ')' : 'Current or comparison weight (' + suffix + ')';
  }
  function onUnitChange(form) {
    var metric = form.elements.namedItem('unit').value === 'metric';
    form.querySelector('.wla-exp-us-fields').hidden = metric;
    form.querySelector('.wla-exp-metric-fields').hidden = !metric;
    form.querySelector('[name="feet"]').required = !metric;
    form.querySelector('[name="inches"]').required = !metric;
    form.querySelector('[name="weight_lb"]').required = !metric;
    form.querySelector('[name="height_cm"]').required = metric;
    form.querySelector('[name="weight_kg"]').required = metric;
    form.querySelector('.wla-exp-error').textContent = '';
    form.querySelector('.wla-exp-output').textContent = '';
  }
  function setup(form) {
    var unit = form.elements.namedItem('unit');
    var changeUnit = form.elements.namedItem('change_unit');
    if (unit) unit.addEventListener('change', function () { onUnitChange(form); });
    if (changeUnit) changeUnit.addEventListener('change', function () {
      updateUnitLabels(form);
      form.querySelector('.wla-exp-error').textContent = '';
      form.querySelector('.wla-exp-output').textContent = '';
    });
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var es = spanish();
      var error = form.querySelector('.wla-exp-error'), output = form.querySelector('.wla-exp-output');
      error.textContent = ''; output.textContent = '';
      if (form.dataset.wlaCalculator === 'bmi') {
        var bmi = unit.value === 'metric'
          ? bmiMetric(numberFrom(form, 'weight_kg'), numberFrom(form, 'height_cm'))
          : bmiImperial(numberFrom(form, 'weight_lb'), numberFrom(form, 'feet'), numberFrom(form, 'inches'));
        if (bmi === null) { error.textContent = es ? 'Ingresa una altura y un peso válidos.' : 'Enter a valid positive height and weight.'; return; }
        output.textContent = (es ? 'IMC: ' : 'BMI: ') + bmi.toFixed(1);
        return;
      }
      var change = weightChange(numberFrom(form, 'starting'), numberFrom(form, 'current'));
      if (!change) { error.textContent = es ? 'Ingresa pesos válidos mayores que cero.' : 'Enter valid positive weights. Starting weight must be greater than zero.'; return; }
      var amount = change.difference.toFixed(1) + ' ' + changeUnit.value;
      var percent = Math.abs(change.percent).toFixed(1) + '%';
      if (change.direction === 'loss') output.textContent = es ? 'Diferencia: ' + amount + ' menos (' + percent + ').' : 'Difference: ' + amount + ' decrease (' + percent + ').';
      else if (change.direction === 'gain') output.textContent = es ? 'Diferencia: ' + amount + ' más (' + percent + ').' : 'Difference: ' + amount + ' increase (' + percent + ').';
      else output.textContent = es ? 'Sin diferencia de peso (0.0%).' : 'No weight difference (0.0%).';
    });
    updateUnitLabels(form);
    root.document.addEventListener('wla:langchange', function () {
      updateUnitLabels(form);
      var output = form.querySelector('.wla-exp-output');
      if (output.textContent) form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    });
  }
  root.document.querySelectorAll('[data-wla-calculator]').forEach(setup);
})(typeof window !== 'undefined' ? window : globalThis);

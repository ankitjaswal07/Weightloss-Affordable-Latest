const test = require('node:test');
const assert = require('node:assert/strict');
const calc = require('../assets/calculators.js');

test('BMI metric formula', () => {
  assert.equal(calc.bmiMetric(70, 175), 22.857142857142858);
  assert.equal(calc.bmiMetric(70, 0), null);
  assert.equal(calc.bmiMetric(NaN, 175), null);
  assert.equal(calc.bmiMetric(Infinity, 175), null);
});

test('BMI US customary formula and input boundaries', () => {
  const result = calc.bmiImperial(154.323584, 5, 8.897638);
  assert.ok(Math.abs(result - 22.855) < 0.005);
  assert.equal(calc.bmiImperial(150, 0, 0), null);
  assert.equal(calc.bmiImperial(150, 5, 12), null);
  assert.equal(calc.bmiImperial(150, 5, null), null);
  assert.equal(calc.bmiImperial(0, 5, 8), null);
});

test('weight comparison calculates absolute difference and signed percentage', () => {
  assert.deepEqual(calc.weightChange(100, 90), { difference: 10, percent: -10, direction: 'loss' });
  assert.deepEqual(calc.weightChange(100, 110), { difference: 10, percent: 10, direction: 'gain' });
  assert.deepEqual(calc.weightChange(100, 100), { difference: 0, percent: 0, direction: 'unchanged' });
  assert.equal(calc.weightChange(0, 90), null);
  assert.equal(calc.weightChange(-1, 90), null);
  assert.equal(calc.weightChange(100, Infinity), null);
});

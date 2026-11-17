import test from 'node:test';
import assert from 'node:assert/strict';
import { matchFace, descriptorDistance } from '../public/js/facial-match.mjs';

const sample = (value) => Array(128).fill(value);
test('matches a known face and rejects an unknown face', () => {
    const faces = [{ studentId: '7', descriptors: [sample(0)] }];
    assert.equal(matchFace(sample(0.01), faces), '7');
    assert.equal(matchFace(sample(0.1), faces), null);
    assert.equal(matchFace(sample(0), []), null);
});
test('rejects ambiguous matches between two students', () => {
    assert.equal(
        matchFace(sample(0), [
            { studentId: '7', descriptors: [sample(0.01)] },
            { studentId: '8', descriptors: [sample(0.012)] }
        ]),
        null
    );
});
test('rejects malformed or non-finite descriptors', () => {
    assert.equal(descriptorDistance([], sample(0)), Infinity);
    assert.equal(matchFace(sample(NaN), [{ studentId: '7', descriptors: [sample(0)] }]), null);
});

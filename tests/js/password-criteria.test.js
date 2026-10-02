import { describe, expect, it } from 'vitest';
import cases from '../fixtures/password-cases.json';
import criteria from '../../resources/password-criteria.json';
import { evaluate, failing } from '../../resources/js/password-criteria.js';

describe('password criteria', () => {
    it('defines the seven criteria in order', () => {
        expect(criteria.map((c) => c.key)).toEqual([
            'min_length', 'uppercase', 'lowercase', 'digit', 'special', 'no_whitespace', 'no_triple_repeat',
        ]);
    });

    it.each(cases.map((c) => [c.name, c.password, c.failing]))('%s', (_name, password, expected) => {
        expect([...failing(password)].sort()).toEqual([...expected].sort());
    });

    it('evaluate() returns one boolean per criterion key', () => {
        const result = evaluate('Abcdefghijklmn1!');
        expect(Object.keys(result)).toEqual(criteria.map((c) => c.key));
        expect(Object.values(result).every(Boolean)).toBe(true);

        expect(evaluate('abc').uppercase).toBe(false);
        expect(evaluate('abc').lowercase).toBe(true);
    });
});

import criteria from '../password-criteria.json';

// Los patrones vienen del mismo JSON que usa el servidor (App\Support\PasswordCriteria).
const compiled = criteria.map((criterion) => ({
    ...criterion,
    regex: new RegExp(criterion.pattern, 'u'),
}));

/** @returns {Record<string, boolean>} true por cada criterio cumplido, indexado por su clave. */
export function evaluate(password) {
    const result = {};

    for (const { key, type, regex } of compiled) {
        const found = regex.test(password);
        result[key] = type === 'match' ? found : !found;
    }

    return result;
}

/** @returns {string[]} claves de los criterios que NO se cumplen. */
export function failing(password) {
    return Object.entries(evaluate(password))
        .filter(([, met]) => !met)
        .map(([key]) => key);
}

/** Estado visual con el campo vacío: nada en verde hasta que el usuario escribe. */
export const untouched = Object.fromEntries(compiled.map(({ key }) => [key, false]));

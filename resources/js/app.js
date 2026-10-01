import Alpine from 'alpinejs';
import { evaluate, untouched } from './password-criteria';

window.Alpine = Alpine;

Alpine.data('passwordCriteria', () => ({
    password: '',

    get met() {
        return this.password === '' ? untouched : evaluate(this.password);
    },
}));

Alpine.start();

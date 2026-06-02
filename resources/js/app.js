import './bootstrap';
import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';
import 'flatpickr/dist/flatpickr.min.css';

window.Alpine = Alpine;
Alpine.plugin(persist);
Alpine.start();

// Locale español por defecto en todos los flatpickr de la app.
flatpickr.localize(Spanish);

// Expuesto en window para poder usarlo desde Alpine en blade (`x-init`).
window.flatpickr = flatpickr;

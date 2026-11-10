import './bootstrap';

import Alpine from 'alpinejs';
import { initMarquees } from './marquee';

window.Alpine = Alpine;

Alpine.start();

// トップのブログのスライド
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMarquees);
} else {
    initMarquees();
}

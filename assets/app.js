import './stimulus_bootstrap.js';
import './styles/app.css';

// Safari on iOS shows :active on a touch only when the page listens to touch events. The heart of
// the like button grows while it is :active.
document.addEventListener('touchstart', () => {}, { passive: true });

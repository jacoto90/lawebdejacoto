(() => {
    'use strict';
    const hero = document.querySelector('.hero');
    if (!hero) return;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    const replay = hero.querySelector('.hero-replay');
    const portrait = hero.querySelector('.hero-portrait');
    let entranceTimer;
    let photoTimer;
    let photoObserver;
    const clearEntrance = () => {
        clearTimeout(entranceTimer);
        clearTimeout(photoTimer);
        hero.classList.remove('is-cinema-entering', 'is-photo-entering', 'is-photo-pending');
        photoObserver?.disconnect();
    };
    const revealPhoto = () => {
        if (reduced.matches) return;
        hero.classList.remove('is-photo-pending');
        hero.classList.add('is-photo-entering');
        photoTimer = setTimeout(() => hero.classList.remove('is-photo-entering'), 2300);
    };
    const enter = () => {
        clearEntrance();
        if (reduced.matches) return;
        // Flush only on explicit replay; the default page is readable without JS.
        void hero.offsetWidth;
        hero.classList.add('is-cinema-entering', 'is-photo-pending');
        entranceTimer = setTimeout(() => hero.classList.remove('is-cinema-entering'), 2600);
        if ('IntersectionObserver' in window) {
            photoObserver = new IntersectionObserver(entries => {
                if (!entries[0].isIntersecting) return;
                revealPhoto();
                photoObserver.disconnect();
            }, { threshold: .15 });
            photoObserver.observe(portrait);
        } else revealPhoto();
    };
    replay.hidden = reduced.matches;
    replay.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'instant' });
        enter();
    });
    reduced.addEventListener('change', () => {
        replay.hidden = reduced.matches;
        clearEntrance();
    });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) clearEntrance();
    });
    window.addEventListener('pagehide', clearEntrance);
    // Clean section URLs should open directly at their destination.
    if ((!document.body.dataset.scrollTarget || document.body.dataset.scrollTarget === 'top') && !location.hash) enter();
})();

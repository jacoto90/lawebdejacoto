(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const header = document.querySelector('.site-header');
    const menu = document.getElementById('mainNav');
    const menuButton = document.getElementById('menuBtn');
    const themeButton = document.getElementById('themeToggle');
    const language = document.body.dataset.language || 'es';
    const routes = {
        es: { top: '/', about: '/sobre-mi', experience: '/experiencia', services: '/servicios', projects: '/proyectos', hobby: '/hobbies', contact: '/hablemos' },
        en: { top: '/en', about: '/en/about', experience: '/en/experience', services: '/en/services', projects: '/en/projects', hobby: '/en/hobbies', contact: '/en/contact' },
    };

    const setMenu = (open, restoreFocus = false) => {
        if (!menu || !menuButton) return;
        menu.classList.toggle('is-open', open);
        menuButton.setAttribute('aria-expanded', String(open));
        menuButton.querySelector('span').textContent = open ? menuButton.dataset.closeLabel : menuButton.dataset.openLabel;
        document.body.classList.toggle('menu-open', open);
        if (restoreFocus) menuButton.focus();
    };

    menuButton?.addEventListener('click', () => {
        const open = menuButton.getAttribute('aria-expanded') !== 'true';
        setMenu(open);
        if (open) menu.querySelector('a')?.focus();
    });

    document.addEventListener('keydown', (event) => {
        if (menuButton?.getAttribute('aria-expanded') !== 'true') return;
        if (event.key === 'Escape') setMenu(false, true);
        if (event.key === 'Tab') {
            const focusable = [...header.querySelectorAll('a,button')].filter(node => node.getClientRects().length);
            const first = focusable[0];
            const last = focusable.at(-1);
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });

    window.matchMedia('(min-width: 901px)').addEventListener('change', (event) => {
        if (event.matches) setMenu(false);
    });

    const updateRouteLinks = (id) => {
        menu?.querySelectorAll('[data-section]').forEach(link => {
            if (link.dataset.section === id) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
        document.querySelectorAll('[data-language-link]').forEach(link => {
            link.href = routes[link.dataset.languageLink][id] || routes[link.dataset.languageLink].top;
        });
    };

    const goToSection = (id, animate = true, focus = false) => {
        const target = document.getElementById(id);
        if (!target) return;
        setMenu(false);
        updateRouteLinks(id);
        if (focus) {
            target.setAttribute('tabindex', '-1');
            target.focus({ preventScroll: true });
            target.addEventListener('blur', () => target.removeAttribute('tabindex'), { once: true });
        }
        const top = id === 'top' ? 0 : Math.max(0, target.getBoundingClientRect().top + window.scrollY - (header?.offsetHeight || 0) - 20);
        window.scrollTo({ top, behavior: animate && !reducedMotion.matches ? 'smooth' : 'instant' });
    };

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-section], a[href^="#"]');
        if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const id = link.dataset.section || link.hash.slice(1);
        if (!document.getElementById(id)) return;
        if (id === 'main') return;
        event.preventDefault();
        document.querySelectorAll('[data-specialty][open]').forEach(details => { details.open = false; });
        if (routes[language][id]) history.pushState({ section: id }, '', routes[language][id]);
        goToSection(id, true, true);
    });

    window.addEventListener('popstate', () => {
        const id = Object.keys(routes[language]).find(key => routes[language][key] === location.pathname.replace(/\/$/, '')) || 'top';
        goToSection(id, false);
    });

    const initialTarget = location.hash.slice(1) || document.body.dataset.scrollTarget;
    if (initialTarget && initialTarget !== 'top') {
        // Font metrics settle before resolving a clean deep link's final position.
        document.fonts.ready.then(() => { measureHero(); goToSection(initialTarget, false); });
    }

    const applyTheme = (theme) => {
        document.documentElement.dataset.theme = theme;
        themeButton?.setAttribute('aria-pressed', String(theme === 'dark'));
        document.querySelector('meta[name="theme-color"]')?.setAttribute('content', theme === 'dark' ? '#191c18' : '#f2f0e9');
    };
    applyTheme(document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light');
    themeButton?.addEventListener('click', () => {
        const theme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        applyTheme(theme);
        try { localStorage.setItem('lawebdejacoto_theme', theme); } catch (_) { /* Theme works without storage. */ }
    });

    if ('IntersectionObserver' in window && !reducedMotion.matches) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px 35px 0px' });
        const belowFold = [...document.querySelectorAll('[data-reveal]')]
            .filter(node => node.getBoundingClientRect().top >= window.innerHeight);
        belowFold.forEach(node => {
            node.classList.add('will-reveal');
            observer.observe(node);
        });
        reducedMotion.addEventListener('change', (event) => {
            if (!event.matches) return;
            observer.disconnect();
            document.querySelectorAll('.will-reveal').forEach(node => node.classList.add('is-visible'));
        });
    }

    const exclusiveDetails = (selector) => {
        const items = [...document.querySelectorAll(selector)];
        items.forEach(item => item.addEventListener('toggle', () => {
            if (item.open) items.forEach(other => { if (other !== item) other.open = false; });
        }));
    };
    exclusiveDetails('[data-accordion]');
    exclusiveDetails('[data-specialty]');
    document.addEventListener('click', event => {
        if (!event.target.closest('.specialties')) document.querySelectorAll('[data-specialty][open]').forEach(item => { item.open = false; });
    });

    const adminEntry = document.querySelector('[data-admin-entry]');
    let taps = 0;
    let tapTimer;
    adminEntry?.addEventListener('click', () => {
        taps += 1;
        clearTimeout(tapTimer);
        if (taps >= 3) { window.location.assign('/admin/'); return; }
        tapTimer = setTimeout(() => { taps = 0; }, 1400);
    });

    // A short native-scroll scene: transforms never change the document's layout.
    const hero = document.querySelector('.hero');
    const heroScene = hero?.querySelector('.hero-scroll-scene');
    const heroStage = hero?.querySelector('.hero-stage');
    const pinnedHero = window.matchMedia('(min-width: 901px) and (min-height: 780px)');
    let heroStart = 0;
    let heroRange = 320;
    let heroCanPin = false;
    let lastHeroProgress = -1;
    const updateHero = () => {
        if (!heroScene) return;
        const progress = reducedMotion.matches ? 0 : Math.max(0, Math.min(1, (window.scrollY - heroStart) / heroRange));
        if (progress === lastHeroProgress) return;
        lastHeroProgress = progress;
        const eased = progress * progress * (3 - 2 * progress);
        const animated = !reducedMotion.matches;
        hero.style.setProperty('--hero-type-scale', animated ? (heroCanPin ? 1.10 - .24 * eased : 1.035 - .095 * eased).toFixed(4) : '1');
        hero.style.setProperty('--hero-identity-y', animated && heroCanPin ? `${-36 * eased}px` : '0px');
        hero.style.setProperty('--hero-portrait-scale', animated && heroCanPin ? (.94 + .06 * eased).toFixed(4) : '1');
        hero.style.setProperty('--hero-portrait-y', animated && heroCanPin ? `${14 * (1 - eased)}px` : '0px');
        hero.style.setProperty('--hero-underline', animated ? (.18 + .82 * eased).toFixed(4) : '1');
    };
    const measureHero = () => {
        if (!heroScene) return;
        const headerHeight = header?.offsetHeight || 0;
        heroCanPin = pinnedHero.matches && heroStage.offsetHeight + headerHeight + 48 < window.innerHeight;
        hero.classList.toggle('is-scroll-ready', heroCanPin && !reducedMotion.matches);
        hero.style.setProperty('--hero-header-height', `${headerHeight}px`);
        hero.style.setProperty('--hero-stage-height', `${heroStage.offsetHeight}px`);
        heroStart = Math.max(0, heroScene.getBoundingClientRect().top + window.scrollY - headerHeight - 24);
        heroRange = heroCanPin && !reducedMotion.matches ? Math.max(1, heroScene.offsetHeight - heroStage.offsetHeight) : Math.min(360, window.innerHeight * .55);
        lastHeroProgress = -1;
        updateHero();
    };
    let framePending = false;
    const updateProgress = () => {
        const range = document.documentElement.scrollHeight - window.innerHeight;
        header?.style.setProperty('--progress', range > 0 ? Math.min(1, Math.max(0, window.scrollY / range)) : 0);
        updateHero();
        framePending = false;
    };
    window.addEventListener('scroll', () => {
        if (framePending) return;
        framePending = true;
        requestAnimationFrame(updateProgress);
    }, { passive: true });
    window.addEventListener('resize', measureHero, { passive: true });
    reducedMotion.addEventListener('change', measureHero);
    if (heroScene) {
        measureHero();
        document.fonts.ready.then(measureHero);
        if ('ResizeObserver' in window) new ResizeObserver(measureHero).observe(heroStage);
        window.addEventListener('pageshow', measureHero);
    }
    updateProgress();
})();

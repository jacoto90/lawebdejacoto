(() => {
    'use strict';
    const root = document.documentElement;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    root.classList.add('js');

    const themeButton = document.querySelector('.theme-toggle');
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
    const updateThemeButton = () => {
        const dark = root.dataset.theme ? root.dataset.theme === 'dark' : systemTheme.matches;
        themeButton.setAttribute('aria-pressed', String(dark));
    };
    try {
        const saved = localStorage.getItem('paso-seguro-theme');
        if (saved === 'light' || saved === 'dark') root.dataset.theme = saved;
    } catch (_) { /* Storage is optional. */ }
    themeButton.hidden = false;
    updateThemeButton();
    systemTheme.addEventListener('change', updateThemeButton);
    themeButton.addEventListener('click', () => {
        root.dataset.theme = themeButton.getAttribute('aria-pressed') === 'true' ? 'light' : 'dark';
        try { localStorage.setItem('paso-seguro-theme', root.dataset.theme); } catch (_) { /* Optional. */ }
        updateThemeButton();
    });

    const menuButton = document.querySelector('.menu-toggle');
    const nav = document.querySelector('#main-nav');
    menuButton.hidden = false;
    const closeMenu = () => {
        menuButton.setAttribute('aria-expanded', 'false');
        nav.classList.remove('is-open');
    };
    menuButton.addEventListener('click', () => {
        const open = menuButton.getAttribute('aria-expanded') !== 'true';
        menuButton.setAttribute('aria-expanded', String(open));
        nav.classList.toggle('is-open', open);
    });
    nav.addEventListener('click', event => {
        const link = event.target.closest('a');
        if (!link) return;
        const wasOpen = nav.classList.contains('is-open');
        closeMenu();
        // Keep keyboard focus on the destination when the mobile links disappear.
        if (wasOpen) {
            const section = document.querySelector(link.hash);
            section.tabIndex = -1;
            section.focus({ preventScroll: true });
        }
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && nav.classList.contains('is-open')) {
            closeMenu();
            menuButton.focus();
        }
    });
    document.addEventListener('click', event => {
        if (!event.target.closest('.site-header')) closeMenu();
    });
    window.matchMedia('(min-width: 1151px)').addEventListener('change', closeMenu);

    const links = [...nav.querySelectorAll('a')];
    const sections = links.map(link => document.querySelector(link.hash));
    const progress = document.querySelector('.scroll-track > div');
    let scrollPending = false;
    const updateScroll = () => {
        const range = root.scrollHeight - window.innerHeight;
        progress.style.transform = `scaleX(${range > 0 ? Math.min(1, Math.max(0, window.scrollY / range)) : 0})`;
        let active = null;
        for (const section of sections) {
            if (section.getBoundingClientRect().top <= 160) active = section.id;
        }
        for (const link of links) {
            if (link.hash === `#${active}`) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        }
        scrollPending = false;
    };
    const scheduleScroll = () => {
        if (scrollPending) return;
        scrollPending = true;
        requestAnimationFrame(updateScroll);
    };
    window.addEventListener('scroll', scheduleScroll, { passive: true });
    window.addEventListener('resize', scheduleScroll);
    updateScroll();

    // Original six-frame sequence: 2400 ms interval, 2000 ms crossfade.
    const frames = [...document.querySelectorAll('.frame')];
    const frameButtons = [...document.querySelectorAll('[data-frame]')];
    const playButton = document.querySelector('.play-toggle');
    const counter = document.querySelector('.frame-counter');
    const scene = document.querySelector('.simulation-shell');
    let activeFrame = 0;
    let paused = reducedMotion.matches;
    let hovered = false;
    let timer = null;
    document.querySelector('.simulation-controls').hidden = false;
    const showFrame = index => {
        activeFrame = index;
        frames.forEach((frame, i) => frame.classList.toggle('is-active', i === index));
        frameButtons.forEach((button, i) => button.setAttribute('aria-pressed', String(i === index)));
        counter.textContent = `${String(index + 1).padStart(2, '0')} / 06`;
    };
    const updatePlayback = () => {
        clearInterval(timer);
        timer = null;
        playButton.textContent = paused ? playButton.dataset.play : playButton.dataset.pause;
        playButton.setAttribute('aria-pressed', String(paused));
        if (!paused && !hovered && !document.hidden) {
            timer = setInterval(() => showFrame((activeFrame + 1) % frames.length), 2400);
        }
    };
    playButton.addEventListener('click', () => { paused = !paused; updatePlayback(); });
    frameButtons.forEach((button, index) => button.addEventListener('click', () => {
        paused = true;
        showFrame(index);
        updatePlayback();
    }));
    scene.addEventListener('pointerenter', event => {
        if (event.pointerType === 'mouse') { hovered = true; updatePlayback(); }
    });
    scene.addEventListener('pointerleave', () => { hovered = false; updatePlayback(); });
    document.addEventListener('visibilitychange', updatePlayback);
    window.addEventListener('pagehide', () => clearInterval(timer));
    window.addEventListener('pageshow', updatePlayback);
    reducedMotion.addEventListener('change', () => {
        if (reducedMotion.matches) paused = true;
        updatePlayback();
    });
    updatePlayback();

    if ('IntersectionObserver' in window && !reducedMotion.matches) {
        const observer = new IntersectionObserver(entries => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.remove('reveal-pending');
                    observer.unobserve(entry.target);
                }
            }
        }, { threshold: 0.08 });
        document.querySelectorAll('[data-reveal]').forEach(element => {
            if (element.getBoundingClientRect().top > innerHeight) {
                element.classList.add('reveal-pending');
                observer.observe(element);
            }
        });
    }
})();

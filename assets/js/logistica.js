(() => {
    'use strict';
    const root = document.documentElement;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    root.classList.add('js');

    const theme = document.querySelector('.theme-button');
    try {
        const saved = localStorage.getItem('logistics-theme');
        if (saved === 'light' || saved === 'dark') root.dataset.theme = saved;
    } catch (_) { /* Theme persistence is optional. */ }
    theme.hidden = false;
    const syncTheme = () => theme.setAttribute('aria-pressed', String(root.dataset.theme === 'light'));
    syncTheme();
    theme.addEventListener('click', () => {
        root.dataset.theme = root.dataset.theme === 'light' ? 'dark' : 'light';
        try { localStorage.setItem('logistics-theme', root.dataset.theme); } catch (_) { /* Optional. */ }
        syncTheme();
    });

    const menu = document.querySelector('.menu-button');
    const nav = document.querySelector('#project-nav');
    menu.hidden = false;
    const closeMenu = () => {
        menu.setAttribute('aria-expanded', 'false');
        nav.classList.remove('is-open');
    };
    menu.addEventListener('click', () => {
        const open = menu.getAttribute('aria-expanded') !== 'true';
        menu.setAttribute('aria-expanded', String(open));
        nav.classList.toggle('is-open', open);
    });
    nav.addEventListener('click', event => {
        const link = event.target.closest('a');
        if (!link) return;
        const open = nav.classList.contains('is-open');
        closeMenu();
        if (open) {
            const target = document.querySelector(link.hash);
            target.tabIndex = -1;
            target.focus({ preventScroll: true });
        }
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && nav.classList.contains('is-open')) {
            closeMenu();
            menu.focus();
        }
    });
    document.addEventListener('click', event => {
        if (!event.target.closest('.site-header')) closeMenu();
    });
    window.matchMedia('(min-width: 1151px)').addEventListener('change', closeMenu);

    const stages = [...document.querySelectorAll('[data-stage]')];
    const stageTitles = stages.map(button => button.children[1].textContent);
    const stageDescriptions = stages.map(button => button.nextElementSibling.textContent);
    const stageMeter = [...document.querySelectorAll('.stage-meter span')];
    const prev = document.querySelector('[data-step="-1"]');
    const next = document.querySelector('[data-step="1"]');
    let currentStage = 4;
    document.querySelector('.stage-controls').hidden = false;
    const selectStage = index => {
        currentStage = Math.max(0, Math.min(stages.length - 1, index));
        stages.forEach((button, i) => button.setAttribute('aria-pressed', String(i === currentStage)));
        document.querySelector('#stage-count').textContent = `${String(currentStage + 1).padStart(2, '0')} / 10`;
        document.querySelector('#stage-title').textContent = stageTitles[currentStage];
        document.querySelector('#stage-description').textContent = stageDescriptions[currentStage];
        stageMeter.forEach((segment, i) => segment.classList.toggle('filled', i <= currentStage));
        prev.disabled = currentStage === 0;
        next.disabled = currentStage === stages.length - 1;
    };
    stages.forEach((button, i) => button.addEventListener('click', () => selectStage(i)));
    prev.addEventListener('click', () => selectStage(currentStage - 1));
    next.addEventListener('click', () => selectStage(currentStage + 1));

    const tabList = document.querySelector('.gallery-tabs');
    const tabs = [...document.querySelectorAll('[data-gallery-tab]')];
    const panels = [...document.querySelectorAll('.gallery-panel')];
    tabList.setAttribute('role', 'tablist');
    tabs.forEach((tab, i) => {
        tab.setAttribute('role', 'tab');
        tab.setAttribute('aria-controls', panels[i].id);
        panels[i].setAttribute('role', 'tabpanel');
        panels[i].setAttribute('aria-labelledby', tab.id);
        panels[i].tabIndex = 0;
    });
    const selectTab = index => {
        tabs.forEach((tab, i) => {
            tab.setAttribute('aria-selected', String(i === index));
            tab.tabIndex = i === index ? 0 : -1;
            panels[i].hidden = i !== index;
        });
        scheduleScroll();
    };
    tabs.forEach((tab, i) => {
        tab.addEventListener('click', event => { event.preventDefault(); selectTab(i); });
        tab.addEventListener('keydown', event => {
            let target;
            if (event.key === 'ArrowRight') target = (i + 1) % tabs.length;
            if (event.key === 'ArrowLeft') target = (i - 1 + tabs.length) % tabs.length;
            if (event.key === 'Home') target = 0;
            if (event.key === 'End') target = tabs.length - 1;
            if (target === undefined) return;
            event.preventDefault();
            selectTab(target);
            tabs[target].focus();
        });
    });

    const dialog = document.querySelector('.lightbox');
    const dialogImage = document.querySelector('#lightbox-image');
    let opener = null;
    document.querySelectorAll('[data-lightbox]').forEach(link => link.addEventListener('click', event => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || typeof dialog.showModal !== 'function') return;
        event.preventDefault();
        opener = link;
        const title = link.dataset.title;
        document.querySelector('#lightbox-title').textContent = title;
        dialogImage.alt = link.querySelector('img')?.alt || title;
        dialogImage.src = link.href;
        document.querySelector('#lightbox-original').href = link.href;
        dialog.showModal();
        document.body.classList.add('modal-open');
        document.querySelector('.lightbox-close').focus();
    }));
    document.querySelector('.lightbox-close').addEventListener('click', () => dialog.close());
    dialog.addEventListener('keydown', event => {
        if (event.key !== 'Tab') return;
        const first = dialog.querySelector('.lightbox-close');
        const last = dialog.querySelector('#lightbox-original');
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
    dialog.addEventListener('click', event => {
        const box = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom)) dialog.close();
    });
    dialog.addEventListener('close', () => {
        document.body.classList.remove('modal-open');
        dialogImage.removeAttribute('src');
        opener?.focus({ preventScroll: true });
    });

    const navLinks = [...nav.querySelectorAll('a')];
    const sections = navLinks.map(link => document.querySelector(link.hash));
    const progress = document.querySelector('.reading-progress');
    let queued = false;
    function updateScroll() {
        const range = root.scrollHeight - innerHeight;
        progress.style.transform = `scaleX(${range > 0 ? Math.min(1, Math.max(0, scrollY / range)) : 0})`;
        let active = null;
        sections.forEach(section => { if (section.getBoundingClientRect().top < 190) active = section.id; });
        navLinks.forEach(link => {
            if (link.hash === `#${active}`) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
        queued = false;
    }
    function scheduleScroll() {
        if (queued) return;
        queued = true;
        requestAnimationFrame(updateScroll);
    }
    const initialTab = panels.findIndex(panel => '#' + panel.id === location.hash);
    selectTab(initialTab < 0 ? 0 : initialTab);
    window.addEventListener('scroll', scheduleScroll, { passive: true });
    window.addEventListener('resize', scheduleScroll);
    window.addEventListener('hashchange', () => {
        const index = panels.findIndex(panel => '#' + panel.id === location.hash);
        if (index >= 0) selectTab(index);
    });
    updateScroll();

    if ('IntersectionObserver' in window && !reduced.matches) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.remove('reveal-pending');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08 });
        document.querySelectorAll('[data-reveal]').forEach(element => {
            if (element.getBoundingClientRect().top > innerHeight) {
                element.classList.add('reveal-pending');
                observer.observe(element);
            }
        });
    }
})();

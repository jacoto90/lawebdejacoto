const headerNode = document.querySelector('.site-header');

const easeInOutQuint = (t) => {
    return t < 0.5
        ? 16 * t * t * t * t * t
        : 1 - Math.pow(-2 * t + 2, 5) / 2;
};

const smoothScrollToNode = (node, baseDuration = 1150) => {
    if (!node) return;

    const startY = window.scrollY;
    const headerOffset = headerNode ? headerNode.offsetHeight : 0;
    const targetY = Math.max(0, node.getBoundingClientRect().top + window.scrollY - headerOffset - 10);
    const distance = targetY - startY;
    const distanceFactor = Math.min(1.55, Math.max(0.8, Math.abs(distance) / 680));
    const duration = Math.round(baseDuration * distanceFactor);

    if (Math.abs(distance) < 4) return;

    let startTime = null;
    const step = (time) => {
        if (startTime === null) startTime = time;
        const elapsed = time - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = easeInOutQuint(progress);
        const rawY = startY + distance * eased;
        const remaining = targetY - rawY;

        // Soft landing in final pixels for premium feel
        let y = rawY;
        if (Math.abs(remaining) < 12 && progress > 0.82) {
            y = targetY - (remaining * 0.35);
        }

        window.scrollTo(0, y);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        } else {
            window.scrollTo(0, targetY);
        }
    };

    window.requestAnimationFrame(step);
};

document.querySelectorAll('a[href^="#"]').forEach((link) => {
    link.addEventListener('click', (event) => {
        const id = link.getAttribute('href');
        if (!id || id === '#') return;
        const target = document.querySelector(id);
        if (!target) return;
        event.preventDefault();
        smoothScrollToNode(target, 1200);
    });
});

const scrollTarget = document.body.getAttribute('data-scroll-target');
if (scrollTarget) {
    const targetNode = document.getElementById(scrollTarget);
    if (targetNode) {
        window.requestAnimationFrame(() => {
            smoothScrollToNode(targetNode, 1050);
        });
    }
}

const menuBtn = document.getElementById('menuBtn');
const mainNav = document.getElementById('mainNav');
const menuOverlay = document.getElementById('menuOverlay');

if (menuBtn && mainNav) {
    menuBtn.addEventListener('click', () => {
        mainNav.classList.toggle('is-open');
        menuBtn.classList.toggle('is-open');
        if (menuOverlay) {
            menuOverlay.classList.toggle('is-open');
        }
    });

    mainNav.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', () => {
            mainNav.classList.remove('is-open');
            menuBtn.classList.remove('is-open');
            if (menuOverlay) {
                menuOverlay.classList.remove('is-open');
            }
        });
    });

    if (menuOverlay) {
        menuOverlay.addEventListener('click', () => {
            mainNav.classList.remove('is-open');
            menuBtn.classList.remove('is-open');
            menuOverlay.classList.remove('is-open');
        });
    }

    document.addEventListener('click', (event) => {
        if (!mainNav.contains(event.target) && !menuBtn.contains(event.target)) {
            mainNav.classList.remove('is-open');
            menuBtn.classList.remove('is-open');
            if (menuOverlay) {
                menuOverlay.classList.remove('is-open');
            }
        }
    });
}

const revealTargets = document.querySelectorAll('.hero, .section, .site-footer');
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.15 });

revealTargets.forEach((target) => revealObserver.observe(target));

const accordions = document.querySelectorAll('[data-accordion]');
accordions.forEach((item) => {
    item.addEventListener('toggle', () => {
        if (!item.open) return;
        accordions.forEach((other) => {
            if (other !== item) {
                other.open = false;
            }
        });
    });
});

const pageLoader = document.getElementById('pageLoader');
if (pageLoader) {
    window.addEventListener('load', () => {
        pageLoader.classList.add('is-hidden');
    });
}

const heroTypeLine = document.querySelector('[data-typewriter]');
if (heroTypeLine) {
    const text = heroTypeLine.getAttribute('data-typewriter') || heroTypeLine.textContent.trim();
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        heroTypeLine.textContent = text;
    } else {
        heroTypeLine.textContent = '';
        let index = 0;

        const typeNextCharacter = () => {
            heroTypeLine.textContent = text.slice(0, index + 1);
            index += 1;

            if (index >= text.length) return;

            const char = text[index - 1];
            const delay = ['.', ','].includes(char) ? 420 : (char === ' ' ? 110 : 95 + Math.random() * 50);
            window.setTimeout(typeNextCharacter, delay);
        };

        window.setTimeout(typeNextCharacter, 900);
    }
}

const heroStackButtons = document.querySelectorAll('[data-hero-stack]');
const heroStackBurst = document.getElementById('heroStackBurst');
if (heroStackButtons.length && heroStackBurst) {
    let hideStackTimer = null;
    let flashTimer = null;

    const hideHeroStack = () => {
        heroStackBurst.classList.remove('is-active', 'is-flashing');
        heroStackButtons.forEach((button) => button.classList.remove('is-active'));
    };

    const showHeroStack = (button) => {
        const items = (button.getAttribute('data-hero-stack') || '')
            .split('|')
            .map((item) => item.trim())
            .filter(Boolean);

        if (!items.length) return;

        window.clearTimeout(hideStackTimer);
        window.clearTimeout(flashTimer);
        heroStackBurst.replaceChildren();

        items.forEach((item, index) => {
            const [label, href = '#projects'] = item.split('::').map((part) => part.trim());
            const rawHref = href || '#projects';
            const pill = document.createElement('a');
            pill.textContent = label;
            pill.href = rawHref;
            pill.style.setProperty('--delay', `${index * 0.045}s`);

            if (/^https?:\/\//i.test(rawHref)) {
                pill.target = '_blank';
                pill.rel = 'noopener noreferrer';
            }

            if (rawHref.startsWith('#')) {
                pill.addEventListener('click', (event) => {
                    const target = document.querySelector(rawHref);
                    if (!target) return;
                    event.preventDefault();
                    hideHeroStack();
                    smoothScrollToNode(target, 900);
                });
            }

            heroStackBurst.appendChild(pill);
        });

        heroStackButtons.forEach((item) => item.classList.toggle('is-active', item === button));
        heroStackBurst.setAttribute('aria-label', `${button.textContent.trim()}: ${items.map((item) => item.split('::')[0].trim()).join(', ')}`);
        heroStackBurst.classList.remove('is-active', 'is-flashing');
        void heroStackBurst.offsetWidth;
        heroStackBurst.classList.add('is-active', 'is-flashing');

        flashTimer = window.setTimeout(() => {
            heroStackBurst.classList.remove('is-flashing');
        }, 680);

        hideStackTimer = window.setTimeout(hideHeroStack, 5000);
    };

    heroStackButtons.forEach((button) => {
        button.addEventListener('click', () => showHeroStack(button));
    });
}

const pythonOdooChip = Array.from(document.querySelectorAll('.skill-cloud span'))
    .find((chip) => chip.textContent.trim() === 'Python / Odoo');

if (pythonOdooChip) {
    let adminTapCount = 0;
    let adminTapTimer = null;

    pythonOdooChip.addEventListener('click', () => {
        adminTapCount += 1;
        window.clearTimeout(adminTapTimer);

        if (adminTapCount >= 3) {
            window.location.href = '/admin/';
            return;
        }

        adminTapTimer = window.setTimeout(() => {
            adminTapCount = 0;
        }, 1400);
    });
}

const updateHeroScaleOnScroll = () => {
    if (window.scrollY > 36) {
        document.body.classList.add('hero-scrolled');
    } else {
        document.body.classList.remove('hero-scrolled');
    }
};

window.addEventListener('scroll', updateHeroScaleOnScroll, { passive: true });
updateHeroScaleOnScroll();

// Theme segmented control
(function() {
    const STORAGE_KEY = 'lawebdejacoto_theme';
    const DARK = 'dark';
    const LIGHT = 'light';

    const segment = document.getElementById('themeSegment');
    if (!segment) return;

    const saved = localStorage.getItem(STORAGE_KEY);
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = saved || (prefersDark ? DARK : LIGHT);

    const applyTheme = (t) => {
        document.documentElement.setAttribute('data-theme', t);
        segment.querySelectorAll('.theme-opt').forEach((opt) => {
            const checked = opt.getAttribute('data-theme-val') === t;
            opt.setAttribute('aria-checked', checked ? 'true' : 'false');
        });
    };

    applyTheme(theme);

    segment.addEventListener('click', (e) => {
        const opt = e.target.closest('.theme-opt');
        if (!opt) return;
        const val = opt.getAttribute('data-theme-val');
        if (val === document.documentElement.getAttribute('data-theme')) return;
        applyTheme(val);
        localStorage.setItem(STORAGE_KEY, val);
    });
})();

const timelinePoints = document.querySelectorAll('.timeline-point');
const timelineVisual = document.getElementById('timelineVisual');
const timelineVisualCaption = document.getElementById('timelineVisualCaption');

if (timelinePoints.length && timelineVisual) {
    const activateTimelinePoint = (point) => {
        if (!point || point.classList.contains('is-active')) return;

        timelinePoints.forEach((item) => item.classList.remove('is-active'));
        point.classList.add('is-active');

        const nextSrc = point.dataset.visualSrc;
        const nextAlt = point.dataset.visualAlt || '';
        if (!nextSrc) return;

        timelineVisual.classList.add('is-switching');
        window.setTimeout(() => {
            timelineVisual.src = nextSrc;
            timelineVisual.alt = nextAlt;
            if (timelineVisualCaption) {
                timelineVisualCaption.textContent = nextAlt;
            }
            timelineVisual.classList.remove('is-switching');
        }, 120);
    };

    const timelineObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                activateTimelinePoint(entry.target);
            }
        });
    }, { rootMargin: '-35% 0px -45% 0px', threshold: 0.1 });

    timelinePoints.forEach((point) => timelineObserver.observe(point));
}

const mediaModal = document.getElementById('mediaModal');
const mediaModalImage = document.getElementById('mediaModalImage');
const mediaModalCaption = document.getElementById('mediaModalCaption');

if (mediaModal && mediaModalImage && mediaModalCaption) {
    const openMediaModal = (src, caption) => {
        mediaModalImage.src = src;
        mediaModalImage.alt = caption;
        mediaModalCaption.textContent = caption;
        mediaModal.classList.add('is-open');
        mediaModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeMediaModal = () => {
        mediaModal.classList.remove('is-open');
        mediaModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-modal-src]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const src = trigger.getAttribute('data-modal-src');
            const caption = trigger.getAttribute('data-modal-title') || '';
            if (!src) return;
            openMediaModal(src, caption);
        });
    });

    mediaModal.querySelectorAll('[data-modal-close]').forEach((closer) => {
        closer.addEventListener('click', closeMediaModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mediaModal.classList.contains('is-open')) {
            closeMediaModal();
        }
    });
}

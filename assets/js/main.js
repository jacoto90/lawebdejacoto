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

const updateHeroScaleOnScroll = () => {
    if (window.scrollY > 36) {
        document.body.classList.add('hero-scrolled');
    } else {
        document.body.classList.remove('hero-scrolled');
    }
};

window.addEventListener('scroll', updateHeroScaleOnScroll, { passive: true });
updateHeroScaleOnScroll();

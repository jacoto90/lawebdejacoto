document.querySelectorAll('a[href^="#"]').forEach((link) => {
    link.addEventListener('click', (event) => {
        const id = link.getAttribute('href');
        if (!id || id === '#') return;
        const target = document.querySelector(id);
        if (!target) return;
        event.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

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

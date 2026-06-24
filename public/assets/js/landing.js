(function () {
    'use strict';

    if (window.AOS) {
        window.AOS.init({
            offset: 90,
            delay: 0,
            duration: 850,
            easing: 'ease-out-cubic',
            once: true,
            mirror: false,
            anchorPlacement: 'top-bottom'
        });
    }

    const navbar = document.querySelector('.landing-navbar');
    const glow = document.querySelector('.cursor-glow');
    const counters = document.querySelectorAll('[data-count]');
    const splash = document.getElementById('landingSplash');
    const canAnimatePointer = window.matchMedia('(pointer: fine)').matches
        && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let glowFrame = null;
    let glowX = 0;
    let glowY = 0;
    let splashHidden = false;

    const updateNavbar = () => {
        if (!navbar) {
            return;
        }

        navbar.classList.toggle('is-scrolled', window.scrollY > 24);
    };

    const hideSplash = () => {
        if (splashHidden) {
            return;
        }

        splashHidden = true;

        if (!splash) {
            document.body.classList.remove('landing-loading');
            return;
        }

        setTimeout(() => {
            splash.classList.add('is-hidden');
            document.body.classList.remove('landing-loading');

            setTimeout(() => {
                splash.remove();
            }, 650);
        }, 650);
    };

    const paintGlow = () => {
        if (!glow) {
            return;
        }

        glow.style.opacity = '1';
        glow.style.transform = `translate3d(${glowX - 130}px, ${glowY - 130}px, 0)`;
        glowFrame = null;
    };

    const moveGlow = (event) => {
        if (!glow || !canAnimatePointer) {
            return;
        }

        glowX = event.clientX;
        glowY = event.clientY;

        if (!glowFrame) {
            glowFrame = requestAnimationFrame(paintGlow);
        }
    };

    const animateCounter = (element) => {
        const target = Number(element.dataset.count || 0);
        const duration = 1200;
        const startedAt = performance.now();

        const tick = (time) => {
            const progress = Math.min((time - startedAt) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            element.textContent = Math.round(target * eased).toString();

            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };

        requestAnimationFrame(tick);
    };

    const counterObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            animateCounter(entry.target);
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.45 });

    counters.forEach((counter) => counterObserver.observe(counter));

    document.querySelectorAll('.landing-navbar .nav-link').forEach((link) => {
        link.addEventListener('click', () => {
            const collapse = document.querySelector('.navbar-collapse.show');

            if (collapse && window.bootstrap) {
                window.bootstrap.Collapse.getOrCreateInstance(collapse).hide();
            }
        });
    });

    window.addEventListener('scroll', updateNavbar, { passive: true });
    if (canAnimatePointer) {
        window.addEventListener('mousemove', moveGlow, { passive: true });
    }
    window.addEventListener('mouseleave', () => {
        if (glow) {
            glow.style.opacity = '0';
        }
    });
    setTimeout(hideSplash, 5000);

    updateNavbar();
})();

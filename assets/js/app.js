/**
 * CampusConnect Security Lab — Client-side JavaScript
 */

'use strict';

/* ── Navbar scroll effect ─────────────────────────────────────────────────── */
const navbar = document.getElementById('navbar');
if (navbar) {
    const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 20);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}

/* ── Mobile nav toggle ────────────────────────────────────────────────────── */
const navToggle = document.getElementById('navToggle');
const navLinks  = document.getElementById('navLinks');
if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
        navLinks.classList.toggle('open');
        navToggle.setAttribute('aria-expanded', navLinks.classList.contains('open'));
    });

    // Close on outside click
    document.addEventListener('click', e => {
        if (!navbar.contains(e.target)) navLinks.classList.remove('open');
    });
}

/* ── Smooth scroll for anchor links ──────────────────────────────────────── */
document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
        const target = document.querySelector(link.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            if (navLinks) navLinks.classList.remove('open');
        }
    });
});

/* ── Animate elements on scroll (intersection observer) ───────────────────── */
const animateOnScroll = () => {
    const els = document.querySelectorAll('[data-animate]');
    if (!els.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    els.forEach(el => observer.observe(el));
};

animateOnScroll();

/* ── Auto-dismiss flash alerts ────────────────────────────────────────────── */
document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
    const delay = parseInt(alert.dataset.autoDismiss, 10) || 5000;
    setTimeout(() => {
        alert.style.transition = 'opacity 0.5s ease, max-height 0.5s ease';
        alert.style.opacity = '0';
        alert.style.maxHeight = '0';
        alert.style.overflow = 'hidden';
        alert.style.marginBottom = '0';
        setTimeout(() => alert.remove(), 600);
    }, delay);
});

/* ── Registration form — live event selection highlight ───────────────────── */
const eventSelect = document.getElementById('event_id');
if (eventSelect) {
    const highlight = () => {
        const selected = eventSelect.value;
        document.querySelectorAll('.event-card[data-event-id]').forEach(card => {
            card.classList.toggle('selected', card.dataset.eventId === selected);
        });
    };
    eventSelect.addEventListener('change', highlight);
}

/* ── Form validation helpers ──────────────────────────────────────────────── */
const forms = document.querySelectorAll('form[data-validate]');
forms.forEach(form => {
    form.addEventListener('submit', e => {
        let valid = true;
        form.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('input-error');
                valid = false;
            } else {
                field.classList.remove('input-error');
            }
        });
        if (!valid) {
            e.preventDefault();
            const first = form.querySelector('.input-error');
            if (first) first.focus();
        }
    });
});

/* ── Counter animation for hero stats ─────────────────────────────────────── */
const animateCounters = () => {
    document.querySelectorAll('[data-count]').forEach(el => {
        const target = parseInt(el.dataset.count, 10);
        const duration = 1200;
        const step = target / (duration / 16);
        let current = 0;

        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = Math.floor(current) + (el.dataset.suffix || '');
        }, 16);
    });
};

// Fire counters when hero section is in view
const heroSection = document.querySelector('.hero');
if (heroSection) {
    const heroObs = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) {
            animateCounters();
            heroObs.disconnect();
        }
    }, { threshold: 0.3 });
    heroObs.observe(heroSection);
}

/* ── Admin sidebar active state ───────────────────────────────────────────── */
const adminLinks = document.querySelectorAll('.admin-nav-link');
adminLinks.forEach(link => {
    if (link.href === window.location.href) link.classList.add('active');
});

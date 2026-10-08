/**
 * CampusConnect Security Lab — Client-side JavaScript
 */

'use strict';

/* ── Mobile nav toggle ────────────────────────────────────────────────────── */
const navToggle = document.getElementById('navToggle');
const navLinks  = document.getElementById('navLinks');

if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
        const open = navLinks.classList.toggle('open');
        navToggle.setAttribute('aria-expanded', String(open));
    });

    document.addEventListener('click', e => {
        const navbar = document.getElementById('navbar');
        if (navbar && !navbar.contains(e.target)) {
            navLinks.classList.remove('open');
            navToggle.setAttribute('aria-expanded', 'false');
        }
    });
}

/* ── Smooth scroll for anchor links ──────────────────────────────────────── */
document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
        const id = link.getAttribute('href');
        const target = document.querySelector(id);
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            if (navLinks) navLinks.classList.remove('open');
        }
    });
});

/* ── Auto-dismiss alerts ──────────────────────────────────────────────────── */
document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
    const delay = parseInt(alert.dataset.autoDismiss, 10) || 5000;
    setTimeout(() => {
        alert.style.transition = 'opacity .4s ease';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 450);
    }, delay);
});

/* ── Registration form event highlight ────────────────────────────────────── */
const eventSelect = document.getElementById('event_id');
if (eventSelect) {
    eventSelect.addEventListener('change', () => {
        const selected = eventSelect.value;
        document.querySelectorAll('.event-card[data-event-id]').forEach(card => {
            card.classList.toggle('selected', card.dataset.eventId === selected);
        });
    });
}

/* ── Basic form validation ────────────────────────────────────────────────── */
document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', e => {
        let valid = true;
        form.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--error)';
                valid = false;
            } else {
                field.style.borderColor = '';
            }
        });
        if (!valid) {
            e.preventDefault();
            const first = form.querySelector('[required]:invalid, [required][style*="error"]');
            if (first) first.focus();
        }
    });
});

/* ── Admin sidebar active state ───────────────────────────────────────────── */
document.querySelectorAll('.admin-nav-link').forEach(link => {
    if (link.href && link.href === window.location.href) {
        link.classList.add('active');
    }
});

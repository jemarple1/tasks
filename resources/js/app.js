const SWIPE_THRESHOLD = 72;

function initTabs() {
    const tabs = document.querySelectorAll('[data-tab]');
    const panels = document.querySelectorAll('[data-panel]');
    const indicator = document.querySelector('[data-tab-indicator]');

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;

            tabs.forEach((t) => {
                const active = t.dataset.tab === target;
                t.classList.toggle('text-garden-text', active);
                t.classList.toggle('font-semibold', active);
                t.classList.toggle('text-garden-muted', !active);
            });

            panels.forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.panel !== target);
            });

            if (indicator) {
                indicator.style.transform = target === 'longterm' ? 'translateX(100%)' : 'translateX(0)';
            }
        });
    });
}

function initModal() {
    const backdrop = document.getElementById('add-modal');
    const openBtn = document.getElementById('fab-add');
    const closeBtn = document.getElementById('modal-close');
    const form = document.getElementById('add-task-form');

    if (!backdrop || !openBtn) return;

    const open = () => {
        backdrop.classList.add('open');
        document.body.classList.add('modal-open');
        setTimeout(() => {
            backdrop.querySelector('input[name="title"]')?.focus();
        }, 100);
    };

    const close = () => {
        backdrop.classList.remove('open');
        document.body.classList.remove('modal-open');
        form?.reset();
    };

    openBtn.addEventListener('click', (e) => {
        e.preventDefault();
        open();
    });

    closeBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        close();
    });

    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) close();
    });
}

function plantFlowers(flowers) {
    const bed = document.getElementById('garden-bed');
    if (!bed || !flowers?.length) return;

    flowers.forEach((flower, index) => {
        const el = document.createElement('span');
        el.className = 'garden-flower raining';
        el.textContent = flower.emoji;
        el.style.left = `${flower.position_x}%`;
        el.dataset.flowerId = flower.id;
        el.style.animationDelay = `${index * 0.08}s`;
        bed.appendChild(el);

        setTimeout(() => el.classList.remove('raining'), 1000 + index * 80);
    });
}

function initSwipeActions() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('[data-task]').forEach((wrapper) => {
        const content = wrapper.querySelector('.task-swipe-content');
        if (!content) return;

        let startX = 0;
        let currentX = 0;
        let dragging = false;

        const reset = () => {
            content.style.transform = '';
            content.classList.remove('swiping', 'animating-out');
        };

        const onStart = (x) => {
            startX = x;
            currentX = 0;
            dragging = true;
            content.classList.add('swiping');
        };

        const onMove = (x) => {
            if (!dragging) return;
            currentX = x - startX;
            const clamped = Math.max(-140, Math.min(140, currentX));
            content.style.transform = `translateX(${clamped}px)`;
        };

        const onEnd = async () => {
            if (!dragging) return;
            dragging = false;
            content.classList.remove('swiping');

            if (currentX <= -SWIPE_THRESHOLD) {
                content.classList.add('animating-out');
                content.style.transform = 'translateX(-110%)';
                content.style.opacity = '0';

                try {
                    const response = await fetch(wrapper.dataset.completeUrl, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            Accept: 'application/json',
                        },
                    });
                    const data = await response.json();
                    plantFlowers(data.flowers);
                    setTimeout(() => wrapper.remove(), 250);
                } catch {
                    content.style.opacity = '';
                    reset();
                }
                return;
            }

            if (currentX >= SWIPE_THRESHOLD) {
                try {
                    const response = await fetch(wrapper.dataset.refreshUrl, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            Accept: 'application/json',
                        },
                    });
                    const data = await response.json();
                    const expiryEl = wrapper.querySelector('[data-expiry]');
                    if (expiryEl && data.days_remaining !== undefined) {
                        expiryEl.textContent = `${data.days_remaining}d left`;
                        expiryEl.classList.add('expiry-refreshed');
                    }
                } catch {
                    //
                }
            }

            reset();
        };

        content.addEventListener('touchstart', (e) => onStart(e.touches[0].clientX), { passive: true });
        content.addEventListener('touchmove', (e) => onMove(e.touches[0].clientX), { passive: true });
        content.addEventListener('touchend', onEnd);

        content.addEventListener('mousedown', (e) => onStart(e.clientX));
        document.addEventListener('mousemove', (e) => {
            if (dragging) onMove(e.clientX);
        });
        document.addEventListener('mouseup', () => {
            if (dragging) onEnd();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initModal();
    initSwipeActions();
});

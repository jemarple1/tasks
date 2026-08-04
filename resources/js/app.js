const SWIPE_THRESHOLD = 80;

function initTabs() {
    const tabs = document.querySelectorAll('[data-tab]');
    const panels = document.querySelectorAll('[data-panel]');
    const indicator = document.querySelector('[data-tab-indicator]');

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;

            tabs.forEach((t) => {
                t.classList.toggle('text-sky-deep', t.dataset.tab === target);
                t.classList.toggle('text-sky-muted', t.dataset.tab !== target);
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
        backdrop.querySelector('input[name="title"]')?.focus();
    };

    const close = () => {
        backdrop.classList.remove('open');
        form?.reset();
    };

    openBtn.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) close();
    });
}

function initSwipeToArchive() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('[data-task]').forEach((wrapper) => {
        const content = wrapper.querySelector('.task-swipe-content');
        if (!content) return;

        let startX = 0;
        let currentX = 0;
        let dragging = false;

        const onStart = (x) => {
            startX = x;
            currentX = 0;
            dragging = true;
            content.classList.add('swiping');
        };

        const onMove = (x) => {
            if (!dragging) return;
            currentX = Math.min(0, x - startX);
            content.style.transform = `translateX(${currentX}px)`;
        };

        const onEnd = async () => {
            if (!dragging) return;
            dragging = false;
            content.classList.remove('swiping');

            if (Math.abs(currentX) >= SWIPE_THRESHOLD) {
                content.classList.add('archiving');
                content.style.transform = 'translateX(-100%)';
                content.style.opacity = '0';

                const url = wrapper.dataset.archiveUrl;
                try {
                    await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            Accept: 'application/json',
                        },
                    });
                    setTimeout(() => wrapper.remove(), 250);
                } catch {
                    content.style.transform = '';
                    content.style.opacity = '';
                    content.classList.remove('archiving');
                }
            } else {
                content.style.transform = '';
            }
        };

        content.addEventListener('touchstart', (e) => onStart(e.touches[0].clientX), { passive: true });
        content.addEventListener('touchmove', (e) => onMove(e.touches[0].clientX), { passive: true });
        content.addEventListener('touchend', onEnd);

        content.addEventListener('mousedown', (e) => onStart(e.clientX));
        content.addEventListener('mousemove', (e) => {
            if (dragging) onMove(e.clientX);
        });
        content.addEventListener('mouseup', onEnd);
        content.addEventListener('mouseleave', () => {
            if (dragging) onEnd();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initModal();
    initSwipeToArchive();
});

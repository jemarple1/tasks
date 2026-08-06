const SWIPE_THRESHOLD = 72;
const TAP_MOVE_THRESHOLD = 8;

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

function initTaskModal() {
    const backdrop = document.getElementById('task-modal');
    const openBtn = document.getElementById('fab-add');
    const closeBtn = document.getElementById('modal-close');
    const form = document.getElementById('task-form');
    const methodInput = document.getElementById('task-form-method');
    const modalTitle = document.getElementById('task-modal-title');
    const submitBtn = document.getElementById('task-form-submit');
    const titleInput = document.getElementById('task-title');
    const notesInput = document.getElementById('task-notes');
    const recurrenceSelect = document.getElementById('task-recurrence');
    const recurrenceUntil = document.getElementById('task-recurrence-until');
    const assigneeField = document.getElementById('assignee-field');

    if (!backdrop || !openBtn || !form) return;

    const storeUrl = form.action;

    const setCategory = (category) => {
        form.querySelectorAll('input[name="category"]').forEach((radio) => {
            radio.checked = radio.value === category;
        });
    };

    const open = () => {
        backdrop.classList.add('open');
        document.body.classList.add('modal-open');
        setTimeout(() => titleInput?.focus(), 100);
    };

    const close = () => {
        backdrop.classList.remove('open');
        document.body.classList.remove('modal-open');
        form.reset();
        form.action = storeUrl;
        methodInput.value = '';
        methodInput.disabled = true;
        modalTitle.textContent = 'New task';
        submitBtn.textContent = 'Save task';
        setCategory('immediate');
        if (recurrenceSelect) recurrenceSelect.value = 'none';
        if (recurrenceUntil) recurrenceUntil.value = '';
        if (assigneeField) assigneeField.classList.remove('hidden');
        if (document.getElementById('recurrence-field')) document.getElementById('recurrence-field').classList.remove('hidden');
    };

    const openAdd = () => {
        close();
        open();
    };

    const openEdit = (wrapper) => {
        form.action = wrapper.dataset.updateUrl;
        methodInput.value = 'PATCH';
        methodInput.disabled = false;
        modalTitle.textContent = 'Edit task';
        submitBtn.textContent = 'Save changes';
        titleInput.value = wrapper.dataset.taskTitle || '';
        notesInput.value = wrapper.dataset.taskNotes || '';
        setCategory(wrapper.dataset.taskCategory || 'immediate');
        if (recurrenceSelect) recurrenceSelect.value = wrapper.dataset.taskRecurrence || 'none';
        if (recurrenceUntil) recurrenceUntil.value = wrapper.dataset.taskRecurrenceUntil || '';
        if (assigneeField) assigneeField.classList.add('hidden');
        if (document.getElementById('recurrence-field')) document.getElementById('recurrence-field').classList.remove('hidden');
        open();
    };

    openBtn.addEventListener('click', (e) => {
        e.preventDefault();
        openAdd();
    });

    closeBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        close();
    });

    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) close();
    });

    window.openTaskEdit = openEdit;
}

function growTree(size) {
    const tree = document.getElementById('garden-tree');
    if (!tree || !size) return;
    tree.style.fontSize = size;
    tree.classList.add('growing');
    setTimeout(() => tree.classList.remove('growing'), 500);
}

function initSwipeActions() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('[data-task]').forEach((wrapper) => {
        const content = wrapper.querySelector('.task-swipe-content');
        if (!content) return;

        let startX = 0;
        let startY = 0;
        let currentX = 0;
        let dragging = false;
        let moved = false;

        const reset = () => {
            content.style.transform = '';
            content.classList.remove('swiping', 'animating-out');
        };

        const onStart = (x, y) => {
            startX = x;
            startY = y;
            currentX = 0;
            dragging = true;
            moved = false;
            content.classList.add('swiping');
        };

        const onMove = (x, y) => {
            if (!dragging) return;
            currentX = x - startX;
            if (Math.abs(currentX) > TAP_MOVE_THRESHOLD || Math.abs(y - startY) > TAP_MOVE_THRESHOLD) {
                moved = true;
            }
            content.style.transform = `translateX(${Math.max(-140, Math.min(140, currentX))}px)`;
        };

        const onEnd = async () => {
            if (!dragging) return;
            dragging = false;
            content.classList.remove('swiping');

            if (!moved && Math.abs(currentX) < TAP_MOVE_THRESHOLD) {
                reset();
                window.openTaskEdit?.(wrapper);
                return;
            }

            if (currentX <= -SWIPE_THRESHOLD) {
                content.classList.add('animating-out');
                content.style.transform = 'translateX(-110%)';
                content.style.opacity = '0';

                try {
                    const response = await fetch(wrapper.dataset.completeUrl, {
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                    });
                    const data = await response.json();
                    growTree(data.tree_size);
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
                        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                    });
                    const data = await response.json();
                    const expiryEl = wrapper.querySelector('[data-expiry]');
                    if (expiryEl && data.days_remaining !== undefined) {
                        expiryEl.textContent = `${data.days_remaining}d`;
                        expiryEl.classList.add('expiry-refreshed');
                    }
                } catch {
                    //
                }
            }

            reset();
        };

        content.addEventListener('touchstart', (e) => onStart(e.touches[0].clientX, e.touches[0].clientY), { passive: true });
        content.addEventListener('touchmove', (e) => onMove(e.touches[0].clientX, e.touches[0].clientY), { passive: true });
        content.addEventListener('touchend', onEnd);
        content.addEventListener('mousedown', (e) => onStart(e.clientX, e.clientY));
        document.addEventListener('mousemove', (e) => { if (dragging) onMove(e.clientX, e.clientY); });
        document.addEventListener('mouseup', () => { if (dragging) onEnd(); });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initTaskModal();
    initSwipeActions();
});

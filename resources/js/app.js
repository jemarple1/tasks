const SWIPE_THRESHOLD = 72;

function initTaskModal() {
    const backdrop = document.getElementById('task-modal');
    const openBtns = [document.getElementById('nav-add-task'), document.getElementById('fab-add')].filter(Boolean);
    const closeBtn = document.getElementById('modal-close');
    const form = document.getElementById('task-form');
    const methodInput = document.getElementById('task-form-method');
    const modalTitle = document.getElementById('task-modal-title');
    const submitBtn = document.getElementById('task-form-submit');
    const titleInput = document.getElementById('task-title');
    const notesInput = document.getElementById('task-notes');
    const dueAtInput = document.getElementById('task-due-at');
    const categorySelect = document.getElementById('task-category-id');
    const recurrenceSelect = document.getElementById('task-recurrence');
    const recurrenceUntil = document.getElementById('task-recurrence-until');
    const assigneeField = document.getElementById('assignee-field');
    const assigneeSelect = document.getElementById('assignee_username');
    const showOnMyListField = document.getElementById('show-on-my-list-field');

    if (!backdrop || !form || openBtns.length === 0) return;

    const storeUrl = form.action;

    const syncAssigneeExtras = () => {
        const hasAssignee = assigneeSelect && assigneeSelect.value !== '';
        if (showOnMyListField) showOnMyListField.classList.toggle('hidden', !hasAssignee);
    };

    assigneeSelect?.addEventListener('change', syncAssigneeExtras);

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
        if (recurrenceSelect) recurrenceSelect.value = 'none';
        if (recurrenceUntil) recurrenceUntil.value = '';
        if (dueAtInput) dueAtInput.value = '';
        if (assigneeField) assigneeField.classList.remove('hidden');
        syncAssigneeExtras();
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
        if (dueAtInput) dueAtInput.value = wrapper.dataset.taskDueAt || '';
        if (categorySelect) categorySelect.value = wrapper.dataset.taskCategoryId || '';
        if (recurrenceSelect) recurrenceSelect.value = wrapper.dataset.taskRecurrence || 'none';
        if (recurrenceUntil) recurrenceUntil.value = wrapper.dataset.taskRecurrenceUntil || '';
        if (assigneeField) assigneeField.classList.add('hidden');
        open();
    };

    openBtns.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openAdd();
        });
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

function initCalendarModal() {
    const backdrop = document.getElementById('calendar-event-modal');
    const form = document.getElementById('calendar-event-form');
    const closeBtn = document.getElementById('calendar-modal-close');

    if (!backdrop || !form) return;

    const open = (block) => {
        form.action = block.dataset.updateUrl;
        document.getElementById('calendar-event-title').value = block.dataset.eventTitle || '';
        document.getElementById('calendar-event-notes').value = block.dataset.eventNotes || '';
        document.getElementById('calendar-event-starts').value = block.dataset.eventStarts || '';
        document.getElementById('calendar-event-ends').value = block.dataset.eventEnds || '';
        document.getElementById('calendar-event-recurrence').value = block.dataset.eventRecurrence || 'none';
        document.getElementById('calendar-event-recurrence-until').value = block.dataset.eventRecurrenceUntil || '';

        const tagged = (block.dataset.eventTagged || '').split(',').filter(Boolean);
        form.querySelectorAll('.calendar-tag-checkbox').forEach((cb) => {
            cb.checked = tagged.includes(cb.value);
        });

        backdrop.classList.add('open');
        document.body.classList.add('modal-open');
    };

    const close = () => {
        backdrop.classList.remove('open');
        document.body.classList.remove('modal-open');
    };

    document.querySelectorAll('[data-calendar-event]').forEach((block) => {
        block.addEventListener('click', (e) => {
            if (e.target.closest('form')) return;
            open(block);
        });
    });

    closeBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        close();
    });

    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) close();
    });
}

function growTree(size) {
    const tree = document.getElementById('garden-tree');
    if (!tree || !size) return;
    tree.style.fontSize = `max(2.75rem, ${size})`;
    tree.classList.add('growing');
    setTimeout(() => tree.classList.remove('growing'), 500);
}

function initTaskActions() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('[data-task]').forEach((wrapper) => {
        const card = wrapper.querySelector('.task-card');
        const editBtn = wrapper.querySelector('.task-edit-area');
        const completeBtn = wrapper.querySelector('.task-complete-btn');

        if (!card || !editBtn || !completeBtn) return;

        let startX = 0;
        let startY = 0;
        let currentX = 0;
        let dragging = false;

        const reset = () => {
            card.style.transform = '';
            card.classList.remove('swiping', 'animating-out');
        };

        completeBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            card.classList.add('animating-out');
            card.style.transform = 'translateX(110%)';
            card.style.opacity = '0';

            try {
                const response = await fetch(wrapper.dataset.completeUrl, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                });
                const data = await response.json();
                growTree(data.tree_size);
                setTimeout(() => wrapper.remove(), 250);
            } catch {
                card.style.opacity = '';
                reset();
            }
        });

        editBtn.addEventListener('click', () => {
            if (editBtn.hasAttribute('data-no-edit')) return;
            window.openTaskEdit?.(wrapper);
        });

        const onStart = (x, y) => {
            if (y > window.innerHeight - 120) return;
            startX = x;
            startY = y;
            currentX = 0;
            dragging = true;
            card.classList.add('swiping');
        };

        const onMove = (x, y) => {
            if (!dragging) return;
            if (Math.abs(y - startY) > Math.abs(x - startX)) return;
            currentX = x - startX;
            card.style.transform = `translateX(${Math.max(-140, Math.min(140, currentX))}px)`;
        };

        const onEnd = async () => {
            if (!dragging) return;
            dragging = false;
            card.classList.remove('swiping');

            if (currentX <= -SWIPE_THRESHOLD) {
                try {
                    const response = await fetch(wrapper.dataset.snoozeUrl, {
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                    });
                    const data = await response.json();
                    const daysEl = wrapper.querySelector('.task-card-meta-days');
                    if (daysEl && data.days_remaining !== undefined) {
                        daysEl.textContent = `${data.days_remaining}d left`;
                        daysEl.classList.add('expiry-refreshed');
                    }
                } catch {
                    //
                }
            }

            if (currentX >= SWIPE_THRESHOLD) {
                try {
                    const response = await fetch(wrapper.dataset.refreshUrl, {
                        method: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                    });
                    const data = await response.json();
                    const daysEl = wrapper.querySelector('.task-card-meta-days');
                    if (daysEl && data.days_remaining !== undefined) {
                        daysEl.textContent = `${data.days_remaining}d left`;
                        daysEl.classList.add('expiry-refreshed');
                    }
                } catch {
                    //
                }
            }

            reset();
        };

        card.addEventListener('touchstart', (e) => onStart(e.touches[0].clientX, e.touches[0].clientY), { passive: true });
        card.addEventListener('touchmove', (e) => onMove(e.touches[0].clientX, e.touches[0].clientY), { passive: true });
        card.addEventListener('touchend', onEnd);
        card.addEventListener('mousedown', (e) => {
            if (e.target.closest('.task-complete-btn')) return;
            onStart(e.clientX, e.clientY);
        });
        document.addEventListener('mousemove', (e) => { if (dragging) onMove(e.clientX, e.clientY); });
        document.addEventListener('mouseup', () => { if (dragging) onEnd(); });
    });
}

function initCalendarAddPanel() {
    const toggle = document.getElementById('calendar-add-toggle');
    const panel = document.getElementById('calendar-add-panel');
    if (!toggle || !panel) return;

    toggle.addEventListener('click', () => {
        panel.classList.toggle('hidden');
    });
}

function initGrocery() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('[data-grocery-item]').forEach((row) => {
        const btn = row.querySelector('.grocery-check');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            row.classList.add('opacity-50');
            try {
                await fetch(row.dataset.completeUrl, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                });
                row.remove();
            } catch {
                row.classList.remove('opacity-50');
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTaskModal();
    initCalendarModal();
    initCalendarAddPanel();
    initTaskActions();
    initGrocery();
});

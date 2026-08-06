@if(auth()->check() && ($navCategories ?? collect())->isNotEmpty())
<div id="task-modal" class="modal-backdrop fixed inset-0 z-50 bg-garden-deep/50">
    <div class="modal-fullscreen fixed inset-0 flex flex-col bg-white pt-safe">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
            <h2 id="task-modal-title" class="font-title text-2xl font-bold text-garden-text">New task</h2>
            <button id="modal-close" type="button" class="font-sans text-lg font-medium text-garden-accent">Cancel</button>
        </div>
        <form id="task-form" action="{{ route('tasks.store') }}" method="POST" class="flex flex-1 flex-col overflow-y-auto px-5 py-5">
            @csrf
            <input type="hidden" id="task-form-method" name="_method" value="" disabled>
            <input type="hidden" id="task-filter-category" name="filter_category" value="{{ request('category') }}">
            <div class="mb-4">
                <label for="task-title" class="mb-1.5 block font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Title</label>
                <input id="task-title" type="text" name="title" required maxlength="255" autocomplete="off" class="input-field w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-garden-text outline-none focus:border-garden-accent">
            </div>
            <div class="mb-4">
                <label for="task-notes" class="mb-1.5 block font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Notes</label>
                <textarea id="task-notes" name="notes" rows="3" maxlength="2000" class="input-field w-full resize-none rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-garden-text outline-none focus:border-garden-accent"></textarea>
            </div>
            <div class="mb-4">
                <label for="task-due-at" class="mb-1.5 block font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Due date <span class="font-normal normal-case">(optional)</span></label>
                <input type="date" id="task-due-at" name="due_at" class="input-field w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-garden-text outline-none focus:border-garden-accent">
            </div>
            <div class="mb-4" id="assignee-field">
                <label for="assignee_username" class="mb-1.5 block font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Assign to</label>
                <select id="assignee_username" name="assignee_username" class="input-field w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-garden-text outline-none focus:border-garden-accent">
                    <option value="">Me ({{ '@'.auth()->user()->username }})</option>
                    @foreach (auth()->user()->connectedUsers() as $person)
                        <option value="{{ $person->username }}">{{ '@'.$person->username }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="task-category-id" class="mb-1.5 block font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Category</label>
                <select id="task-category-id" name="task_category_id" required class="input-field w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-garden-text outline-none focus:border-garden-accent">
                    @foreach ($navCategories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4" id="recurrence-field">
                <p class="mb-2 font-sans text-sm font-semibold uppercase tracking-wide text-garden-muted">Repeat</p>
                <select id="task-recurrence" name="recurrence" class="input-field mb-2 w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-garden-text outline-none focus:border-garden-accent">
                    <option value="none">Does not repeat</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
                <input type="date" id="task-recurrence-until" name="recurrence_until" class="input-field w-full rounded-xl border-2 border-slate-300 bg-slate-50 px-4 py-3 text-garden-text outline-none focus:border-garden-accent">
            </div>
            <button id="task-form-submit" type="submit" class="mt-auto w-full rounded-xl bg-garden-accent py-3.5 font-sans text-lg font-semibold text-white shadow-lg">Save task</button>
        </form>
    </div>
</div>
@endif

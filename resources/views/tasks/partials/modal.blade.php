@if(auth()->check() && ($navCategories ?? collect())->isNotEmpty())
<div id="task-modal" class="modal-backdrop fixed inset-0 z-50 bg-garden-deep/50">
    <div class="modal-fullscreen fixed inset-0 flex flex-col bg-white pt-safe">
        <div class="modal-header">
            <h2 id="task-modal-title" class="modal-title">New task</h2>
            <button id="modal-close" type="button" class="font-sans text-[15px] font-semibold text-garden-accent">Cancel</button>
        </div>
        <form id="task-form" action="{{ route('tasks.store') }}" method="POST" class="form-stack flex flex-1 flex-col overflow-y-auto px-4 py-5">
            @csrf
            <input type="hidden" id="task-form-method" name="_method" value="" disabled>
            <input type="hidden" id="task-filter-category" name="filter_category" value="{{ request('category') }}">
            <div>
                <label for="task-title" class="field-label">Title</label>
                <input id="task-title" type="text" name="title" required maxlength="255" autocomplete="off" class="input-field">
            </div>
            <div>
                <label for="task-notes" class="field-label">Notes</label>
                <textarea id="task-notes" name="notes" rows="3" maxlength="2000" class="input-field resize-none"></textarea>
            </div>
            <div>
                <label for="task-category-id" class="field-label">Category</label>
                <select id="task-category-id" name="task_category_id" required class="input-field">
                    @foreach ($navCategories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="task-due-at" class="field-label">Due date <span class="font-medium normal-case tracking-normal text-garden-faint">optional</span></label>
                <input type="date" id="task-due-at" name="due_at" class="input-field">
            </div>
            <div id="assignee-field">
                <label for="assignee_username" class="field-label">Assign to</label>
                <select id="assignee_username" name="assignee_username" class="input-field">
                    <option value="">Me ({{ '@'.auth()->user()->username }})</option>
                    @foreach (auth()->user()->connectedUsers() as $person)
                        <option value="{{ $person->username }}">{{ '@'.$person->username }}</option>
                    @endforeach
                </select>
            </div>
            <div class="hidden" id="show-on-my-list-field">
                <label class="checkbox-chip w-full justify-start rounded-xl px-4 py-3">
                    <input type="checkbox" name="show_on_my_list" value="1" class="h-4 w-4 rounded border-slate-300 text-garden-accent">
                    <span class="font-sans text-sm text-garden-text">Also show on my task list</span>
                </label>
            </div>
            <div id="recurrence-field">
                <label for="task-recurrence" class="field-label">Repeat</label>
                <select id="task-recurrence" name="recurrence" class="input-field">
                    <option value="none">Does not repeat</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
                <input type="date" id="task-recurrence-until" name="recurrence_until" class="input-field mt-2">
            </div>
            <button id="task-form-submit" type="submit" class="btn-primary mt-auto w-full">Save task</button>
        </form>
    </div>
</div>
@endif

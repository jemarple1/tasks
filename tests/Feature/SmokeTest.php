<?php

namespace Tests\Feature;

use App\Models\CalendarEvent;
use App\Models\GroceryItem;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\User;
use App\Models\UserConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $username): User
    {
        return User::factory()->create([
            'username' => $username,
            'tree_emoji' => '🌳',
        ]);
    }

    public function test_every_authenticated_page_renders(): void
    {
        $me = $this->makeUser('me');
        $friend = $this->makeUser('friend');
        UserConnection::create(['user_id' => $me->id, 'connected_user_id' => $friend->id]);

        $category = TaskCategory::create([
            'user_id' => $me->id,
            'name' => 'Immediate',
            'color' => '#2563eb',
            'sort_order' => 1,
        ]);

        Task::create([
            'user_id' => $me->id,
            'created_by_user_id' => $me->id,
            'task_category_id' => $category->id,
            'title' => 'Water the plants',
            'recurrence' => 'none',
            'expires_at' => now()->addDays(5),
        ]);

        foreach ([$me, $friend] as $owner) {
            CalendarEvent::create([
                'user_id' => $owner->id,
                'title' => $owner->username.' event',
                'starts_at' => now(),
                'recurrence' => 'none',
            ]);

            GroceryItem::create([
                'user_id' => $owner->id,
                'name' => $owner->username.' milk',
                'recurrence' => 'none',
            ]);
        }

        $this->actingAs($me);

        $urls = [
            '/',
            '/complete',
            '/calendar',
            '/calendar?view=week',
            '/calendar?view=day',
            '/grocery',
            '/settings',
            '/notifications',
            '/weather',
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/garden')->assertRedirect('/settings');
    }

    public function test_grocery_and_calendar_pool_across_the_circle(): void
    {
        $me = $this->makeUser('me');
        $friend = $this->makeUser('friend');
        $stranger = $this->makeUser('stranger');
        UserConnection::create(['user_id' => $friend->id, 'connected_user_id' => $me->id]);

        GroceryItem::create(['user_id' => $friend->id, 'name' => 'shared eggs', 'recurrence' => 'none']);
        GroceryItem::create(['user_id' => $stranger->id, 'name' => 'private bread', 'recurrence' => 'none']);

        CalendarEvent::create([
            'user_id' => $friend->id,
            'title' => 'shared standup',
            'starts_at' => now(),
            'recurrence' => 'none',
        ]);
        CalendarEvent::create([
            'user_id' => $stranger->id,
            'title' => 'private dentist',
            'starts_at' => now(),
            'recurrence' => 'none',
        ]);

        $this->actingAs($me);

        $this->get('/grocery')
            ->assertOk()
            ->assertSee('shared eggs')
            ->assertDontSee('private bread');

        $this->get('/calendar?view=day')
            ->assertOk()
            ->assertSee('shared standup')
            ->assertDontSee('private dentist');
    }

    public function test_circle_members_can_check_off_shared_items_but_outsiders_cannot(): void
    {
        $me = $this->makeUser('me');
        $friend = $this->makeUser('friend');
        $stranger = $this->makeUser('stranger');
        UserConnection::create(['user_id' => $me->id, 'connected_user_id' => $friend->id]);

        $shared = GroceryItem::create(['user_id' => $friend->id, 'name' => 'eggs', 'recurrence' => 'none']);
        $private = GroceryItem::create(['user_id' => $stranger->id, 'name' => 'bread', 'recurrence' => 'none']);

        $this->actingAs($me);

        $this->patch("/grocery/{$shared->id}/complete")->assertOk();
        $this->assertNull(GroceryItem::find($shared->id));

        $this->patch("/grocery/{$private->id}/complete")->assertNotFound();
        $this->assertNotNull(GroceryItem::find($private->id));
    }

    public function test_users_cannot_delete_calendar_events_they_do_not_own(): void
    {
        $me = $this->makeUser('me');
        $friend = $this->makeUser('friend');
        UserConnection::create(['user_id' => $me->id, 'connected_user_id' => $friend->id]);

        $event = CalendarEvent::create([
            'user_id' => $friend->id,
            'title' => 'their event',
            'starts_at' => now(),
            'recurrence' => 'none',
        ]);

        $this->actingAs($me);

        $this->delete("/calendar/{$event->id}")->assertNotFound();
        $this->assertNotNull(CalendarEvent::find($event->id));
    }
}

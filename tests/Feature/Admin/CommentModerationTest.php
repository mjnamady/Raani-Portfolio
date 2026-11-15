<?php

use App\Http\Controllers\Admin\CommentController;
use App\Models\BannerSection;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
    $this->admin = User::factory()->admin()->create(['email' => 'owner@example.com']);
});

// ---------- Access ----------

test('guests are redirected from every moderation route', function () {
    $comment = Comment::factory()->create();

    $this->get(route('admin.comments.index'))->assertRedirect(route('login'));
    $this->patch(route('admin.comments.approve', $comment))->assertRedirect(route('login'));
    $this->patch(route('admin.comments.unapprove', $comment))->assertRedirect(route('login'));
    $this->post(route('admin.comments.reply', $comment), ['body' => 'Hi'])->assertRedirect(route('login'));
    $this->delete(route('admin.comments.destroy', $comment))->assertRedirect(route('login'));

    expect($comment->fresh()->status)->toBe(Comment::STATUS_PENDING);
});

test('non-admin users can not moderate', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create();

    $this->actingAs($user)->get(route('admin.comments.index'))->assertForbidden();
    $this->actingAs($user)->patch(route('admin.comments.approve', $comment))->assertForbidden();
    $this->actingAs($user)->post(route('admin.comments.reply', $comment), ['body' => 'Hi'])->assertForbidden();
    $this->actingAs($user)->delete(route('admin.comments.destroy', $comment))->assertForbidden();

    expect($comment->fresh()->status)->toBe(Comment::STATUS_PENDING)
        ->and(Comment::query()->count())->toBe(1);
});

test('the policy only allows the admin', function () {
    $comment = Comment::factory()->create();
    $user = User::factory()->create();

    expect($this->admin->can('update', $comment))->toBeTrue()
        ->and($this->admin->can('viewAny', Comment::class))->toBeTrue()
        ->and($user->can('update', $comment))->toBeFalse()
        ->and($user->can('delete', $comment))->toBeFalse()
        ->and($user->can('viewAny', Comment::class))->toBeFalse();
});

// ---------- The queue ----------

test('the queue opens on pending comments with counts per tab', function () {
    Comment::factory()->create(['author_name' => 'Waiting Person', 'author_email' => 'wait@example.com']);
    Comment::factory()->approved()->create(['author_name' => 'Approved Person']);

    $this->actingAs($this->admin)->get(route('admin.comments.index'))
        ->assertOk()
        ->assertSee('Waiting Person')
        ->assertSee('mailto:wait@example.com', false)
        ->assertDontSee('Approved Person')
        ->assertSeeInOrder(['Pending', '1', 'Approved', '1', 'All', '2']);

    $this->actingAs($this->admin)->get(route('admin.comments.index', ['status' => 'approved']))
        ->assertSee('Approved Person')->assertDontSee('Waiting Person');

    $this->actingAs($this->admin)->get(route('admin.comments.index', ['status' => 'all']))
        ->assertSee('Approved Person')->assertSee('Waiting Person');
});

test('the queue can be filtered by post', function () {
    $post = Post::factory()->published()->withThemeImages()->create(['title' => 'Filtered Post']);
    Comment::factory()->create(['post_id' => $post->id, 'author_name' => 'On This Post']);
    Comment::factory()->create(['author_name' => 'On Another Post']);

    $this->actingAs($this->admin)->get(route('admin.comments.index', ['post' => $post->id]))
        ->assertOk()
        ->assertSee('Filtered Post')
        ->assertSee('On This Post')
        ->assertDontSee('On Another Post');

    $this->actingAs($this->admin)->get(route('admin.comments.index', ['post' => ['x']]))->assertOk()->assertSee('On Another Post');
});

test('the queue is paginated and an empty queue says so', function () {
    $this->actingAs($this->admin)->get(route('admin.comments.index'))->assertSee('All caught up');

    Comment::factory()->count(CommentController::PER_PAGE + 1)->create();

    $this->actingAs($this->admin)->get(route('admin.comments.index'))->assertSee('page=2', false);
});

test('the sidebar shows the pending count', function () {
    Comment::factory()->count(2)->create();
    Comment::factory()->approved()->create();

    $this->actingAs($this->admin)->get(route('admin.posts.index'))
        ->assertSee('href="'.route('admin.comments.index').'"', false)
        ->assertSee('<span class="badge badge-danger sidebar-count" aria-label="2 pending">2</span>', false);
});

// ---------- Actions ----------

test('the admin can approve and unapprove a comment', function () {
    $comment = Comment::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.comments.approve', $comment))
        ->assertSessionHas('status', 'The comment is now shown on the post.');
    expect($comment->fresh())->status->toBe(Comment::STATUS_APPROVED)->approved_at->not->toBeNull();

    $this->actingAs($this->admin)->patch(route('admin.comments.unapprove', $comment))
        ->assertSessionHas('status', 'The comment is hidden again and back in the pending list.');
    expect($comment->fresh())->status->toBe(Comment::STATUS_PENDING)->approved_at->toBeNull();
});

test('the owner reply is approved, marked as the author and approves the comment it answers', function () {
    BannerSection::query()->create(BannerSection::defaults());
    $comment = Comment::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.comments.reply', $comment), ['body' => "  Thanks!\r\nSee you.  "])
        ->assertSessionHas('status', 'Your reply has been posted.');

    $reply = Comment::query()->where('is_owner', true)->firstOrFail();

    expect($reply)
        ->parent_id->toBe($comment->id)
        ->post_id->toBe($comment->post_id)
        ->status->toBe(Comment::STATUS_APPROVED)
        ->author_name->toBe('Brooklyn Simmons')
        ->author_email->toBe('owner@example.com')
        ->body->toBe("Thanks!\nSee you.")
        ->and($comment->fresh()->isApproved())->toBeTrue();
});

test('replying to a reply joins the same thread, one level deep', function () {
    $parent = Comment::factory()->approved()->create();
    $child = Comment::factory()->approved()->replyTo($parent)->create();

    $this->actingAs($this->admin)->post(route('admin.comments.reply', $child), ['body' => 'Answer']);

    expect(Comment::query()->where('is_owner', true)->first()->parent_id)->toBe($parent->id);
});

test('the reply is validated and its errors reopen the right reply box', function () {
    $comment = Comment::factory()->create(['author_name' => 'Asker']);

    $this->actingAs($this->admin)->from(route('admin.comments.index'))
        ->post(route('admin.comments.reply', $comment), ['body' => ''])
        ->assertSessionHasErrorsIn('reply', 'body');

    $this->actingAs($this->admin)->post(route('admin.comments.reply', $comment), ['body' => str_repeat('x', Comment::MAX_BODY_LENGTH + 1)])
        ->assertSessionHasErrorsIn('reply', 'body');
    $this->actingAs($this->admin)->post(route('admin.comments.reply', $comment), ['body' => ['x']])
        ->assertSessionHasErrorsIn('reply', 'body');

    expect(Comment::query()->count())->toBe(1)
        ->and($comment->fresh()->isApproved())->toBeFalse();

    $this->actingAs($this->admin)->from(route('admin.comments.index'))
        ->followingRedirects()
        ->post(route('admin.comments.reply', $comment), ['body' => ''])
        ->assertSee('id="reply-'.$comment->id.'"', false)
        ->assertDontSee('id="reply-'.$comment->id.'" hidden', false);
});

test('deleting a comment deletes its replies', function () {
    $parent = Comment::factory()->approved()->create();
    Comment::factory()->byOwner()->replyTo($parent)->create();
    $other = Comment::factory()->create();

    $this->actingAs($this->admin)->delete(route('admin.comments.destroy', $parent))
        ->assertSessionHas('status', 'The comment has been deleted.');

    expect(Comment::query()->pluck('id')->all())->toBe([$other->id]);
});

test('deleting a post deletes its comments', function () {
    $comment = Comment::factory()->approved()->create();

    $this->actingAs($this->admin)->delete(route('admin.posts.destroy', $comment->post));

    expect(Comment::query()->count())->toBe(0);
});

test('the delete button warns that replies go too', function () {
    Comment::factory()->create(['author_name' => 'Top Level']);

    $this->actingAs($this->admin)->get(route('admin.comments.index'))
        ->assertSee('data-confirm="Delete this comment by &quot;Top Level&quot;? Its replies will be deleted too."', false);
});

test('unknown comments are 404', function () {
    $this->actingAs($this->admin)->patch(route('admin.comments.approve', 999))->assertNotFound();
    $this->actingAs($this->admin)->delete(route('admin.comments.destroy', 999))->assertNotFound();
});

// ---------- Posts: allow comments + counts ----------

test('the post form can close comments, and the posts list shows comment counts', function () {
    $post = Post::factory()->withThemeImages()->create(['title' => 'Talked About']);
    Comment::factory()->count(2)->create(['post_id' => $post->id]);
    Comment::factory()->approved()->create(['post_id' => $post->id]);

    $this->actingAs($this->admin)->get(route('admin.posts.index'))
        ->assertSee('3 comments (2 pending)');

    $this->actingAs($this->admin)->get(route('admin.posts.edit', $post))
        ->assertSee('id="allow_comments"', false)
        ->assertSee('href="'.route('admin.comments.index', ['post' => $post->id]).'"', false);

    $this->actingAs($this->admin)->put(route('admin.posts.update', $post), [
        'title' => 'Talked About', 'author_name' => 'Me', 'status' => 'draft', 'allow_comments' => '0',
    ])->assertSessionHasNoErrors();

    expect($post->fresh()->allow_comments)->toBeFalse();

    $this->actingAs($this->admin)->get(route('admin.posts.index'))->assertSee('comments closed');
});

test('the reply-by-email link encodes the address, so it can not add mail parameters', function () {
    Comment::factory()->create(['author_email' => 'x?bcc=spy@example.com']);

    $this->actingAs($this->admin)->get(route('admin.comments.index'))
        ->assertSee('href="mailto:x%3Fbcc%3Dspy@example.com"', false);
});

test('comment text is escaped in the admin', function () {
    Comment::factory()->create(['author_name' => '<script>alert(1)</script>', 'body' => '<img src=x onerror=alert(2)>']);

    $this->actingAs($this->admin)->get(route('admin.comments.index'))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x onerror=alert(2)>', false);
});

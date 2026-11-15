<?php

use App\Http\Controllers\Frontend\CommentController;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Database\Seeders\CommentSeeder;
use Database\Seeders\PostSeeder;

beforeEach(function () {
    $this->post = Post::factory()->published()->withThemeImages()->create(['title' => 'Commented Post']);
});

function commentPayload(array $overrides = []): array
{
    return array_merge([
        'author_name' => 'Ada Lovelace',
        'author_email' => 'ada@example.com',
        'body' => "  Great post!\r\nSecond line.  ",
    ], $overrides);
}

function commentsHtml(string $html): string
{
    $start = strpos($html, '<section class="comment-area-main-wrapper');

    return $start === false ? '' : substr($html, $start, strpos($html, '</section>', $start) - $start);
}

// ---------- Posting ----------

test('a visitor comment is saved as pending and not shown yet', function () {
    $this->post(route('blog.comments.store', $this->post->slug), commentPayload())
        ->assertRedirect(route('blog.show', $this->post->slug).'#comment-form')
        ->assertSessionHas('comment_status', CommentController::THANK_YOU);

    $comment = Comment::query()->firstOrFail();

    expect($comment)
        ->post_id->toBe($this->post->id)
        ->status->toBe(Comment::STATUS_PENDING)
        ->approved_at->toBeNull()
        ->is_owner->toBeFalse()
        ->body->toBe("Great post!\nSecond line.")
        ->ip_address->toBe('127.0.0.1');

    $this->get(route('blog.show', $this->post->slug))
        ->assertDontSee('Ada Lovelace')
        ->assertSee('Comments (00)');
});

test('the thank-you message is shown above the form', function () {
    $this->followingRedirects()
        ->post(route('blog.comments.store', $this->post->slug), commentPayload())
        ->assertSee(CommentController::THANK_YOU);
});

test('approved comments show oldest first, with replies indented and the owner marked', function () {
    $first = Comment::factory()->approved()->create(['post_id' => $this->post->id, 'author_name' => 'First Person', 'created_at' => now()->subDays(2)]);
    Comment::factory()->approved()->create(['post_id' => $this->post->id, 'author_name' => 'Second Person', 'created_at' => now()->subDay()]);
    Comment::factory()->byOwner()->replyTo($first)->create(['author_name' => 'Site Owner']);
    Comment::factory()->replyTo($first)->create(['author_name' => 'Pending Reply']);
    Comment::factory()->create(['post_id' => $this->post->id, 'author_name' => 'Pending Person']);

    $html = $this->get(route('blog.show', $this->post->slug))->assertOk()->getContent();
    $section = commentsHtml($html);

    expect($section)
        ->toContain('Comments (3)')
        ->toContain('<span class="comment-author-badge">Author</span>')
        ->toMatch('/class="single-comment-audience pl--100"[^>]*>.*?Site Owner/s')
        ->not->toContain('Pending Reply')
        ->not->toContain('Pending Person')
        ->and(strpos($section, 'First Person'))->toBeLessThan(strpos($section, 'Site Owner'))
        ->and(strpos($section, 'Site Owner'))->toBeLessThan(strpos($section, 'Second Person'))
        ->and($html)->toContain('Comments (03)');
});

test('commenter emails are never shown publicly', function () {
    Comment::factory()->approved()->create(['post_id' => $this->post->id, 'author_email' => 'secret@example.com']);

    $this->get(route('blog.show', $this->post->slug))->assertDontSee('secret@example.com');
});

test('initials are shown instead of photos', function () {
    Comment::factory()->approved()->create(['post_id' => $this->post->id, 'author_name' => 'court  henry']);

    expect(commentsHtml($this->get(route('blog.show', $this->post->slug))->getContent()))
        ->toContain('>CH</span>')
        ->not->toContain('<img');
});

// ---------- Replies ----------

test('a visitor can reply to an approved top-level comment', function () {
    $parent = Comment::factory()->approved()->create(['post_id' => $this->post->id, 'author_name' => 'Parent Person']);

    $this->get(route('blog.show', ['slug' => $this->post->slug, 'reply' => $parent->id]))
        ->assertSee('Reply to Parent Person')
        ->assertSee('name="parent_id" value="'.$parent->id.'"', false);

    $this->post(route('blog.comments.store', $this->post->slug), commentPayload(['parent_id' => $parent->id]))
        ->assertSessionHasNoErrors();

    expect(Comment::query()->latest('id')->first()->parent_id)->toBe($parent->id);
});

test('replies to pending, nested or other posts\' comments are refused', function () {
    $pending = Comment::factory()->create(['post_id' => $this->post->id]);
    $parent = Comment::factory()->approved()->create(['post_id' => $this->post->id]);
    $nested = Comment::factory()->approved()->replyTo($parent)->create();
    $elsewhere = Comment::factory()->approved()->create();

    foreach ([$pending->id, $nested->id, $elsewhere->id] as $parentId) {
        $this->post(route('blog.comments.store', $this->post->slug), commentPayload(['parent_id' => $parentId]))
            ->assertSessionHasErrorsIn('comment', 'parent_id');
    }

    expect(Comment::query()->count())->toBe(4);
});

test('an invalid reply link just shows the normal form', function () {
    $this->get(route('blog.show', ['slug' => $this->post->slug, 'reply' => '999']))->assertOk()->assertSee('Leave a comment');
    $this->get(route('blog.show', $this->post->slug).'?reply[]=1')->assertOk()->assertSee('Leave a comment');
});

// ---------- Validation and spam protection ----------

test('required fields and lengths are validated, keeping what was typed', function () {
    $this->post(route('blog.comments.store', $this->post->slug), ['author_name' => '', 'author_email' => 'not-an-email', 'body' => ' '])
        ->assertRedirect(route('blog.show', $this->post->slug).'#comment-form')
        ->assertSessionHasErrorsIn('comment', ['author_name', 'author_email', 'body'])
        ->assertSessionHasInput('author_email', 'not-an-email');

    $this->post(route('blog.comments.store', $this->post->slug), commentPayload([
        'author_name' => str_repeat('a', 81),
        'body' => str_repeat('b', Comment::MAX_BODY_LENGTH + 1),
    ]))->assertSessionHasErrorsIn('comment', ['author_name', 'body']);

    expect(Comment::query()->count())->toBe(0);
});

test('field errors show next to the fields on the post page', function () {
    $this->followingRedirects()
        ->post(route('blog.comments.store', $this->post->slug), commentPayload(['author_name' => '']))
        ->assertSee('id="comment-name-error"', false)
        ->assertSee('The name field is required.')
        ->assertSee('Great post!');
});

test('array input is rejected without an error page', function () {
    $payload = ['author_name' => ['x'], 'author_email' => ['y'], 'body' => ['z'], 'parent_id' => ['1']];

    $this->post(route('blog.comments.store', $this->post->slug), $payload)
        ->assertRedirect()
        ->assertSessionHasErrorsIn('comment', ['author_name', 'author_email', 'body', 'parent_id']);

    // The post page the visitor is sent back to must render too.
    $this->followingRedirects()
        ->post(route('blog.comments.store', $this->post->slug), $payload)
        ->assertOk()
        ->assertSee('Leave a comment');

    expect(Comment::query()->count())->toBe(0);
});

test('invalid UTF-8 gets a validation error instead of a database error', function () {
    $this->post(route('blog.comments.store', $this->post->slug), commentPayload(['author_name' => "Ada\xFF", 'body' => "Hi \xFE\xFF there"]))
        ->assertSessionHasErrorsIn('comment', ['author_name', 'body']);

    $this->withHeader('User-Agent', "Bot\xFF\xFE")
        ->post(route('blog.comments.store', $this->post->slug), commentPayload())
        ->assertSessionHasNoErrors();

    expect(Comment::query()->count())->toBe(1)
        ->and(mb_check_encoding(Comment::query()->first()->user_agent, 'UTF-8'))->toBeTrue();
});

test('invisible characters are stripped, so a name of only zero-width characters is refused', function () {
    $this->post(route('blog.comments.store', $this->post->slug), commentPayload(['author_name' => "\u{200B}\u{202E}\u{FEFF}"]))
        ->assertSessionHasErrorsIn('comment', 'author_name');

    $this->post(route('blog.comments.store', $this->post->slug), commentPayload([
        'author_name' => "Ada\u{202E} Lovelace",
        'body' => "Line one\u{200B}\nLine two 👩‍💻",
    ]))->assertSessionHasNoErrors();

    expect(Comment::query()->first())
        ->author_name->toBe('Ada Lovelace')
        ->body->toBe("Line one\nLine two 👩‍💻");
});

test('emails that would add parameters to the owner\'s mailto link are refused', function (string $email) {
    $this->post(route('blog.comments.store', $this->post->slug), commentPayload(['author_email' => $email]))
        ->assertSessionHasErrorsIn('comment', 'author_email');

    expect(Comment::query()->count())->toBe(0);
})->with(['x?bcc=spy@example.com', 'a&body=hi@example.com', 'a#b@example.com', 'a%40b@example.com']);

test('honeypot submissions get the normal answer but are not saved', function () {
    $this->post(route('blog.comments.store', $this->post->slug), commentPayload([StoreCommentRequest::HONEYPOT => 'http://spam.example']))
        ->assertRedirect(route('blog.show', $this->post->slug).'#comment-form')
        ->assertSessionHas('comment_status', CommentController::THANK_YOU);

    expect(Comment::query()->count())->toBe(0);
});

test('a browser autofilling a "website" field never causes a comment to be discarded', function () {
    $this->post(route('blog.comments.store', $this->post->slug), commentPayload(['website' => 'https://ada.example']))
        ->assertSessionHasNoErrors();

    expect(Comment::query()->count())->toBe(1);
});

test('more than 3 comments a minute from one IP are blocked with a friendly message', function () {
    foreach (range(1, 3) as $i) {
        $this->post(route('blog.comments.store', $this->post->slug), commentPayload(['body' => "Comment {$i}"]))->assertSessionHasNoErrors();
    }

    $this->from(route('blog.show', $this->post->slug))
        ->post(route('blog.comments.store', $this->post->slug), commentPayload(['body' => 'Comment 4']))
        ->assertRedirect(route('blog.show', $this->post->slug).'#comment-form')
        ->assertSessionHasErrorsIn('comment', ['body' => 'You are sending comments too quickly. Please wait a little and try again.'])
        ->assertSessionHasInput('body', 'Comment 4');

    expect(Comment::query()->count())->toBe(3);

    $this->travel(61)->seconds();
    $this->post(route('blog.comments.store', $this->post->slug), commentPayload(['body' => 'Comment 5']))->assertSessionHasNoErrors();
    expect(Comment::query()->count())->toBe(4);
});

test('IPv6 visitors share one limit per /64 network', function () {
    foreach (['2001:db8:1:2::1', '2001:db8:1:2::a', '2001:db8:1:2:ffff::9'] as $i => $ip) {
        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(route('blog.comments.store', $this->post->slug), commentPayload(['body' => "Comment {$i}"]))
            ->assertSessionHasNoErrors();
    }

    $this->withServerVariables(['REMOTE_ADDR' => '2001:db8:1:2::beef'])
        ->post(route('blog.comments.store', $this->post->slug), commentPayload(['body' => 'Blocked']))
        ->assertSessionHasErrorsIn('comment', 'body');

    $this->withServerVariables(['REMOTE_ADDR' => '2001:db8:1:3::1'])
        ->post(route('blog.comments.store', $this->post->slug), commentPayload(['body' => 'Other network']))
        ->assertSessionHasNoErrors();

    expect(Comment::query()->count())->toBe(4);
});

test('the whole site accepts at most 100 comments an hour', function () {
    foreach (range(1, 100) as $i) {
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.'.intdiv($i, 250).'.'.($i % 250)])
            ->post(route('blog.comments.store', $this->post->slug), commentPayload(['body' => "Comment {$i}"]));
    }

    $this->withServerVariables(['REMOTE_ADDR' => '10.9.9.9'])
        ->post(route('blog.comments.store', $this->post->slug), commentPayload(['body' => 'One too many']))
        ->assertSessionHasErrorsIn('comment', 'body');

    expect(Comment::query()->count())->toBe(100);
});

test('the form includes the CSRF field and the hidden honeypot', function () {
    $this->get(route('blog.show', $this->post->slug))
        ->assertSee('name="_token"', false)
        ->assertSee('name="'.StoreCommentRequest::HONEYPOT.'" value="" tabindex="-1"', false)
        ->assertDontSee('name="website"', false)
        ->assertSee('class="comment-hp" aria-hidden="true"', false);
});

// ---------- Which posts accept comments ----------

test('drafts, future posts and unknown posts can not be commented on', function () {
    $draft = Post::factory()->withThemeImages()->create();
    $future = Post::factory()->withThemeImages()->create(['status' => 'published', 'published_at' => now()->addDay()]);

    $this->post(route('blog.comments.store', $draft->slug), commentPayload())->assertNotFound();
    $this->post(route('blog.comments.store', $future->slug), commentPayload())->assertNotFound();
    $this->post(route('blog.comments.store', 'no-such-post'), commentPayload())->assertNotFound();

    expect(Comment::query()->count())->toBe(0);
});

test('closed posts keep their approved comments but have no form and refuse new ones', function () {
    $this->post->update(['allow_comments' => false]);
    Comment::factory()->approved()->create(['post_id' => $this->post->id, 'author_name' => 'Earlier Person']);

    $this->get(route('blog.show', $this->post->slug))
        ->assertSee('Earlier Person')
        ->assertSee('Comments are closed on this post.')
        ->assertDontSee('Leave a comment')
        ->assertDontSee('class="reply-btn"', false);

    $this->post(route('blog.comments.store', $this->post->slug), commentPayload())
        ->assertSessionHasErrorsIn('comment', ['body' => 'Comments are closed on this post.']);

    expect(Comment::query()->count())->toBe(1);
});

// ---------- Escaping and seed data ----------

test('comment text is escaped, so markup can not run', function () {
    Comment::factory()->approved()->create([
        'post_id' => $this->post->id,
        'author_name' => '<script>alert(1)</script>',
        'body' => '<img src=x onerror=alert(2)><a href="javascript:alert(3)">x</a>',
    ]);

    $this->get(route('blog.show', $this->post->slug))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x onerror=alert(2)>', false)
        ->assertDontSee('<a href="javascript:alert(3)">', false)
        ->assertSee('&lt;img src=x onerror=alert(2)&gt;', false);
});

test('the seeded comments render on the newest post', function () {
    $this->post->delete();
    $this->seed([PostSeeder::class, CommentSeeder::class]);
    $post = Post::query()->published()->newestFirst()->first();

    $section = commentsHtml($this->get(route('blog.show', $post->slug))->getContent());

    expect(Comment::query()->count())->toBe(4)
        ->and(Comment::query()->pending()->count())->toBe(1)
        ->and($section)->toContain('Stanio lainto')->toContain('Court Henry')->toContain('Author')
        ->not->toContain('Jenny Wilson');
});

<?php

namespace Tests\Feature\Templates;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Sendportal\Base\Models\Template;
use Tests\TestCase;

class TemplatesControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    // Index.

    /** @test */
    function a_guest_cannot_see_the_index()
    {
        $response = $this->get(route('sendportal.templates.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    function a_logged_in_user_can_see_template_index()
    {
        $user = $this->createUserWithTeam();
        $this->loginUser($user);

        $response = $this->get(route('sendportal.templates.index'));

        $response->assertOk();
    }

    /** @test */
    function the_index_lists_existing_templates()
    {
        $user = $this->createUserWithTeam();
        $this->loginUser($user);

        $template = factory(Template::class)->create([
            'team_id' => $user->currentTeam()->id
        ]);

        $response = $this->get(route('sendportal.templates.index'));

        $response->assertOk();
        $response->assertSee($template->name);
    }

    // Create.

    /** @test */
    function a_guest_cannot_see_the_create_form()
    {
        $response = $this->get(route('sendportal.templates.create'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    function a_logged_in_user_can_see_the_create_form()
    {
        $user = $this->createUserWithTeam();
        $this->loginUser($user);

        $response = $this->get(route('sendportal.templates.create'));

        $response->assertOk();
        $response->assertSee('New Template');
        $response->assertSee('Template Name');
        $response->assertSee('Content');
    }

    // Store.

    /** @test */
    function a_guest_cannot_store_a_new_template()
    {
        $data = [
            'name' => $this->faker->name,
            'content' => $this->faker->sentence
        ];

        $response = $this->post(route('sendportal.templates.store'), $data);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('templates', [
            'name' => $data['name'],
            'content' => $data['content'],
        ]);
    }

    /** @test */
    function a_logged_in_user_can_store_a_new_template()
    {
        $user = $this->createUserWithTeam();
        $this->loginUser($user);

        $data = [
            'name' => $this->faker->name,
            'content' => $this->faker->sentence
        ];

        $response = $this->post(route('sendportal.templates.store'), $data);

        $response->assertRedirect(route('sendportal.templates.index'));

        $this->assertDatabaseHas('templates', [
            'name' => $data['name'],
            'content' => $data['content'],
            'team_id' => $user->currentTeam()->id
        ]);
    }

    /** @test */
    function storing_is_validated()
    {
        $user = $this->createUserWithTeam();
        $this->loginUser($user);

        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->post(route('sendportal.templates.store'), $data);

        $response->assertSessionHasErrors('content');

        $data = [
            'content' => $this->faker->sentence
        ];

        $response = $this->post(route('sendportal.templates.store'), $data);

        $response->assertSessionHasErrors('name');
    }

    // Edit.

    /** @test */
    function a_guest_cannot_see_the_edit_form()
    {
        $template = factory(Template::class)->create();

        $response = $this->get(route('sendportal.templates.edit', $template->id));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    function a_logged_in_user_can_see_the_edit_form()
    {
        $user = $this->createUserWithTeam();
        $this->loginUser($user);

        $template = factory(Template::class)->create([
            'team_id' => $user->currentTeam()->id
        ]);

        $response = $this->get(route('sendportal.templates.edit', $template->id));

        $response->assertOk();

        $response->assertSee($template->name);
        $response->assertSee($template->content);
    }

    // Update.

    /** @test */
    function a_guest_cannot_update_a_template()
    {
        $template = factory(Template::class)->create();

        $data = [
            'name' => $this->faker->name,
            'content' => $this->faker->sentence
        ];

        $response = $this->put(route('sendportal.templates.update', $template->id), $data);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('templates', $data);
        $this->assertDatabaseHas('templates', [
            'id' => $template->id,
            'team_id' => $template->team_id,
            'name' => $template->name,
            'content' => $template->content
        ]);
    }

    /** @test */
    function a_logged_in_user_can_update_a_template()
    {
        $user = $this->createUserWithTeam();
        $this->loginUser($user);

        $template = factory(Template::class)->create([
            'team_id' => $user->currentTeam()->id
        ]);

        $data = [
            'name' => $this->faker->name,
            'content' => $this->faker->sentence
        ];

        $response = $this->put(route('sendportal.templates.update', $template->id), $data);

        $response->assertRedirect(route('sendportal.templates.index'));

        $this->assertDatabaseMissing('templates', [
            'id' => $template->id,
            'name' => $template->name,
            'content' => $template->content
        ]);
        $this->assertDatabaseHas('templates', $data + ['id' => $template->id, 'team_id' => $user->currentTeam()->id]);
    }

    /** @test */
    function updates_are_validated()
    {
        $user = $this->createUserWithTeam();
        $this->loginUser($user);

        $template = factory(Template::class)->create([
            'team_id' => $user->currentTeam()->id
        ]);

        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->put(route('sendportal.templates.update', $template->id), $data);

        $response->assertSessionHasErrors('content');

        $data = [
            'content' => $this->faker->sentence
        ];

        $response = $this->put(route('sendportal.templates.update', $template->id), $data);

        $response->assertSessionHasErrors('name');
    }

    // Destroy.

    /** @test */
    function a_logged_in_user_can_delete_a_template()
    {
        $user = $this->createUserWithTeam();
        $this->loginUser($user);

        $template = factory(Template::class)->create([
            'team_id' => $user->currentTeam()->id
        ]);

        $response = $this->delete(route('sendportal.templates.destroy', $template->id));

        $response->assertRedirect(route('sendportal.templates.index'));

        $this->assertDatabaseMissing('templates', [
            'id' => $template->id,
            'name' => $template->name
        ]);
    }
}

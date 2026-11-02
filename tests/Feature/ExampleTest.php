<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root has no page of its own: it sends visitors to the visits list.
     */
    public function test_the_root_redirects_to_the_visits_list(): void
    {
        $this->get('/')->assertRedirect('/visits');
    }
}

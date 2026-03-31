<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository;
    }

    public function test_find_by_email_returns_user(): void
    {
        $user = User::factory()->createOne(['email' => 'find@me.com']);

        $found = $this->repository->findByEmail('find@me.com');

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_by_email_returns_null_if_not_found(): void
    {
        $found = $this->repository->findByEmail('nonexistent@me.com');

        $this->assertNull($found);
    }
}

<?php

namespace rohsyl\LaravelOtc\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use rohsyl\LaravelOtc\Generators\NumberGenerator;
use rohsyl\LaravelOtc\LaravelOtcManager;
use rohsyl\LaravelOtc\Models\OtcToken;
use rohsyl\LaravelOtc\Notifications\OneTimeCodeNotification;
use rohsyl\LaravelOtc\Tests\Models\User;

class ControllerTest extends LaravelOtcTestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }


    /** @test */
    public function it_request_code_controller_throw_validation_error()
    {
        $response = $this->post(
            uri: route('laravel-otc.request-code'),
            data: [],
            headers: ['Accept' => 'application/json']
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function it_request_code_controller()
    {

        Notification::fake();

        $response = $this->postJson(
            uri: route('laravel-otc.request-code'),
            data: [
                'type' => 'user',
                'identifier' => $this->user->email,
            ],
            headers: ['Accept' => 'application/json', 'Content-Type' => 'application/json']
        );

        $response->assertStatus(200);
        Notification::assertSentTo($this->user, OneTimeCodeNotification::class);
    }

    /** @test */
    public function it_request_code_controller_redirect()
    {

        Notification::fake();

        $response = $this->post(
            uri: route('laravel-otc.request-code'),
            data: [
                'type' => 'user',
                'identifier' => $this->user->email,
            ]
        );

        $response->assertStatus(302);
        Notification::assertSentTo($this->user, OneTimeCodeNotification::class);
    }

    /** @test */
    public function it_throw_error_when_entity_not_found_and_not_registerable()
    {

        Notification::fake();

        $response = $this->post(
            uri: route('laravel-otc.request-code'),
            data: [
                'type' => 'user',
                'identifier' => 'wrong_email@mail.com',
            ]
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function it_auth_and_return_token()
    {
        Carbon::setTestNow(Carbon::create(2022, 10, 10, 10, 0, 0));

        OtcToken::factory()->create([
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'code' => 12345,
            'ip' => '127.0.0.1',
            'code_valid_until' => Carbon::create(2022, 10, 10, 10, 15, 0),
            'token' => null,
        ]);

        $response = $this->postJson(
            uri: route('laravel-otc.auth-code'),
            data: [
                'type' => 'user',
                'identifier' => $this->user->email,
                'code' => 12345
            ],
            headers: ['Accept' => 'application/json', 'Content-Type' => 'application/json']
        );

        $response->assertStatus(200);
    }

    /** @test */
    public function it_deny_auth_when_wrong_token()
    {
        Carbon::setTestNow(Carbon::create(2022, 10, 10, 10, 0, 0));

        OtcToken::factory()->create([
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'code' => 12345,
            'ip' => '127.0.0.1',
            'code_valid_until' => Carbon::create(2022, 10, 10, 10, 15, 0),
            'token' => null,
        ]);

        $response = $this->postJson(
            uri: route('laravel-otc.auth-code'),
            data: [
                'type' => 'user',
                'identifier' => $this->user->email,
                'code' => 22222
            ],
            headers: ['Accept' => 'application/json', 'Content-Type' => 'application/json']
        );

        $response->assertStatus(401);
    }

    /** @test */
    public function it_throw_validation_error_when_missing_code()
    {
        Carbon::setTestNow(Carbon::create(2022, 10, 10, 10, 0, 0));

        OtcToken::factory()->create([
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'code' => 12345,
            'ip' => '127.0.0.1',
            'code_valid_until' => Carbon::create(2022, 10, 10, 10, 15, 0),
            'token' => null,
        ]);

        $response = $this->postJson(
            uri: route('laravel-otc.auth-code'),
            data: [
                'type' => 'user',
                'identifier' => $this->user->email
            ],
            headers: ['Accept' => 'application/json', 'Content-Type' => 'application/json']
        );

        $response->assertStatus(422);
    }
}

<?php

namespace rohsyl\LaravelOtc\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Request;
use rohsyl\LaravelOtc\Generators\NumberGenerator;
use rohsyl\LaravelOtc\LaravelOtcManager;
use rohsyl\LaravelOtc\Models\OtcToken;
use rohsyl\LaravelOtc\Notifications\OneTimeCodeNotification;
use rohsyl\LaravelOtc\Otc;
use rohsyl\LaravelOtc\Tests\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OtcTest extends LaravelOtcTestCase
{
    use RefreshDatabase;

    private User $user;
    private LaravelOtcManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->manager = new LaravelOtcManager(new NumberGenerator());
    }

    /** @test */
    public function it_check() {
        Carbon::setTestNow(Carbon::create(2022, 10, 10, 11, 0, 0));

        OtcToken::factory()->create([
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'code' => 12345,
            'code_valid_until' => Carbon::create(2022, 10, 10, 10, 15, 0),
            'token' => '123456789asdfghjkqwertzuiy<xcvbnm',
            'token_valid_until' => Carbon::create(2022, 11, 20, 10, 00, 0),
        ]);

        $request = Request::createFromBase(new \Symfony\Component\HttpFoundation\Request([
            'token' => '123456789asdfghjkqwertzuiy<xcvbnm',
        ]));

        $this->manager->setTestRequest($request);

        $isAuth = $this->manager->check();

        $this->assertTrue($isAuth);
    }

    /** @test */
    public function it_deny_wrong_token() {
        Carbon::setTestNow(Carbon::create(2022, 10, 10, 11, 0, 0));

        OtcToken::factory()->create([
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'code' => 12345,
            'code_valid_until' => Carbon::create(2022, 10, 10, 10, 15, 0),
            'token' => '123456789asdfghjkqwertzuiy<xcvbnm',
            'token_valid_until' => Carbon::create(2022, 11, 20, 10, 00, 0),
        ]);

        $request = Request::createFromBase(new \Symfony\Component\HttpFoundation\Request([
            'token' => 'wrong_token',
        ]));

        $this->manager->setTestRequest($request);

        $isAuth = $this->manager->check();

        $this->assertFalse($isAuth);
    }

    /** @test */
    public function it_deny_when_no_token() {
        Carbon::setTestNow(Carbon::create(2022, 10, 10, 11, 0, 0));

        OtcToken::factory()->create([
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'code' => 12345,
            'code_valid_until' => Carbon::create(2022, 10, 10, 10, 15, 0),
            'token' => '123456789asdfghjkqwertzuiy<xcvbnm',
            'token_valid_until' => Carbon::create(2022, 11, 20, 10, 00, 0),
        ]);

        $request = Request::createFromBase(new \Symfony\Component\HttpFoundation\Request([
        ]));

        $this->manager->setTestRequest($request);

        $isAuth = $this->manager->check();

        $this->assertFalse($isAuth);
    }

    /** @test */
    public function it_deny_when_token_unvalid() {
        Carbon::setTestNow(Carbon::create(2022, 11, 25, 11, 0, 0));

        OtcToken::factory()->create([
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'code' => 12345,
            'code_valid_until' => Carbon::create(2022, 10, 10, 10, 15, 0),
            'token' => '123456789asdfghjkqwertzuiy<xcvbnm',
            'token_valid_until' => Carbon::create(2022, 11, 20, 10, 00, 0),
        ]);

        $request = Request::createFromBase(new \Symfony\Component\HttpFoundation\Request([
            'token' => '123456789asdfghjkqwertzuiy<xcvbnm',
        ]));

        $this->manager->setTestRequest($request);

        $isAuth = $this->manager->check();

        $this->assertFalse($isAuth);
    }

    /** @test */
    public function it_create_code() {

        $this->assertDatabaseEmpty('otc_tokens');

        $this->manager->createCode($this->user);

        $this->assertDatabaseHas('otc_tokens', [
            'related_type' => User::class,
            'related_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_check_code() {

        Carbon::setTestNow(Carbon::create(2022, 10, 10, 10, 0, 0));

        OtcToken::factory()->create([
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'code' => 12345,
            'ip' => null,
            'code_valid_until' => Carbon::create(2022, 10, 10, 10, 15, 0),
            'token' => null,
        ]);

        $request = Request::createFromBase(new \Symfony\Component\HttpFoundation\Request([
            'type' => 'user',
            'identifier' => $this->user->email,
            'code' => 12345
        ]));


        $this->manager->setTestRequest($request);

        $isValid = $this->manager->checkCode();

        $this->assertTrue($isValid);
    }

    /** @test */
    public function it_deny_wrong_code() {

        Carbon::setTestNow(Carbon::create(2022, 10, 10, 10, 0, 0));

        OtcToken::factory()->create([
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'code' => 12345,
            'code_valid_until' => Carbon::create(2022, 10, 10, 10, 15, 0),
            'token' => null,
        ]);

        $request = Request::createFromBase(new \Symfony\Component\HttpFoundation\Request([
            'type' => 'user',
            'identifier' => $this->user->email,
            'code' => 12222
        ]));

        $this->manager->setTestRequest($request);

        $isValid = $this->manager->checkCode();

        $this->assertFalse($isValid);
    }


    /** @test */
    public function it_deny_code_when_unvalid() {

        Carbon::setTestNow(Carbon::create(2022, 10, 10, 11, 0, 0));

        OtcToken::factory()->create([
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'code' => 12345,
            'code_valid_until' => Carbon::create(2022, 10, 10, 10, 15, 0),
            'token' => null,
        ]);

        $request = Request::createFromBase(new \Symfony\Component\HttpFoundation\Request([
            'type' => 'user',
            'identifier' => $this->user->email,
            'code' => 12345
        ]));

        $this->manager->setTestRequest($request);

        $isValid = $this->manager->checkCode();

        $this->assertFalse($isValid);
    }

    /** @test */
    public function it_create_token() {

        Carbon::setTestNow(Carbon::create(2022, 10, 10, 10, 0, 0));

        $token = OtcToken::factory()->create([
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'code' => 12345,
            'code_valid_until' => Carbon::create(2022, 10, 10, 10, 15, 0),
            'token' => null,
        ]);

        $this->assertDatabaseHas('otc_tokens', [
            'related_type' => User::class,
            'related_id' => $this->user->id,
        ]);

        $this->manager->createToken($token);


        $this->assertDatabaseHas('otc_tokens', [
            'related_type' => User::class,
            'related_id' => $this->user->id,
            'token' => $token->token,
        ]);

    }

    /** @test */
    public function it_sent_notification() {
        Notification::fake();

        $request = Request::createFromBase(new \Symfony\Component\HttpFoundation\Request([
            'type' => 'user',
            'identifier' => $this->user->email
        ]));

        $this->manager->setTestRequest($request);

        $this->manager->sendCode();

        Notification::assertSentTo($this->user, OneTimeCodeNotification::class);
    }

    /** @test */
    public function unauthorized_response_test() {

        $request = Request::createFromBase(
            new \Symfony\Component\HttpFoundation\Request(
                server: ['HTTP_Accept' => 'application/json']
            )
        );
        $this->manager->setTestRequest($request);

        $response = $this->manager->unauthorizedResponse($this->user);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /** @test */
    public function unauthorized_response_abort_when_unknow_type_test() {

        $request = Request::createFromBase(
            new \Symfony\Component\HttpFoundation\Request(
                server: ['HTTP_Accept' => 'application/json']
            )
        );

        $this->manager->setTestRequest($request);

        $this->manager->setTestRequest($request);

        //$this->expectException(NoMatchingAuthenticatableException::class);
        $this->expectException(HttpException::class);

        $this->manager->unauthorizedResponse(new OtcToken());

    }

    /** @test */
    public function unauthorized_response_abort_test() {
        //$this->expectException(NoMatchingAuthenticatableException::class);
        $this->expectException(HttpException::class);
        $this->manager->unauthorizedResponse($this->user);

    }

    /** @test */
    public function facade_works_test() {
        $isAuth = Otc::check();
        $this->assertFalse($isAuth);
    }
}

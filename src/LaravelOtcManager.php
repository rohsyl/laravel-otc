<?php

namespace rohsyl\LaravelOtc;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use rohsyl\LaravelOtc\Generators\GeneratorContract;
use rohsyl\LaravelOtc\Models\OtcToken;
use rohsyl\LaravelOtc\Notifications\OneTimeCodeNotification;
use Symfony\Component\HttpFoundation\Response;

class LaravelOtcManager
{
    private $generator;

    public function __construct(
        GeneratorContract $generator
    )
    {
        $this->generator = $generator;
    }

    /**
     * Return true if authenticated using bearer token
     * @return bool
     */
    public function check()
    {
        $token = request()->bearerToken() ?? (
            request()->has('token') ? request()->token : null
        );

        if(!isset($token)) return false;

        $otc = $this->findOtcTokenByToken($token);

        return isset($otc)
            && $otc->token === $token
            && $otc->token_valid_until->isAfter(now());
    }

    public function unauthorizedResponse(Model $related) : Response
    {
        $slug = $this->getModelSlug($related);

        if(!isset($slug)) {
            // todo throw an exceition ?
            // throw new  NoMatchingAuthenticatableException
            return abort(401);
        }

        $identifierColumn = config('otc.authenticatables.' . $slug . '.identifier');

        if (request()->wantsJson()) {
            return response()->json([
                'request_code_url' => route('laravel-otc.request-code'),
                'request_code_body' => [
                    'type' => $slug,
                    'identifier' => $related->$identifierColumn,
                ]
            ], 401);
        }

        return abort(401);
    }

    public function storeCode(Model $related, $code) : OtcToken
    {
        return OtcToken::create([
            'related_type' => get_class($related),
            'related_id' => $related->id,
            'ip' => request()->ip(),
            'code' => $code,
            'code_valid_until' => now()->addMinutes(30),
        ]);
    }

    public function getModel() : Model
    {
        $slug = request()->type;
        $identifier = request()->identifier;

        $modelClass = config('otc.authenticatables.' . $slug . '.model');
        $identifierColumn = config('otc.authenticatables.' . $slug . '.identifier');

        return call_user_func_array([$modelClass, 'query'], [])->where($identifierColumn, $identifier)->first();
    }

    public function checkCode(OtcToken $token = null)
    {
        $token = $token ?? $this->findOtcTokenByRelatedAndCode($this->getModel(), request()->code);

        return isset($token)
            && $token->code == request()->code
            && $token->code_valid_until->isAfter(now());
    }

    public function createCode(Model $related)
    {
        $code = $this->generator->generate();
        return $this->storeCode($related, $code);
    }

    public function createToken(OtcToken $token)
    {
        $token->update([
            'token' => Str::random(64),
            'token_valid_until' => now()->addDays(30),
        ]);
    }

    public function sendCode(?Model $related = null, ?OtcToken $token = null)
    {
        $related = $related ?? $this->getModel();
        $token = $token ?? $this->createCode($related);

        $notifierClass = config('otc.notifier_class');
        $notificationClass = config('otc.notification_class');
        if(!class_exists($notifierClass)) {
            $notifierClass = Notification::class;
        }
        if(!class_exists($notificationClass)) {
            $notificationClass = OneTimeCodeNotification::class;
        }
        call_user_func_array(
            [$notifierClass, 'sendNow'],
            [$related, new $notificationClass($token)]
        );
    }

    private function findOtcTokenByToken(string $token) : ?OtcToken
    {
        return OtcToken::query()
            ->where('token', $token)
            ->latest()
            ->first();
    }

    public function findOtcTokenByRelatedAndCode(Model $related, $code) : ?OtcToken
    {
        return OtcToken::query()
            ->where('related_id', $related->id)
            ->where('related_type', get_class($related))
            ->where('ip', request()->ip())
            ->where('code', $code)
            ->latest()
            ->first();
    }

    private function findOtcTokenByRelated(Model $related) : ?OtcToken
    {
        return OtcToken::query()
            ->where('related_id', $related->id)
            ->where('related_type', get_class($related))
            ->where('id', request()->ip())
            ->latest()
            ->first();
    }

    private function getModelSlug(Model $related) {
        $authenticatables = config('otc.authenticatables');
        foreach($authenticatables as $slug => $a) {
            if($a['model'] === get_class($related)) {
                return $slug;
            }
        }
        return null;
    }
}

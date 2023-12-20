<?php

namespace rohsyl\LaravelOtc;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method static boolean check()
 * @method static Response unauthorizedResponse(Model $related)
 * @method static Model|null user()
 * @method static bool auth(string $token)
 *
 * @see LaravelOtcManager
 */
class Otc extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return LaravelOtcManager::class;
    }
}

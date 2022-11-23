<?php

namespace rohsyl\LaravelOtc\Http\Controllers;

use Illuminate\Routing\Controller;
use rohsyl\LaravelOtc\LaravelOtcManager;

class AuthController extends Controller
{
    private $manager;

    public function __construct(
        LaravelOtcManager $manager,
    )
    {
        $this->manager = $manager;
    }

    public function __invoke() {

        request()->validate([
            'type'  => 'required|string',
            'identifier'    => 'required|string',
            'code'          => 'required|numeric',
        ]);

        $related = $this->manager->getModel();

        $token = $this->manager->findOtcTokenByRelatedAndCode($related, request()->code);

        if (!$this->manager->checkCode($token)) {
            return response()->json([
                'message' => 'wrong_code',
            ], 401);
        }

        $this->manager->createToken($token);

        return response()->json([
            'token' => $token->token,
        ]);

    }
}

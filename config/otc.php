<?php
return [
    'notifier_class' => \Illuminate\Support\Facades\Notification::class,
    'notification_class' => \rohsyl\LaravelOtc\Notifications\OneTimeCodeNotification::class,

    'authenticatables' => [
        'user' => [
            'model' => \App\Models\User::class,
            'identifier' => 'email',
        ]
    ]
];

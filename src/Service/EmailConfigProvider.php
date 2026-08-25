<?php

namespace App\Service;

class EmailConfigProvider
{
    private const SYSTEM_SENDER = 'support@shelfit.com';
    public const ACCOUNT_ACTIVATION_EMAIL = 'account_activation';
    public const PASSWORD_RESET_EMAIL = 'password_reset';

    private const EMAIL_CONFIG = [
        self::ACCOUNT_ACTIVATION_EMAIL => [
            'subject' => 'Welcome to Shelfit! Let\'s get your account activated',
            'template' => 'account_activation',
            'sender' => self::SYSTEM_SENDER,
        ],
        self::PASSWORD_RESET_EMAIL => [
            'subject' => 'Your password reset token (valid for '.AccountTokenService::PASSWORD_RESET_TOKEN_TTL_MINUTES.' minutes)',
            'template' => 'password_reset',
            'sender' => self::SYSTEM_SENDER,
        ]
    ];

    public function getConfig(): array
    {
        return self::EMAIL_CONFIG;
    }
}
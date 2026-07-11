<?php

$environment = (string) env(
    'APP_ENV',
    'production'
);

$testingRecipientAddress = $environment === 'local'
    ? trim(
        (string) env(
            'MAIL_TEST_RECIPIENT',
            ''
        )
    )
    : '';

$testingRecipientName = trim(
    (string) env(
        'MAIL_TEST_RECIPIENT_NAME',
        'Donor Darah Local'
    )
);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    */

    'default' => env(
        'MAIL_MAILER',
        'log'
    ),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env(
                'MAIL_HOST',
                '127.0.0.1'
            ),
            'port' => env(
                'MAIL_PORT',
                2525
            ),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url(
                    env(
                        'APP_URL',
                        'http://localhost'
                    ),
                    PHP_URL_HOST
                )
            ),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env(
                'MAIL_SENDMAIL_PATH',
                '/usr/sbin/sendmail -bs -i'
            ),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env(
                'MAIL_LOG_CHANNEL'
            ),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global From Address
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env(
            'MAIL_FROM_ADDRESS',
            'hello@example.com'
        ),
        'name' => env(
            'MAIL_FROM_NAME',
            'Example'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Testing Recipient
    |--------------------------------------------------------------------------
    |
    | Laravel MailManager membaca konfigurasi "to" ini ketika mailer dibuat.
    | Nilainya hanya diaktifkan saat APP_ENV bernilai local.
    |
    */

    'to' => $testingRecipientAddress !== ''
        ? [
            'address' => $testingRecipientAddress,
            'name' => $testingRecipientName !== ''
                ? $testingRecipientName
                : null,
        ]
        : null,

    /*
    |--------------------------------------------------------------------------
    | Local Testing Information
    |--------------------------------------------------------------------------
    */

    'testing_recipient' => [
        'enabled' => $testingRecipientAddress !== '',
        'address' => $testingRecipientAddress !== ''
            ? $testingRecipientAddress
            : null,
        'name' => $testingRecipientName !== ''
            ? $testingRecipientName
            : null,
    ],

];
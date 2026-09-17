<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Token
    |--------------------------------------------------------------------------
    |
    | Your Telegram Bot API token. Obtain this from @BotFather on Telegram.
    | NEVER hardcode this value — always read from environment variables.
    |
    */
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Default Chat ID
    |--------------------------------------------------------------------------
    |
    | The Telegram group chat ID where lab notifications are posted.
    | This is your existing lab group chat. Get the chat ID by messaging
    | @userinfobot or checking the Telegram API response.
    |
    */
    'default_chat_id' => env('TELEGRAM_DEFAULT_CHAT_ID'),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | Telegram Bot API base URL. No need to change this unless Telegram
    | changes their API endpoint.
    |
    */
    'api_url' => 'https://api.telegram.org/bot',

];

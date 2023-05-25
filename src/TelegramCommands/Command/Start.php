<?php

namespace App\TelegramCommands\Command;

use SergiX44\Nutgram\Nutgram;

class Start extends \SergiX44\Nutgram\Handlers\Type\Command
{
    protected string $command = 'start';

    protected ?string $description = 'Start command';

    public function handle(Nutgram $bot): void
    {
        $bot->sendMessage('Hello, '.$bot->user()->first_name.' '.$bot->user()->last_name.'!');
    }

}
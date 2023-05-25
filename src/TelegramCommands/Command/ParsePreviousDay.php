<?php

namespace App\TelegramCommands\Command;

use App\Service\ParseNotionDayRecord;
use Carbon\Carbon;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class ParsePreviousDay extends Command
{
    protected string $command = 'previous';

    protected ?string $description = 'Get report for the day before today';

    public function handle(Nutgram $bot): void
    {
        $bot->sendMessage('On it!');
        $date = Carbon::now('Europe/Moscow')->addDays(-1);
        $record = (new ParseNotionDayRecord())->getPages($date);

        $bot->sendMessage($record);
    }

}
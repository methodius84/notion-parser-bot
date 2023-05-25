<?php
namespace App\TelegramCommands\Command;

use App\Service\ParseNotionDayRecord;
use Carbon\Carbon;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class ParseNotion extends Command
{
    protected string $command = 'report';

    protected ?string $description = 'Get report for the last day';

    public function handle(Nutgram $bot): void
    {
        $bot->sendMessage('On it!');
        $date = Carbon::now('Europe/Moscow');
        if ($bot->user()->id != $_ENV['OWNER']){
            $bot->sendMessage('You are not my owner');
        }
        else{
            $record = "";
            try {
                $record = (new ParseNotionDayRecord())->getPages($date);
            }
            catch (\Throwable $exception){
                //debug message
                $record .= 'Something went wrong during parse action! '.$exception->getMessage();
            }
            $bot->sendMessage($record);
        }
    }
}
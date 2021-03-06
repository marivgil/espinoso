<?php

namespace App\Handlers;

use Telegram\Bot\Objects\Message;

class GifsHandler extends BaseCommand
{
    /**
     * @var bool
     */
    protected $ignorePrefix = true;
    /**
     * @var string
     */

    /**
     * @var null
     */
    protected $match = null;

    public function shouldHandle(Message $message): bool
    {
        $this->message = $message;

        $this->match = collect(trans('gifs.patterns'))->filter(function ($pattern) {
            return $this->matchCommand($pattern['pattern'], $this->message);
        });

        return $this->match->isNotEmpty();
    }

    public function handle(): void
    {
        $this->espinoso->replyGif(public_path('gifs/'.$this->match->first()['video']));
    }
}

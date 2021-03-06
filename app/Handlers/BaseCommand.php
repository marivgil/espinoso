<?php

namespace App\Handlers;

use Telegram\Bot\Objects\Message;

abstract class BaseCommand extends BaseHandler
{
    protected $flags   = 'i';
    protected $prefix  = "^(?'e'espi(noso)?\s+)"; // 'espi|espinoso '
    protected $pattern = '$';
    protected $matches = [];
    /**
     * @var bool
     * If false, should match 'espi'
     * If true, could not match 'espi'
     */
    protected $ignorePrefix = false;

    /**
     * Default behavior to determine is Command handler should response the message.
     *
     * @param Message $message
     * @return bool
     */
    public function shouldHandle(Message $message): bool
    {
        $this->message = $message;

        return $this->matchCommand($this->pattern, $this->message, $this->matches);
    }

    /*
     * Internals
     */

    /**
     * @param $pattern
     * @param Message $message
     * @param array|null $matches
     * @return bool
     */
    protected function matchCommand($pattern, Message $message, array &$matches = null): bool
    {
        $quantifier = $this->ignorePrefix ? '{0,3}' : '{1,3}';
        $text = $message->getText();
        $pattern = "/{$this->prefix}{$quantifier}{$pattern}/{$this->flags}";

        return preg_match($pattern, $text, $matches) === 1;
    }
}

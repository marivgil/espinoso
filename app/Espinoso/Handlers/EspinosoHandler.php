<?php namespace App\Espinoso\Handlers;

use Exception;
use App\Espinoso\Espinoso;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Api as ApiTelegram;
use Illuminate\Support\Facades\Log;

abstract class EspinosoHandler
{
    /**
     * @var Espinoso
     */
    protected $espinoso;
    /**
     * @var ApiTelegram
     */
    protected $telegram;

    protected $signature;
    protected $description;

    protected function help()
    {
        return empty($this->signature) ? '' : "*{$this->signature}*\n\t\t\t{$this->description}";
    }

    public function __construct(Espinoso $espinoso, ApiTelegram $telegram)
    {
        $this->espinoso = $espinoso;
        $this->telegram = $telegram;
    }

    abstract public function shouldHandle(Message $message): bool;

    abstract public function handle(Message $message);

    /**
     * @param Message $message
     */
    protected function replyNotFound(Message $message)
    {
        $this->telegram->sendMessage([
            'chat_id' => $message->getChat()->getId(),
            'text' => 'No encontré una mierda, che',
        ]);
    }

    /**
     * @param Message $message
     */
    protected function replyError(Message $message)
    {
        $this->telegram->sendMessage([
            'chat_id' => $message->getChat()->getId(),
            'text' => 'Ups! Esta cosa anda como el culo...',
        ]);
    }

    public function handleError(Exception $e, Message $message)
    {
        $clazz = get_called_class();
        Log::error($clazz);
        Log::error($message);
        Log::error($e);

        $chat = $message->getChat();
        $username = $chat->getUsername() ? " (@{$chat->getUsername()})" : "";
        $fromUser = $chat->getFirstName() . $username;

        // chat could be private, group, supergroup or channel
        $fromChat = $chat->getType() == 'private' ? $fromUser : $chat->getTitle();

        $error = "Fuck! Something blow up on {$clazz}
 - `{$e->getMessage()}`
 - *From:* {$fromUser}
 - *Chat:* {$fromChat}
 - *Text:* _{$message->getText()}_

View Log for details";

        $this->telegram->sendMessage([
            'chat_id' => config('espinoso.chat.dev'),
            'text'    => $error,
            'parse_mode' => 'Markdown',
        ]);
    }

    public function __toString()
    {
        return get_called_class();
    }


}
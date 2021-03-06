<?php

namespace App\DeliveryServices;

use App\Model\TelegramChat;
use App\Facades\GuzzleClient;
use Telegram\Bot\Objects\Chat;
use Telegram\Bot\Objects\Voice;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Api as ApiTelegram;
use Telegram\Bot\Objects\User as UserObject;
use Psr\Http\Message\StreamInterface;

/**
 * Class TelegramDelivery
 * @package App\DeliveryServices
 */
class TelegramDelivery implements EspinosoDeliveryInterface
{
    /**
     * @var ApiTelegram
     */
    protected $telegram;

    /**
     * TelegramDelivery constructor.
     * @param ApiTelegram $telegram
     */
    public function __construct(ApiTelegram $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * @return Update
     */
    public function getUpdate(): Update
    {
        $update = $this->telegram->getWebhookUpdates()->getRawResponse();
        $update['message'] = $update['message'] ?? $update['edited_message'] ?? [];
        $update = new Update($update);
        logger($update);

        return $update;
    }



    /**
     * @param array $params
     */
    public function sendMessage(array $params = []): void
    {
        $this->telegram->sendMessage($params);
    }

    /**
     * @param array $params
     */
    public function sendImage(array $params = []): void
    {
        $this->telegram->sendPhoto($params);
    }

    /**
     * @param array $params
     */
    public function sendSticker(array $params = []): void
    {
        $this->telegram->sendSticker($params);
    }

    public function sendGif(array $params = []): void
    {
        $this->telegram->sendDocument($params);
    }

    public function getFileUrl(array $params = []): string
    {
        return $this->telegram->getFile($params)->getFilePath();
    }

    public function getVoiceStream(Voice $voice): StreamInterface
    {
        $id = $voice->getFileId();
        $file = $this->getFileUrl(['file_id' => $id]);

        $response = GuzzleClient::get(
            config('espinoso.telegram.url.file')."{$file}",
            ['stream' => true]
        );

        return $response->getBody();
    }

    /**
     * Register chat and return true if new
     *
     * @param Chat $chat
     * @return bool
     */
    public function registerChat(Chat $chat): bool
    {
        /** @var TelegramChat $telegramChat */
        $telegramChat = TelegramChat::find($chat->getId());
        $isNew = empty($telegramChat);

        $telegramChat = $telegramChat ?? new TelegramChat;
        $telegramChat->id = $chat->getId();
        $telegramChat->type = $chat->getType();
        $telegramChat->title = $chat->getTitle();
        $telegramChat->username = $chat->getUsername();
        $telegramChat->first_name = $chat->getFirstName();
        $telegramChat->last_name = $chat->getLastName();
        $telegramChat->all_members_are_administrators = boolval($chat->get("all_members_are_administrators"));
        $telegramChat->photo = $chat->get("photo")->big_file_id ?? "";
        $telegramChat->description = $chat->get('description');
        $telegramChat->save();

        return $isNew;
    }

    /**
     * Delete chat
     *
     * @param Chat $chat
     */
    public function deleteChat(Chat $chat): void
    {
        $chat = TelegramChat::find($chat->getId());
        if (!empty($chat)) {
            $chat->delete();
        }
    }

    /**
     * @param UserObject $user
     * @return bool
     */
    public function isMe(UserObject $user): bool
    {
        return $user->getUsername() == $this->telegram->getMe()->getUsername();
    }

    /**
     * @param Chat $chat
     * @return bool
     */
    public function hasRegisteredChat(Chat $chat): bool
    {
        $telegramChat = TelegramChat::find($chat->getId());
        return !empty($telegramChat);
    }
}

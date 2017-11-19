<?php namespace App\Espinoso\DeliveryServices;

use Telegram\Bot\Objects\Chat;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User as UserObject;

/**
 * Interface EspinosoDeliveryInterface
 * @package App\Espinoso\DeliveryServices
 */
interface EspinosoDeliveryInterface
{
    /**
     * @param UserObject $user
     * @return bool
     */
    public function isMe(UserObject $user): bool;

    /**
     * @return Update
     */
    public function getUpdate(): Update;

    /**
     * @param array $params
     * @return mixed
     */
    public function sendMessage(array $params = []): void;

    /**
     * @param array $params
     */
    public function sendImage(array $params = []): void;

    /**
     * @param array $params
     */
    public function sendSticker(array $params = []): void;

    /**
     * @param array $params
     */
    public function sendGif(array $params = []): void;

    /**
     * Register chat and return true if new
     *
     * @param Chat $chat
     * @return bool
     */
    public function registerChat(Chat $chat): bool;

    /**
     * Delete chat
     *
     * @param Chat $chat
     */
    public function deleteChat(Chat $chat): void;

    /**
     * @param Chat $chat
     * @return bool
     */
    public function hasRegisteredChat(Chat $chat): bool;
}

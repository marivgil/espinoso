<?php namespace App\Espinoso\DeliveryServices;

use Telegram\Bot\Objects\Message;

/**
 * Interface EspinosoDeliveryInterface
 * @package App\Espinoso\DeliveryServices
 */
interface EspinosoDeliveryInterface
{
    /**
     * @return Message
     */
    public function getMessage(): Message;

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

    public function sendGif(array $params = []): void;
}
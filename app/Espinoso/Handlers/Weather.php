<?php
namespace App\Espinoso\Handlers ; 

use App\Espinoso\Helpers\Msg;
use Cmfcmf\OpenWeatherMap\Forecast;
use \DateTime;
use Gmopx\LaravelOWM\LaravelOWM;
use Mockery\CountValidator\Exception;
use Telegram\Bot\Laravel\Facades\Telegram;

class Weather extends EspinosoHandler
{
    public function shouldHandle($updates, $context=null) 
    {
        if ( ! $this->isTextMessage($updates) ) return false ; 

        return preg_match($this->regex(), $updates->message->text);
    }

    public function handle($updates, $context=null)
    {
        $day = $this->extractDay($updates->message->text);
        $dayEn = $this->translateDay($day);
        $date = $this->getNearestDateFromDay($dayEn);

        $response = $this->buildResponse($date);

        Telegram::sendMessage(Msg::html($response)->build($updates));
    }

    private function buildMessage($response, $pattern, $updates)
    {
        if ($response instanceof Msg)
            return $response->build($updates, $pattern);
        else 
            return Msg::plain($response)->build($updates, $pattern);
    }
 
    private function regex()
    {
        return "/clima[^a-z0-9]+(?:este|el)[^a-z0-9].*(?'dia'lunes|martes|miercoles|jueves|viernes|sabado|domingo).*\??/i";
    }

    /**
     * @param $updates
     * @return string
     */
    public function buildResponse(DateTime $date)
    {
        try {
            $weather = $this->getWeatherDescriptionForDate($date);
            if (empty($weather))
                throw new \Exception() ;
            $response = "está pronosticado " . $weather;
        } catch (Exception $e) {
            $response = "que se yo, forro";
        }

        return $response;
    }

    private function extractDay($text)
    {
        preg_match($this->regex(), $text, $matches);
        return $matches['dia'];
    }

    private function translateDay($day)
    {
        $days = [
            'lunes'     => 'Monday',
            'martes'    => 'Tuesday',
            'miercoles' => 'Wednesday',
            'jueves'    => 'Thursday',
            'viernes'   => 'Friday',
            'sabado'    => 'Saturday' ,
            'domingo'   => 'Sunday'
        ];
        return $days[$day];
    }

    private function getNearestDateFromDay($day) : DateTime
    {
        $time = strtotime("next $day");
        return DateTime::createFromFormat('U', $time);
    }

    private function getWeatherDescriptionForDate(DateTime $date) : string
    {
        $owm = new LaravelOWM();

        $forecasts = $owm->getWeatherForecast('Buenos Aires', "es", "metric", 10, '');

        return collect($forecasts)
            ->filter(   function (Forecast $forecast) use ($date) { return $this->isForecastForDate($date, $forecast); } )
            ->map(      function (Forecast $forecast) { return $this->forecastToDescription($forecast); } )
            ->reduce(   function ($carry, $str) { return empty($carry) ? $str : $carry . "," . $str;  } , "")
        ;
    }

    private function isForecastForDate(DateTime $date, Forecast $forecast) : bool
    {
        return $forecast->time->day->format('Y-m-d') == $date->format('Y-m-d');
    }

    private function forecastToDescription(Forecast $forecast) : string
    {
        $from = $forecast->time->from->format('H:i');
        $to = $forecast->time->to->format('H:i');
        $minTemperature = $forecast->temperature->min->getValue();
        $maxTemperature = $forecast->temperature->max->getValue();
        $description =  $forecast->weather->description;

        return "de " . $from . " a " . $to . " " . $description . " con temperaturas entre " . $minTemperature . " y " . $maxTemperature . " grados ";
    }

}



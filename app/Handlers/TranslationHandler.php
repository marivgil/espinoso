<?php

namespace App\Handlers;

use App\Facades\Translator;

class TranslationHandler extends BaseCommand
{
    protected $ignorePrefix = true;
    protected $pattern = "(?'i'\b(gt)\b)(?'query'.+)$";

    protected $signature   = "gt <cosa a traducir>";
    protected $description = "Traductor";

    public function handle(): void
    {
        $this->espinoso->reply(
            Translator::translate(trim($this->matches['query']))
        );
    }
}

<?php

declare(strict_types=1);

namespace App\BasicRum\CoreObjects\TechnicalMetrics\LoadEventEnd;

use App\BasicRum\CoreObjects\Interfaces\WriterHintInterface;

class WriterHint implements WriterHintInterface
{
    public function getFieldName(): string
    {
        return 'load_event_end';
    }

    public function getTabledName(): string
    {
        return 'rum_data_flat';
    }
}

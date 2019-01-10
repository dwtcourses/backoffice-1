<?php

declare(strict_types=1);

namespace App\BasicRum\Report\Filter;


class FilterAggregator
{

    /** @var array */
    private $filters = [];

    public function __construct()
    {
        $this->filters = [
            UserAgent::INTERNAL_IDENTIFIER   => new UserAgent(),
            Url::INTERNAL_IDENTIFIER         => new Url(),
            DeviceType::INTERNAL_IDENTIFIER  => new DeviceType(),
            OsName::INTERNAL_IDENTIFIER      => new OsName(),
            BrowserName::INTERNAL_IDENTIFIER => new BrowserName()
        ];
    }

    /**
     * @param $identifier
     * @return FilterInterface
     */
    public function getFilter($identifier)
    {
        return $this->filters[$identifier];
    }

}
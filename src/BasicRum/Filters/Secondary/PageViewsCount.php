<?php

declare(strict_types=1);

namespace App\BasicRum\Filters\Secondary;

class PageViewsCount extends AbstractFilter
{
    public function getSecondaryTableName(): string
    {
        return 'visits_overview';
    }

    public function getSecondaryKeyFieldName(): string
    {
        return 'first_page_view_id';
    }

    public function getSecondarySearchFieldName(): string
    {
        return 'page_views_count';
    }

    public function getPrimaryTableName(): string
    {
        return 'rum_data_flat';
    }

    public function getPrimarySearchFieldName(): string
    {
        return 'rum_data_id';
    }
}

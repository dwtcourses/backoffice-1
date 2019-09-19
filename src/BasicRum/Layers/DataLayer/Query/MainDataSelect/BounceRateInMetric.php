<?php

declare(strict_types=1);

namespace App\BasicRum\Layers\DataLayer\Query\MainDataSelect;

class BounceRateInMetric
    implements MainDataInterface
{

    /** @var string */
    private $tableName;

    /** @var string */
    private $fieldName;

    /** @var int */
    private $bucketSize;

    /**
     * Percentile constructor.
     * @param string $tableName
     * @param string $fieldName
     * @param int $bucketSize
     */
    public function __construct(
        string $tableName,
        string $fieldName,
        int $bucketSize
    )
    {
        $this->tableName  = $tableName;
        $this->fieldName  = $fieldName;
        $this->bucketSize = $bucketSize;
    }

    /**
     * @param string $where
     * @param array $limitWhere
     * @return string
     */
    public function getBouncedBucketsSql(string $where, array $limitWhere) : string
    {
        $limitWhereStr = implode(' AND ', $limitWhere);

        $visitsOverviewLimit = str_replace(
            'navigation_timings.page_view_id',
            'visits_overview.first_page_view_id',
            $limitWhereStr
            );

        if (!empty($where)) {
            $where = ' AND ' . $where;
        }

        return

"SELECT floor(first_paint/$this->bucketSize)*$this->bucketSize AS bin_floor, COUNT(*)
FROM navigation_timings
WHERE 
  {$limitWhereStr} AND
  page_view_id IN
  (
	SELECT visits_overview.first_page_view_id 
    FROM visits_overview
    WHERE {$visitsOverviewLimit}
		AND visits_overview.page_views_count = 1
		AND visits_overview.first_page_view_id IN
		  (
			SELECT page_view_id
			from navigation_timings
			WHERE {$limitWhereStr} {$where} AND {$this->tableName}.{$this->fieldName} > 0
		  )
  )
  
GROUP BY 1
ORDER BY 1";
    }

    /**
     * @param string $where
     * @param array $limitWhere
     * @return string
     */
    public function getAllBucketsSql(string $where, array $limitWhere) : string
    {
        $limitWhereStr = implode(' AND ', $limitWhere);

        $visitsOverviewLimit = str_replace(
            'navigation_timings.page_view_id',
            'visits_overview.first_page_view_id',
            $limitWhereStr
        );

        if (!empty($where)) {
            $where = ' AND ' . $where;
        }

        return

            "SELECT floor(first_paint/$this->bucketSize)*$this->bucketSize AS bin_floor, COUNT(*)
FROM navigation_timings
WHERE 
  {$limitWhereStr} AND
  page_view_id IN
  (
	SELECT visits_overview.first_page_view_id 
    FROM visits_overview
    WHERE {$visitsOverviewLimit}
		AND visits_overview.first_page_view_id IN
		  (
			SELECT page_view_id
			from navigation_timings
			WHERE {$limitWhereStr} {$where} AND {$this->tableName}.{$this->fieldName} > 0
		  )
  )
  
GROUP BY 1
ORDER BY 1";
    }

    /**
     * @param $connection
     * @param string $where
     * @param array $limitWhere
     * @return array
     */
    public function retrieve($connection, string $where, array $limitWhere) : array
    {
        $data = [];

        $bouncedSql = $this->getBouncedBucketsSql($where, $limitWhere);
        $allSql     = $this->getAllBucketsSql($where, $limitWhere);

        $bouncedBuckets = $connection->fetchAll($bouncedSql);
        $allBuckets     = $connection->fetchAll($allSql);

        // Make bin_floor to be a key and count value. It will be easier to work wit this array in the rest parts of the
        // application
        $data['bounced_buckets'] = $this->flattenBuckets($bouncedBuckets);
        $data['all_buckets']     = $this->flattenBuckets($allBuckets);

        return $data;
    }

    /**
     * @param array $buckets
     * @return array
     */
    private function flattenBuckets(array $buckets) : array
    {
        $flatten = [];

        foreach ($buckets as $bucket) {
            $flatten[$bucket['bin_floor']] = $bucket['COUNT(*)'];
        }

        return $flatten;
    }

}


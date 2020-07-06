<?php

declare(strict_types=1);

namespace App\BasicRum\Beacon\Importer\Process\Beacon;

use App\BasicRum\CoreObjects\ImportCollaborator;

class RumDataFlatNormalizer
{
    private $fieldsCalculation = [
        'last_byte_duration' => ['nt_res_end', 'nt_nav_st'],
        'ttfb' => ['nt_res_st', 'nt_req_st'],
        'download_time' => ['nt_res_end', 'nt_req_st'],
    ];

    private ImportCollaborator $importCollaborator;

    public function __construct()
    {
        $this->importCollaborator = new ImportCollaborator();
    }

    /**
     * @return array
     */
    public function normalize(array &$rumDataFlat)
    {
        //Rename keys and add specific key convention
        //Fix URL ... remove version
        //Add offsets
        $entries = [];

        foreach ($this->fieldsCalculation as $key => $calculationPair) {
            if (!empty($rumDataFlat[$calculationPair[0]]) && !empty($rumDataFlat[$calculationPair[1]])) {
                $entries[$key] = $rumDataFlat[$calculationPair[0]] - $rumDataFlat[$calculationPair[1]];
            } else {
                $entries[$key] = 0;
            }
        }

        /*
         * Comes from Architecture refactoring
         * Ref: https://github.com/basicrum/backoffice/issues/129
         */
        foreach ($this->importCollaborator->getCollaboratorIds() as $id) {
            $value = $this->importCollaborator
                        ->getBeaconExtract($id)
                        ->extractValue($rumDataFlat);

            $field = $this->importCollaborator
                        ->getWriterHint($id)
                        ->getFieldName();

            $entries[$field] = $value;
        }

        if (!empty($rumDataFlat['u'])) {
            $urlParts = explode('?', $rumDataFlat['u']);

            $entries['url'] = $urlParts[0];

            if (!empty($urlParts[1])) {
                $entries['query_params'] = $urlParts[1];
            }
        }

        if (!empty($rumDataFlat['user_agent'])) {
            $entries['user_agent'] = $rumDataFlat['user_agent'];
        }

        if (!empty($rumDataFlat['pid'])) {
            $entries['process_id'] = $rumDataFlat['pid'];
        }

        if (!empty($rumDataFlat['created_at'])) {
            $entries['created_at'] = $rumDataFlat['created_at'];
        }

        if (empty($rumDataFlat['stay_on_page_time'])) {
            $entries['stay_on_page_time'] = 0;
        }

        if ($entries['last_byte_duration'] < 0) {
            $entries['last_byte_duration'] = 0;
        }

        if ($entries['last_byte_duration'] > 65535) {
            $entries['last_byte_duration'] = 65535;
        }

        return $entries;
    }
}

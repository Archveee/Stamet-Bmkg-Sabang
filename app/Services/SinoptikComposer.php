<?php

namespace App\Services;

use App\Helpers\SinoptikDataHelper;
use App\Models\DataSinoptik;

class SinoptikComposer
{
    // minus verification
    
    public static function composeSinoptikMessage(DataSinoptik $dataSinoptik)
    {
        $encodedSynopWithHeader = SinoptikDataHelper::createEncodedSynopWithHeader($dataSinoptik['encoded_synop'], $dataSinoptik['data_timestamp']);
        
        return $encodedSynopWithHeader;
    }

}

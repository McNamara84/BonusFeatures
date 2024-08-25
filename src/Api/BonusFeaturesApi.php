<?php
namespace MediaWiki\Extension\BonusFeatures\Api;

use ApiBase;
use MediaWiki\Extension\BonusFeatures\Special\SpecialBonusSchauplatzStatistiken;

class BonusFeaturesApi extends ApiBase
{
    public function execute()
    {
        $params = $this->extractRequestParams();
        $specialPage = new SpecialBonusSchauplatzStatistiken();
        $result = $specialPage->getTableData($params['prefix'], $params['page']);
        $this->getResult()->addValue(null, $this->getModuleName(), $result);
    }

    public function getAllowedParams()
    {
        return [
            'prefix' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
            ],
            'page' => [
                ApiBase::PARAM_TYPE => 'integer',
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_MIN => 1,
            ],
        ];
    }
}
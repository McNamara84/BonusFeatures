<?php
namespace MediaWiki\Extension\BonusFeatures\Api;

use ApiBase;
use MediaWiki\Extension\BonusFeatures\Special\SpecialBonusSchauplatzStatistiken;
use MediaWiki\Extension\BonusFeatures\Special\SpecialBonusPersonenStatistiken;

class BonusFeaturesApi extends ApiBase
{
    public function execute()
    {
        $params = $this->extractRequestParams();
        $statisticType = $params['statisticType'];

        if ($statisticType === 'person') {
            $specialPage = new SpecialBonusPersonenStatistiken();
        } else {
            $specialPage = new SpecialBonusSchauplatzStatistiken();
        }

        $result = $specialPage->getTableData($params['prefix'], $params['page'], $statisticType);
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
            'statisticType' => [
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true,
                    // Entfernen Sie ApiBase::PARAM_DEFAULT, stattdessen:
                ApiBase::PARAM_DFLT => 'schauplatz',
            ],
        ];
    }
}
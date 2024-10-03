<?php

namespace weiperio\craftqrmanager\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

/**
 * Route condition
 */
class RouteCondition extends ElementCondition
{
    protected function selectableConditionRules(): array
    {
        return array_merge(parent::conditionRuleTypes(), [
            // ...
        ]);
    }
}

<?php

namespace weiperio\craftqrmanager\variables;

use weiperio\craftqrmanager\elements\Route;
use weiperio\craftqrmanager\elements\db\RouteQuery;
use Craft;
use yii\base\Behavior;

/**
 * The class name isn't important, but we've used something that describes
 * how it is applied, rather than what it does.
 * 
 * You are only apt to need a single behavior, even if your plugin or module
 * provides multiple element types.
 */
class CraftVariableBehavior extends Behavior
{
    public function routes(array $criteria = []): RouteQuery
    {
        // Create a query via your element type, and apply any passed criteria:
        return Craft::configure(Route::find(), $criteria);
    }
}
<?php

namespace weiperio\craftqrmanager\services;

use Craft;
use yii\base\Component;
use craft\db\Table;
use craft\base\MemoizableArray;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use craft\helpers\StringHelper;
use craft\events\ConfigEvent;
use craft\helpers\Db;
use Throwable;
use DateTime;

use weiperio\craftqrmanager\elements\Route;
use weiperio\craftqrmanager\models\Campaign;
use weiperio\craftqrmanager\records\Campaign as CampaignRecord;
use weiperio\craftqrmanager\db\Table as QrTable;

use weiperio\craftqrmanager\events\CampaignEvent;


/**
 * Routes service
 */
class Routes extends Component
{
    // Routes
    // -------------------------------------------------------------------------

    /**
     * Returns a tag by its ID.
     *
     * @param int $routeId
     * @param int|null $siteId
     * @return Route|null
     */
    public function getRouteById(int $routeId, ?int $siteId = null): ?Route
    {
        return Craft::$app->getElements()->getElementById($routeId, Route::class, $siteId);
    }

    /**
     * Returns a route by its entry URI.
     *
     * @param string $entryUri
     * @param int|null $siteId
     * @return Route|null
     */
    public function getRouteByEntryUri(string $entryUri, ?int $siteId = null): ?Route
    {
        return Route::find()
            ->siteId($siteId)
            ->entryUri($entryUri)
            ->one();
    }

    /**
     * Adds a row into the routes analytics table
     *
     * @param string $ipAddress
     * @param string $userAgent
     * @param string $referrer
     * @return mixed
     */
    public function addRouteAnalytics(int $routeId, string $userAgent, string $referrer)
    {
        $dateRouted = new DateTime();
        $db = Craft::$app->getDb();
        $db->createCommand()
            ->insert(QrTable::ROUTES_ANALYTICS, [
                'routeId' => $routeId,
                'dateRouted' => Db::prepareDateForDb($dateRouted),
                'userAgent' => $userAgent,
                'referrer' => $referrer,
            ])
            ->execute();
    }

    /**
     * Returns the total number of times a route has been accessed
     *
     * @param int $routeId
     * @return int
     */
    public function getRouteAnalyticsTotal(int $routeId): int
    {
        // Create raw SQL query
        $query = 'SELECT COUNT(*) FROM ' . QrTable::ROUTES_ANALYTICS . ' WHERE routeId = :routeId';
        $params = [':routeId' => $routeId];

        // Execute query
        $result = Craft::$app->getDb()->createCommand($query, $params)->queryScalar();

        return $result;
    }

    /**
     * Returns the number of times a route has been accessed each day
     *
     * @param int $routeId
     * @param int $numberOfDays
     * @return array
     */
    public function getRouteDailyAnalytics(int $routeId, int $numberOfDays): array
    {
        // Create raw SQL query
        $query = 'SELECT DATE(dateRouted) AS date, COUNT(*) AS count FROM ' . QrTable::ROUTES_ANALYTICS . ' WHERE routeId = :routeId AND dateRouted >= DATE_SUB(CURDATE(), INTERVAL :numberOfDays DAY) GROUP BY DATE(dateRouted)';
        $params = [':routeId' => $routeId, ':numberOfDays' => $numberOfDays];

        // Execute query
        $result = Craft::$app->getDb()->createCommand($query, $params)->queryAll();

        return $result;
    }

    /**
     * Deletes a route by its ID.
     * 
     * @param int $routeId
     * @return bool
     */
    public function deleteRouteById(int $routeId): bool
    {
        $route = $this->getRouteById($routeId);

        if (!$route) {
            return false;
        }

        return Craft::$app->getElements()->deleteElement($route);
    }
}

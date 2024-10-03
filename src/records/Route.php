<?php

namespace craft\records;

use craft\db\ActiveRecord;
use craft\db\Table;
use yii\db\ActiveQueryInterface;

/**
 * Route record.
 *
 * @property int $id ID
 * @property int $groupId Group ID
 * @property Element $element Element
 * @property Campaign $campaign Campaign
 */
class Route extends ActiveRecord
{
    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(): string
    {
        return Table::ROUTES;
    }

    /**
     * Returns the route's element.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }

    /**
     * Returns the route's campaign
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getCampaign(): ActiveQueryInterface
    {
        return $this->hasOne(Campaign::class, ['id' => 'groupId']);
    }
}

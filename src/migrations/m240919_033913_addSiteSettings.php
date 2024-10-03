<?php

namespace weiperio\craftqrmanager\migrations;

use Craft;
use craft\db\Migration;

use weiperio\craftqrmanager\db\Table;

/**
 * m240919_033913_addSiteSettings migration.
 */
class m240919_033913_addSiteSettings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {

        // Create site settings table
        $this->createTable(Table::SITE_SETTINGS, [
            'id' => $this->primaryKey(),
            'settings' => $this->json()
        ]);

        // Give it a foreign key to the routes table:
        $this->addForeignKey(
            null,
            Table::SITE_SETTINGS,
            'id',
            '{{%sites}}',
            'id',
            'CASCADE',
            null
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m240919_033913_add_site_settings cannot be reverted.\n";
        return false;
    }
}

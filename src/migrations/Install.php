<?php

namespace weiperio\craftqrmanager\migrations;

use Craft;
use craft\db\Migration;

use weiperio\craftqrmanager\db\Table;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Create routes table
        $this->createTable(Table::ROUTES, [
            'id' => $this->primaryKey(),
            'entryUri' => $this->string(),
            'redirectUri' => $this->string(),
            'deletedWithCampaign' => $this->boolean()->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
        ]);

        // Give it a foreign key to the routes table:
        $this->addForeignKey(
            null,
            Table::ROUTES,
            'id',
            '{{%elements}}',
            'id',
            'CASCADE',
            null
        );

        // Create analytics table
        $this->createTable(Table::ROUTES_ANALYTICS, [
            'id' => $this->primaryKey(),
            'routeId' => $this->integer()->notNull(),
            'dateRouted' => $this->dateTime()->notNull(),
            'userAgent' => $this->string(),
            'referrer' => $this->string()
        ]);

        // Give it a foreign key to the routes table:
        $this->addForeignKey(
            null,
            Table::ROUTES_ANALYTICS,
            'routeId',
            Table::ROUTES,
            'id',
            'CASCADE',
            null
        );

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
        // Drop the qr_manager_routes table
        $this->dropTableIfExists(Table::ROUTES_ANALYTICS);

        // Drop the qr_manager_routes table
        $this->dropTableIfExists(Table::ROUTES);

        // Drop the qr_manager_routes table
        $this->dropTableIfExists(Table::SITE_SETTINGS);

        return true;
    }
}

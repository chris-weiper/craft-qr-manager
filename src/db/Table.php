<?php

namespace weiperio\craftqrmanager\db;

/**
 * This class provides constants for defining Craft’s database table names based upon Pixel & Tonic's Table class.
 */
abstract class Table
{
    /** @since 1.0.0 */
    public const ROUTES = '{{%qr_manager_routes}}';
    public const ROUTES_ANALYTICS = '{{%qr_manager_routes_analytics}}';

}

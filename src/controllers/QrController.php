<?php

namespace weiperio\craftqrmanager\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;

/**
 * Qr controller
 */
class QrController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * qr-manager/qr action
     */
    public function actionIndex(): Response
    {
        // ...
    }
}

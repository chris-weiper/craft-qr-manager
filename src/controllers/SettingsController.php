<?php

namespace weiperio\craftqrmanager\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;

use weiperio\craftqrmanager\QrManager;

/**
 * Settings controller
 */
class SettingsController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * qr-manager/settings/save-site-settings action
     */
    public function actionSaveSiteSettings(): Response
    {
        // Get the siteHandle from the request
        $siteHandle = Craft::$app->getRequest()->getBodyParam('siteHandle');
        // Get the site id using the siteHandle
        $siteId = Craft::$app->getSites()->getSiteByHandle($siteHandle)->id;

        // Get the foregroundColor from the request
        $foregroundColor = Craft::$app->getRequest()->getBodyParam('foregroundColor');

        // Get the backgroundColor from the request
        $backgroundColor = Craft::$app->getRequest()->getBodyParam('backgroundColor');

        // Get the logo from the request
        $logo = Craft::$app->getRequest()->getBodyParam('logo');

        // Get the logoSize from the request
        $logoSize = Craft::$app->getRequest()->getBodyParam('logoSize');

        // Get the logoMargin from the request
        $logoMargin = Craft::$app->getRequest()->getBodyParam('logoMargin');

        // Get the dotOptions from the request
        $dotOptions = Craft::$app->getRequest()->getBodyParam('dotOptions');

        // Get the errorCorrectionLevel from the request
        $errorCorrectionLevel = Craft::$app->getRequest()->getBodyParam('errorCorrectionLevel');

        // Save the settings using the Settings service
        QrManager::getInstance()->settingsService->saveSiteSettings($siteId, $foregroundColor, $backgroundColor, $logo, $logoSize, $logoMargin, $dotOptions, $errorCorrectionLevel);

        // Use the redirect input to redirect back to the settings page
        return $this->redirect(Craft::$app->getRequest()->getBodyParam('redirect'));
    }
}

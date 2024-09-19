<?php

namespace weiperio\craftqrmanager\services;

use Craft;
use yii\base\Component;

use weiperio\craftqrmanager\models\Settings as SettingsModel;
use weiperio\craftqrmanager\db\Table as QrTable;

/**
 * Settings service
 */
class Settings extends Component
{
    public function saveSiteSettings($sideId, $foregroundColor, $backgroundColor, $logo, $logoSize, $logoMargin, $dotOptions, $errorCorrectionLevel)
    {
        // Init a settings model for verification
        $settings = new SettingsModel();
        $settings->foregroundColor = $foregroundColor;
        $settings->backgroundColor = $backgroundColor;
        $settings->logo = $logo;
        $settings->logoSize = $logoSize;
        $settings->logoMargin = $logoMargin;
        $settings->dotOptions = $dotOptions;
        $settings->errorCorrectionLevel = $errorCorrectionLevel;

        // Validate the settings model
        if (!$settings->validate()) {
            Craft::$app->getSession()->setError(Craft::t('qr-manager', 'Couldn’t save settings.'));

            return null;
        }

        // Save the settings
        $db = Craft::$app->getDb();
        $db->createCommand()
            ->upsert(QrTable::SITE_SETTINGS, [
                'id' => $sideId,
                'settings' => json_encode($settings->toArray()),
            ])
            ->execute();

        Craft::$app->getSession()->setNotice(Craft::t('qr-manager', 'Settings saved.'));

        return $settings;
    }

    public function getSiteSettings($siteId)
    {
        $result = Craft::$app->getDb()->createCommand('SELECT settings FROM ' . QrTable::SITE_SETTINGS . ' WHERE id = :id', [':id' => $siteId])->queryOne();

        if ($result) {
            return json_decode(json_decode($result['settings']));
        }

        return new SettingsModel();
    }
}

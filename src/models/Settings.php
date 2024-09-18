<?php

namespace weiperio\craftqrmanager\models;

use Craft;
use craft\base\Model;

/**
 * QR Manager settings
 */
class Settings extends Model
{
    public $foregroundColor = '#000000';
    public $backgroundColor = '#FFFFFF';
    public $errorCorrectionLevel = 'H';
    public $logo = '';
    public $logoSize = 0.5;
    public $logoMargin = 10;

    public function defineRules(): array
    {
        return [
            [['foregroundColor', 'backgroundColor', 'errorCorrectionLevel'], 'required'],
            // ...
        ];
    }
}

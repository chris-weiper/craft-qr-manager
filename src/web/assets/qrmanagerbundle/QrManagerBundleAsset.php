<?php

namespace weiperio\craftqrmanager\web\assets\qrmanagerbundle;

use Craft;
use craft\web\AssetBundle;

/**
 * Qr Manager Bundle asset bundle
 */
class QrManagerBundleAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/dist';
    public $depends = [];
    public $js = [];
    public $css = [];

    public function init()
    {
        $this->js = [
            'qrmanager.js',
        ];

        $this->css = [
            'css/qrmanager.css',
        ];

        parent::init();
    }
}

<?php

namespace weiperio\craftqrmanager\models;

use Craft;
use craft\base\Model;

use weiperio\craftqrmanager\elements\Route as RouteElement;

/**
 * Route model
 */
class Route extends Model
{
    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            [['entryUri', 'redirectUri'], 'safe'],
            [['entryUri', 'redirectUri'], 'string'],
            [['entryUri', 'redirectUri'], function($attribute, $params)
            {
                $value = $this->$attribute;

                // Split the URI into scheme, host, and path
                $parts = parse_url($value);

                // Ensure scheme, host, or path is present
                if (!isset($parts['scheme']) && !isset($parts['host']) && !isset($parts['path'])) {
                    $this->addError($attribute, 'Please enter a valid URI.');
                    return;
                }
            }],
            [['entryUri', 'redirectUri'], function($attribute, $params) {
                // Make sure there is not a route belonging to the same site with the same entryUri or redirectUri
                // Get the current site ID
                // $siteId = Craft::$app->getSites()->currentSite->id;
                // $site = Craft::$app->getRequest()->getParam('site');
                // $siteId = Craft::$app->getSites()->getSiteByHandle($site)->id;

                // // Query for existing routes within this site that match either the entryUri or redirectUri,
                // // Join the elements table to get the site ID
                // // but exclude the current route if it has an ID
                // $query = RouteElement::find()
                //     ->siteId($siteId)
                //     ->andWhere(['or', ['entryUri' => $this->entryUri], ['redirectUri' => $this->redirectUri]])
                //     ->andWhere(['not', ['id' => $this->id]]);

                // $existingRoute = $query->one();

                // // If an existing route is found, add an error
                // if ($existingRoute) {
                //     $this->addError($attribute, 'A route with the same {attribute} already exists for this site.');
                // } else {
                //     $this->validate();
                // }
            }],
        ]);
    }

    // Safe attributes
    public function safeAttributes(): array
    {
        return [
            'entryUri',
            'redirectUri',
        ];
    }

    public $id;
    public $title;
    public $entryUri;
    public $redirectUri;
}

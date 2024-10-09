<?php

namespace weiperio\craftqrmanager\models;

use Craft;
use craft\base\Model;

use League\Uri\Uri;

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

                try {
                    $uri = Uri::createFromString($value);
            
                    // Ensure scheme, host, or path is present
                    if (!$uri->getScheme() && !$uri->getHost() && !$uri->getPath()) {
                        $this->addError($attribute, 'Please enter a valid URI.');
                        return;
                    }
                } catch (\League\Uri\Exception $e) {
                    $this->addError($attribute, 'Please enter a valid URI.');
                }
            }],
            [['entryUri'], function($attribute, $params) {
                // Make sure there is not a route belonging to the same site with the same entryUri or redirectUri
                // Get the current site ID
                $siteId = Craft::$app->getSites()->currentSite->id;
                $site = Craft::$app->getRequest()->getParam('site');
                $siteId = Craft::$app->getSites()->getSiteByHandle($site)->id;
                $elementId = Craft::$app->getRequest()->getParam('elementId');

                // Query for existing routes within this site that match either the entryUri or redirectUri,
                // Join the elements table to get the site ID
                // but exclude the current route if it has an ID
                $query = RouteElement::find()
                    ->id('not ' . $elementId)
                    ->siteId($siteId)
                    ->andWhere(['or', ['entryUri' => $this->entryUri]]);

                $existingRoute = $query->one();

                // If an existing route is found, add an error
                if ($existingRoute) {
                    $this->addError($attribute, 'A route with the same entryUri already exists for this site.');
                }

                return;
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

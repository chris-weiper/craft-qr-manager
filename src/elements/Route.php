<?php

namespace weiperio\craftqrmanager\elements;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\User;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\ElementQueryInterface;
use craft\enums\MenuItemType;
use craft\models\FieldLayout;
use craft\helpers\Cp;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use craft\web\CpScreenResponseBehavior;
use craft\events\DefineFieldLayoutFieldsEvent;
use craft\fieldlayoutelements\TextField;
use craft\fieldlayoutelements\TitleField;

use yii\base\Event;

use weiperio\craftqrmanager\elements\conditions\RouteCondition;
use weiperio\craftqrmanager\elements\db\RouteQuery;
use weiperio\craftqrmanager\models\Route as RouteModel;
use yii\web\Response;

use weiperio\craftqrmanager\QrManager;
use weiperio\craftqrmanager\db\Table;

/**
 * Route element type
 */
class Route extends Element implements ElementInterface
{

    // Public Properties
    public string $entryUri = '';
    public string $redirectUri = '';

    public static function displayName(): string
    {
        return Craft::t('qr-manager', 'Route');
    }

    public static function lowerDisplayName(): string
    {
        return Craft::t('qr-manager', 'route');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('qr-manager', 'Routes');
    }

    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('qr-manager', 'routes');
    }

    public static function refHandle(): ?string
    {
        return 'route';
    }

    public static function trackChanges(): bool
    {
        return false;
    }

    public static function hasTitles(): bool
    {
        return true;
    }

    public static function hasUris(): bool
    {
        return false;
    }

    public static function isLocalized(): bool
    {
        return true;
    }

    public static function hasStatuses(): bool
    {
        return true;
    }

    public static function find(): ElementQueryInterface
    {
        return Craft::createObject(RouteQuery::class, [static::class]);
    }

    public static function createCondition(): ElementConditionInterface
    {
        return Craft::createObject(RouteCondition::class, [static::class]);
    }

    protected static function defineSources(string $context): array
    {   

        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('qr-manager', 'All routes'),
            ],
        ];

        // $campaigns = QrManager::getInstance()->campaign->getAllCampaigns();

        // foreach ($campaigns as $campaign) {
        //     $sources[] = [
        //         'key' => $campaign->id,
        //         'label' => Craft::t('site', $campaign->name),
        //         'data' => ['handle' => $campaign->handle],
        //         'criteria' => ['campaignId' => $campaign->id]
        //     ];
        // }

        return $sources;
    }

    protected static function defineActions(string $source): array
    {
        // List any bulk element actions here
        return [];
    }

    protected static function includeSetStatusAction(): bool
    {
        return true;
    }

    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('app', 'Title'),
            'slug' => Craft::t('app', 'Slug'),
            'uri' => Craft::t('app', 'URI'),
            [
                'label' => Craft::t('app', 'Date Created'),
                'orderBy' => 'elements.dateCreated',
                'attribute' => 'dateCreated',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('app', 'Date Updated'),
                'orderBy' => 'elements.dateUpdated',
                'attribute' => 'dateUpdated',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('app', 'ID'),
                'orderBy' => 'elements.id',
                'attribute' => 'id',
            ],
            // ...
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'slug' => ['label' => Craft::t('app', 'Slug')],
            'uri' => ['label' => Craft::t('app', 'URI')],
            'link' => ['label' => Craft::t('app', 'Link'), 'icon' => 'world'],
            'id' => ['label' => Craft::t('app', 'ID')],
            'uid' => ['label' => Craft::t('app', 'UID')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('app', 'Date Updated')],
            'entryUri' => 'Entry URI',
            'redirectUri' => 'Redirect URI',
            'redirects' => 'Redirects',
            // ...
        ];
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'redirects',
            'entryUri',
            'redirectUri',
            'dateCreated',
            // ...
        ];
    }

    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $model = new RouteModel();
        $rules = array_merge($rules, $model->rules());
        
        return $rules;
    }

    public function getRedirects(): int
    {
        // Get the total number of redirects for this route
        return QrManager::getInstance()->routes->getRouteAnalyticsTotal($this->id);
    }

    public function getUriFormat(): ?string
    {
        // If routes should have URLs, define their URI format here
        return null;
    }

    public function getSupportedSites(): array
    {
        // If the element should be available in multiple sites, list the supported site IDs here
        return [$this->siteId];
    }

    protected function previewTargets(): array
    {
        $previewTargets = [];
        $url = $this->getUrl();
        if ($url) {
            $previewTargets[] = [
                'label' => Craft::t('app', 'Primary {type} page', [
                    'type' => self::lowerDisplayName(),
                ]),
                'url' => $url,
            ];
        }
        return $previewTargets;
    }

    protected function route(): array|string|null
    {
        // Define how routes should be routed when their URLs are requested
        return [
            'templates/render',
            [
                'template' => 'site/template/path',
                'variables' => ['route' => $this],
            ]
        ];
    }

    public function canView(User $user): bool
    {
        if (parent::canView($user)) {
            return true;
        }
        // todo: implement user permissions
        return $user->can('viewRoutes');
    }

    public function canSave(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }
        // todo: implement user permissions
        return $user->can('saveRoutes');
    }

    public function canDuplicate(User $user): bool
    {
        if (parent::canDuplicate($user)) {
            return true;
        }
        // todo: implement user permissions
        return $user->can('saveRoutes');
    }

    public function canDelete(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }
        // todo: implement user permissions
        return $user->can('deleteRoutes');
    }

    public function canCreateDrafts(User $user): bool
    {
        return false;
    }

    protected function cpEditUrl(): ?string
    {
        return sprintf('qr-manager/routes/edit/%s', $this->getCanonicalId());
    }

    public function getPostEditUrl(): ?string
    {
        return UrlHelper::cpUrl('qr-manager/routes');
    }

    public function metaFieldsHtml(bool $static): string
    {

        $site = Craft::$app->getSites()->getCurrentSite();
        $siteId = $site->id;
        // Check if the request has a siteHandle
        $siteHandle = Craft::$app->getRequest()->getParam('site');
        if ($siteHandle) {
            // Get the site id using the siteHandle
            $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);
            $siteId = $site->id;
        }
        $siteSettings = QrManager::getInstance()->settingsService->getSiteSettings($siteId);

        // Get the route analytics
        $routeAnalytics = QrManager::getInstance()->routes->getRouteDailyAnalytics($this->id, 5);

        // Render the metaFields twig file
        return Craft::$app->getView()->renderTemplate('qr-manager/routes/_metafields', [
            'element' => $this,
            'id' => $this->id,
            'entryUri' => $this->entryUri,
            'settings' => $siteSettings,
            'siteUrl' => $site->baseUrl,
            'routeAnalytics' => $routeAnalytics,
        ]);

    }


    public function metadata(): array
    {
        // Get the route analytics
        $totalRedirects = QrManager::getInstance()->routes->getRouteAnalyticsTotal($this->id);

        // Render the metadata twig file
        return [
            'Redirects' => $totalRedirects,
        ];
    }

    public function getCardBodyHtml(): string
    {
        // Render the cardBody twig file
        return Craft::$app->getView()->renderTemplate('qr-manager/routes/_card', [
            'route' => $this,
            'id' => $this->id,
            'entryUri' => $this->entryUri,
        ]);
    }

    public function getActionMenuItems(): array
    {
        // Render the route action menuItem 
        return [
            array(
                'label' => Craft::t('qr-manager', 'Edit'),
                'attributes' => [
                    'data-qr-manager-edit' => $this->id
                ],
                'icon' => 'edit',
            ),
            array(
                'label' => Craft::t('qr-manager', 'Download QR Code'),
                'id' => 'qr-code-download-' . $this->id,
                'attributes' => [
                    'data-qr-manager-download' => $this->entryUri,
                    'data-qr-manager-download-name' => $this->title
                ],
                'icon' => 'download'
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout(): ?FieldLayout
    {
        Event::on(
            FieldLayout::class,
            FieldLayout::EVENT_DEFINE_NATIVE_FIELDS,
            function(DefineFieldLayoutFieldsEvent $event) {
                /** @var FieldLayout $fieldLayout */
                $fieldLayout = $event->sender;
        
                // We only want to provide these options to our product field layouts:
                if ($fieldLayout->type !== self::class) {
                    return;
                }
        
                // Add a title field:
                $event->fields[] = TitleField::class;
        
                // Add an "entryUrl" field:
                $event->fields[] = [
                    'class' => TextField::class,
                    'attribute' => 'entryUri',
                    'type' => 'text',
                    'mandatory' => true,
                    'required' => true,
                    'label' => 'Entry URI',
                    'instructions' => 'The URI on this site that you want to redirect from.',
                    'placeholder' => '/path/to/entry'
                ];
                // Add a "redirectUri" field:
                $event->fields[] = [
                    'class' => TextField::class,
                    'attribute' => 'redirectUri',
                    'type' => 'text',
                    'mandatory' => true,
                    'required' => true,
                    'label' => 'Redirect URI',
                    'instructions' => 'The URI on this site that you want to redirect to.',
                    'placeholder' => '/path/to/redirect',
                ];
            }
        );

        // return parent::getFieldLayout() ?? $this->getCampaign()->getFieldLayout();
        $fieldLayout = \Craft::$app->getFields()->getLayoutByType(self::class);

        return $fieldLayout;
        
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        // $validates = parent::validate(['entryUri', 'redirectUri'], $clearErrors);

        // if ($validates) {
            $model = new RouteModel();
            $model->setAttributes($this->getAttributes(['entryUri', 'redirectUri']), false); // Skip unsafe attributes

            if ($model->validate()) {
                return true;
            } else {
                $this->addErrors($model->getErrors());
                return false;
            }
        // }

        // return $validates;
    }

    public function prepareEditScreen(Response $response, string $containerId): void
    {
        /** @var Response|CpScreenResponseBehavior $response */
        $response->crumbs([
            [
                'label' => self::pluralDisplayName(),
                'url' => UrlHelper::cpUrl('qr-manager/routes'),
            ],
        ]);
    }

    public function afterSave(bool $isNew): void
    {
        if (!$this->propagating) {

            $request = Craft::$app->getRequest();
                
            // Get the validated model data
            $model = new RouteModel();
            $model->setAttributes($request->getBodyParams(), false); // Skip unsafe attributes

            if ($model->validate()) {

                // Directly set attributes on the element from the validated model
                foreach ($model->getAttributes() as $attribute => $value) {
                    if ( $value != null ) {
                        $this->{$attribute} = $value;
                    }
                }

                // Update the qr_manager_routes table
                $data = [
                    'entryUri' => $this->entryUri,
                    'redirectUri' => $this->redirectUri,
                ];

                // Save the custom element data to the qr_manager_routes table
                Db::upsert(Table::ROUTES, ['id' => $this->id], $data);

            } else {
                // Handle validation errors from the model
                Craft::error('Could not save the element due to validation errors.', __METHOD__);
                // You can access $model->getErrors() to get the specific errors
            }
        }

        parent::afterSave($isNew);
    }
}

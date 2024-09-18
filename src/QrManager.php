<?php

namespace weiperio\craftqrmanager;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\base\Element;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\DefineHtmlEvent;
use craft\services\Elements;
use craft\web\ErrorHandler;
use craft\web\Response;
use craft\web\UrlManager;
use craft\helpers\Cp as CpHelper;
use yii\base\Application;
use craft\web\twig\variables\Cp;
use weiperio\craftqrmanager\elements\Route;
use weiperio\craftqrmanager\models\Settings;
use weiperio\craftqrmanager\services\QrService;
use weiperio\craftqrmanager\services\Routes;
use yii\base\Event;


/**
 * QR Manager plugin
 *
 * @method static QrManager getInstance()
 * @method Settings getSettings()
 * @author weiperio <chris.weiper@gmail.com>
 * @copyright weiperio
 * @license https://craftcms.github.io/license/ Craft License
 * @property-read QrService $qrService
 * @property-read Routes $routes
 */
class QrManager extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => ['qrService' => QrService::class, 'routes' => Routes::class],
        ];
    }

    public function init(): void
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('qr-manager/_settings', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }
    protected function cpNavIconPath(): ?string
    {
        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . 'src/qr-manager-icon.svg';

        return is_file($path) ? $path : null;
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = Route::class;
        });

        // Register CP URL rules
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules['qr-manager/routes'] = ['template' => 'qr-manager/routes/_index.twig'];
            $event->rules['qr-manager/routes/create/?'] = 'qr-manager/routes/create';
            $event->rules['qr-manager/routes/edit/?'] = 'qr-manager/routes/edit';
            $event->rules['qr-manager/routes/edit/<routeId:\\d+>'] = 'qr-manager/routes/edit';
        });

        // Register CP Nav item
        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, function(RegisterCpNavItemsEvent $event) {
            $event->navItems[] = [
                'url' => 'qr-manager/routes',
                'label' => 'QR Manager',
                'icon' => '@weiperio/craftqrmanager/qr-manager-icon.svg',
                'subnav' => [
                    'routes' => ['label' => 'Routes', 'url' => 'qr-manager/routes'],
                    'settings' => ['label' => 'Settings', 'url' => '/admin/settings/plugins/qr-manager'],
                ],
            ];
        });

        // Register additional button on Entry edit page
        Event::on(Element::class, Element::EVENT_DEFINE_ADDITIONAL_BUTTONS, function(DefineHtmlEvent $event) {
            $element = $event->sender;
            if ($element instanceof \craft\elements\Entry ) {
                $event->html = Craft::$app->getView()->renderTemplate('qr-manager/entries/_button', ['redirectUri' => $element->uri]);
            }
        });

        // Register additional meta data on Entry edit page
        Event::on(Element::class, Element::EVENT_DEFINE_SIDEBAR_HTML, function(DefineHtmlEvent $event) {
            $element = $event->sender;
            if ($element instanceof \craft\elements\Entry ) {
                // Get all the routes that have a redirectUri that matches the entry's URI and belong to the current site
                $routes = Route::find()
                    ->siteId($element->siteId)
                    ->redirectUri($element->uri)
                    ->all();
                $routeElementChips = [];
                foreach ($routes as $route) {
                    array_push($routeElementChips, CpHelper::elementCardHtml($route, [
                        'showActionMenu' => true,
                        'context' => 'field',
                        'autoReload' => true
                    ]));
                }
                // Render the template
                $event->html .= Craft::$app->getView()->renderTemplate('qr-manager/entries/_meta', ['routes' => $routes, 'chips' => $routeElementChips]);
            }
        });

        // Register element type
        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function (RegisterComponentTypesEvent $event) {
        });

        // Throw error on 404 event
        Event::on(
            Response::class,
            Response::EVENT_BEFORE_SEND, function() {
            // Get request path
            $request = Craft::$app->getRequest();
            $path = $request->getPathInfo(); // Get the requested path

            // Check if Craft would resolve the path to a template (false indicates potential 404)
            if (!Craft::$app->getView()->resolveTemplate($path)) {

                // Run your custom query based on $path (replace with your actual query logic)
                $route = Route::find()
                    ->siteId('*')
                    ->entryUri($path)
                    ->one();

                if ($route) {
                    // Found an entry, gather analytics data
                    $ipAddress = $request->getUserIP();
                    $userAgent = $request->getUserAgent();
                    $referer = $request->getReferrer() ?? "";

                    // Add the analytics data
                    QrManager::getInstance()->routes->addRouteAnalytics($route->id, $ipAddress, $userAgent, $referer);
                    
                    // So redirect or render as needed
                    return Craft::$app->getResponse()->redirect($route->redirectUri); // Example redirection
                }
            }
        });
    }
}

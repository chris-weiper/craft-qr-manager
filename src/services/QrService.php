<?php

namespace weiperio\craftqrmanager\services;

use Craft;
use yii\base\Component;

/**
 * Qr Service service
 */
class QrService extends Component
{
    public function getRouteCardHtml($route)
    {
        $html = '';
        $html .= '<div class="card">';
        $html .= '<div class="card-header">';
        $html .= '<h3 class="card-title">' . $route->title . '</h3>';
        $html .= '</div>';
        $html .= '<div class="card-body">';
        $html .= '<p>' . $route->redirectUri . '</p>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}

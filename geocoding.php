<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Plugin\Geocoding;

/**
 * Class GeocodingPlugin
 * @package Grav\Plugin
 */
class GeocodingPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => [
                ['onPluginsInitialized', 0]
            ]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main events we are interested in
        $this->enable([
            // Put your main events here
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
        ]);
    }

    /**
     * Add geocoding functions to twig templates
     */
    public function onTwigSiteVariables()
    {
        require_once __DIR__ . '/classes/geocoding.php';

        $twig = $this->grav['twig'];
        $twig->twig_vars['geocoding'] = new Geocoding();
    }
}

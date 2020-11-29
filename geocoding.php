<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use Grav\Plugin\Geocoding\Geocoding;

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
                ['autoload', 100000], // TODO: Remove when plugin requires Grav >=1.7
                ['register', 1000],
                ['onPluginsInitialized', 0]
            ]
        ];
    }

    /**
     * Register the service
     */
    public function register()
    {
        $this->grav['geocoding'] = function ($c) {
            /** @var Config $config */
            $config = $c['config'];

            return new Geocoding($config->get('plugins.geocoding'));
        };
    }

    /**
    * Composer autoload.
    *is
    * @return ClassLoader
    */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            $this->active = false;
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
        $twig = $this->grav['twig'];
        $twig->twig_vars['geocoding'] = $this->grav['geocoding'];
    }
}

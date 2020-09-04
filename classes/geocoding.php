<?php
namespace Grav\Plugin;

use Grav\Common\GPM\Response;
use Grav\Common\Grav;
use Grav\Common\Language\Language;

class Geocoding
{
    // Grav instance
    private $grav;

    // The geocoding server
    private $nominatim_host;

    // Gecoding Nominatim REST API
    // https://nominatim.org/release-docs/develop/api/Search/
    // https://nominatim.org/release-docs/develop/api/Output/
    // Example: https://nominatim.openstreetmap.org/search/berlin?format=jsonv2&limit=1&countrycodes=de&addressdetails=1
    private $nominatim_api = '/{QUERY}?format=jsonv2&limit=1&countrycodes={COUNTRY}&addressdetails=1';


    public function __construct()
    {
        $this->grav = Grav::instance();
        $this->cache = $this->grav['cache'];
        $this->config = $this->grav['config'];

        $this->nominatim_host = $this->config->get('plugins.geocoding.nominatim_host', 'https://nominatim.openstreetmap.org/search');
        $this->country_code = $this->config->get('plugins.geocoding.country_code', '');
    }

    /**
     * Get location data of the requested query string.
     * Return null on no result.
     *
     * @return array
     */
    public function getLocation(string $query = null)
    {
        try {
            $cache = $this->cache;
            $hash = hash('sha256', $query);

            // Cache gecoding results, but store them as hash
            if ($location = $cache->fetch($hash)) {
                return $location;
            }

            // Call Nominatim REST API
            $request = $this->nominatim_host . $this->nominatim_api;
            $request = str_replace('{QUERY}', urlencode($query), $request);
            $request = str_replace('{COUNTRY}', urlencode($this->country_code), $request);
            $response = Response::get($request);
            $data = json_decode($response);

            // Format result
            $location = array(
                "lat" => $data[0]->lat,
                "lon" => $data[0]->lon,
                "name" => $data[0]->display_name
            );

            // Store result in cache
            $cache->save($hash, $location);
            return $location;
        }

        catch (\Exception $e) {
            return null;
        }
    }
}

<?php
namespace Grav\Plugin\Geocoding;

use Grav\Common\GPM\Response;
use Grav\Common\Grav;
use Grav\Common\Config\Config;
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

    /** @var Config $config */
    protected $config;

    public function __construct($config)
    {
        $this->grav = Grav::instance();
        $this->cache = $this->grav['cache'];
        $this->config = new Config($config);

        $this->nominatim_host = $this->config->get('nominatim_host', 'https://nominatim.openstreetmap.org/search');
        $this->country_code = $this->config->get('country_code', '');
    }

    /**
     * Get location data of the requested query string.
     * Return null on no result.
     *
     * @return array
     */
    public function getLocation(string $query = null, string $country_code = null) : ?GeoLocation
    {
        if ($country_code === null) {
            $country_code = $this->country_code;
        }

        try {
            // Cache key allows us to invalidate all cache on configuration changes.
            $cache = $this->cache;
            $cache_id = hash('sha256', 'geocoding-location' . $cache->getKey() . '-' . $query);

            // Cache gecoding results, but store them as hash
            if ($location = $cache->fetch($cache_id)) {
                return $location;
            }

            // Call Nominatim REST API
            $request = $this->nominatim_host . $this->nominatim_api;
            $request = str_replace('{QUERY}', rawurlencode($query), $request);
            $request = str_replace('{COUNTRY}', rawurlencode($country_code), $request);
            $response = Response::get($request);
            $data = json_decode($response);

            $location = new GeoLocation();
            $location->lat = $data[0]->lat;
            $location->lon = $data[0]->lon;
            $location->name = $data[0]->display_name;

            // Store result in cache for 7 days
            $cache->save($cache_id, $location, 604800);
            return $location;
        }

        catch (\Exception $e) {
            return null;
        }
    }

    /**
    * Calculates the great-circle distance between two points, with
    * the Vincenty formula.
    * https://stackoverflow.com/questions/10053358
    * @param float $latitudeFrom Latitude of start point in [deg decimal]
    * @param float $longitudeFrom Longitude of start point in [deg decimal]
    * @param float $latitudeTo Latitude of target point in [deg decimal]
    * @param float $longitudeTo Longitude of target point in [deg decimal]
    * @return float Distance between points in [m] (same as earthRadius)
    */
    public function getDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
    {
        // Cache key allows us to invalidate all cache on configuration changes.
        $cache = $this->cache;
        $cache_id = hash('sha256', 'geocoding-distance' . $cache->getKey() .
          '-' . $latitudeFrom . '-' . $longitudeFrom .
          '-' . $latitudeTo . '-' . $longitudeTo);

        // Cache gecoding results, but store them as hash
        if ($distance = $cache->fetch($cache_id)) {
            return $distance;
        }

        // Earth radius in [m]
        $earthRadius = 6371000;

        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
          pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        $distance = $angle * $earthRadius;

        // Store result in cache for 7 days
        $cache->save($cache_id, $distance, 604800);

        return $distance;
    }
}

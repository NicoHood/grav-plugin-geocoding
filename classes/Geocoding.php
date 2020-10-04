<?php
namespace Grav\Plugin\Geocoding;

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
            $request = str_replace('{QUERY}', rawurlencode($query), $request);
            $request = str_replace('{COUNTRY}', rawurlencode($this->country_code), $request);
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

    /**
    * Calculates the great-circle distance between two points, with
    * the Vincenty formula.
    * https://stackoverflow.com/questions/10053358
    * @param float $latitudeFrom Latitude of start point in [deg decimal]
    * @param float $longitudeFrom Longitude of start point in [deg decimal]
    * @param float $latitudeTo Latitude of target point in [deg decimal]
    * @param float $longitudeTo Longitude of target point in [deg decimal]
    * @param float $earthRadius Mean earth radius in [m]
    * @return float Distance between points in [m] (same as earthRadius)
    */
    public static function getDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
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
        return $angle * $earthRadius;
    }
}

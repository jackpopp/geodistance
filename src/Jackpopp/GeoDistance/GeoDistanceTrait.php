<?php namespace Jackpopp\GeoDistance;

use DB;
use Jackpopp\GeoDistance\InvalidMeasurementException;

trait GeoDistanceTrait {

    protected $latColumn = 'lat';

    protected $lngColumn = 'lng';

    protected $distance = 10;

    private static $MEASUREMENTS = [
        'miles' => 3959,
        'm' => 3959,
        'kilometers' => 6371,
        'km' => 6371,
        'meters' => 6371000,
        'feet' => 20902231,
        'nautical_miles' => 3440.06479
    ];

    public function getLatColumn()
    {
        return $this->latColumn;
    }

    public function getLngColumn()
    {
        return $this->lngColumn;
    }

    public function lat($lat = null)
    {
        if ($lat)
        {
            $this->lat = $lat;
            return $this;
        }

        return $this->lat;
    }

    public function lng($lng = null)
    {
        if ($lng)
        {
            $this->lng = $lng;
            return $this;
        }

        return $this->lng;
    }

    public function resolveEarthMeanRadius($measurement = null)
    {
        $measurement = ($measurement === null) ? key(static::$MEASUREMENTS) : strtolower($measurement);

        if (array_key_exists($measurement, static::$MEASUREMENTS))
            return static::$MEASUREMENTS[$measurement];

        throw new InvalidMeasurementException('Invalid measurement');
    }

    /**
    * @param Query
    * @param integer
    * @param mixed
    * @param mixed
    *
    * @todo Use pdo paramater bindings, instead of direct variables in query
    * @return Query
    *
    * Implements a distance radius search using Haversine formula.
    * Returns a query scope.
    * credit - https://developers.google.com/maps/articles/phpsqlsearch_v3
    **/

    public function scopeWithin($q, $distance, $measurement = null, $lat = null, $lng = null)
    {
        $pdo = DB::connection()->getPdo();

        $latColumn = "{$this->getTable()}.{$this->getLatColumn()}";
        $lngColumn = "{$this->getTable()}.{$this->getLngColumn()}";

        $lat = ($lat === null) ? $this->lat() : $lat;
        $lng = ($lng === null) ? $this->lng() : $lng;

        $meanRadius = $this->resolveEarthMeanRadius($measurement);
        $distance = intval($distance);

        // first-cut bounding box (in degrees)
        $maxLat = floatval($lat) + rad2deg($distance/$meanRadius);
        $minLat = floatval($lat) - rad2deg($distance/$meanRadius);
        // compensate for degrees longitude getting smaller with increasing latitude
        $maxLng = floatval($lng) + rad2deg($distance/$meanRadius/cos(deg2rad(floatval($lat))));
        $minLng = floatval($lng) - rad2deg($distance/$meanRadius/cos(deg2rad(floatval($lat))));

        $lat = $pdo->quote(floatval($lat));
        $lng = $pdo->quote(floatval($lng));
        $distance = $pdo->quote($distance);
        $meanRadius = $pdo->quote(floatval($meanRadius));

        return $q->select(DB::raw("*, ( $meanRadius * acos( cos( radians($lat) ) * cos( radians( $latColumn ) ) * cos( radians( $lngColumn ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( $latColumn ) ) ) ) AS distance"))
            ->from(DB::raw(
                "(
                    Select *
                    From locations
                    Where lat Between $minLat And $maxLat
                    And lng Between $minLng And $maxLng
                ) As locations"
            ))
            ->where(DB::raw("acos(sin($lat)*sin(radians(lat)) + cos($lng)*cos(radians(lat))*cos(radians(lng)-$lng)) * $distance < $lat"))
            ->orderby('distance', 'ASC');
    }

}
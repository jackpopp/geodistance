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

    private static $ADAPTER_CLASS = 'QueryAdapter';

    private static $ADAPTER_NAMESPACE = 'QueryAdapters';

    public function getLatColumn()
    {
        return "{$this->getTable()}.{$this->latColumn}";
    }

    public function getLngColumn()
    {
        return "{$this->getTable()}.{$this->lngColumn}";
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

    /**
    * @param string
    *
    * Grabs the earths mean radius in a specific measurment based on the key provided, throws an exception
    * if no mean readius measurement is found
    * 
    * @throws InvalidMeasurementException
    * @return float
    **/

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

        $latColumn = $this->getLatColumn();
        $lngColumn = $this->getLngColumn();

        $lat = ($lat === null) ? $this->lat() : $lat;
        $lng = ($lng === null) ? $this->lng() : $lng;

        $meanRadius = $this->resolveEarthMeanRadius($measurement);
        $this->distance = $distance;

        // first-cut bounding box (in degrees)
        $maxLat = floatval($lat) + rad2deg($this->distance/$meanRadius);
        $minLat = floatval($lat) - rad2deg($this->distance/$meanRadius);
        // compensate for degrees longitude getting smaller with increasing latitude
        $maxLng = floatval($lng) + rad2deg($this->distance/$meanRadius/cos(deg2rad(floatval($lat))));
        $minLng = floatval($lng) - rad2deg($this->distance/$meanRadius/cos(deg2rad(floatval($lat))));

        $lat = $pdo->quote(floatval($lat));
        $lng = $pdo->quote(floatval($lng));
        $meanRadius = $pdo->quote(floatval($meanRadius));

        // Paramater bindings havent been used as it would need to be within a DB::select which would run straight away and return its result, which we dont want as it will break the query builder.
        // This method should work okay as our values have been cooerced into correct types and quoted with pdo.
        $adapter = $this->resolveQueryAdapter(DB::connection()->getDriverName());
        return $adapter->within($q, $meanRadius, $lat, $lng, $minLat, $minLng, $maxLat, $maxLng);

        /*return $q->select(DB::raw("*, ( $meanRadius * acos( cos( radians($lat) ) * cos( radians( $latColumn ) ) * cos( radians( $lngColumn ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( $latColumn ) ) ) ) AS distance"))
            ->from(DB::raw(
                "(
                    Select *
                    From {$this->getTable()}
                    Where $latColumn Between $minLat And $maxLat
                    And $lngColumn Between $minLng And $maxLng
                ) As {$this->getTable()}"
            ))
            ->having('distance', '<=', $this->distance)
            ->orderby('distance', 'ASC');*/
    }

    public function scopeOutside($q, $distance, $measurement = null, $lat = null, $lng = null)
    {
        $pdo = DB::connection()->getPdo();

        $latColumn = $this->getLatColumn();
        $lngColumn = $this->getLngColumn();

        $lat = ($lat === null) ? $this->lat() : $lat;
        $lng = ($lng === null) ? $this->lng() : $lng;

        $meanRadius = $this->resolveEarthMeanRadius($measurement);
        $distance = floatval($distance);

        $lat = $pdo->quote(floatval($lat));
        $lng = $pdo->quote(floatval($lng));
        $meanRadius = $pdo->quote(floatval($meanRadius));

        $adapter = $this->resolveQueryAdapter(DB::connection()->getDriverName());
        return $adapter->within($q, $meanRadius, $lat, $lng);
    }

    public function resolveQueryAdapter($connectionType)
    {
        $class = $this->buildFullyQualifiedClassString($connectionType);

        if ( ! class_exists($class))
        {
            $class =  $this->buildFullyQualifiedClassString();
        }

        return new $class($this, $this->getTable(), $this->getLatColumn(), $this->getLngColumn(), $this->distance);
    }

    private function buildFullyQualifiedClassString($connectionType = '')
    {
        return __NAMESPACE__.'\\'.self::$ADAPTER_NAMESPACE."\\{$connectionType}".self::$ADAPTER_CLASS;
    }

}

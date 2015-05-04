<?php namespace Jackpopp\GeoDistance;

use DB;
use Jackpopp\GeoDistance\InvalidMesurementException;

trait GeoDistanceTrait {

    protected $latColumn = 'lat';

    protected $lngColumn = 'lng';

    protected $distance = 10;

    private static $MESUREMENTS = [
        'miles' => 3959, 
        'm' => 3959, 
        'kilometers' => 6371, 
        'km' => 6371
    ];

    protected $yards = 3959;

    public function getLatColumn()
    {
        return $this->latColumn;
    }

    public function getLngColumn()
    {
        return $this->lngColumn;
    }

    public function getYards()
    {
        return $this->yards;
    }

    public function setYards($yards)
    {
        $this->yards = $yards;
        return $this;
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

    public function resolveYards($measurement = null)
    {
        $measurement = ($measurement === null) ? key(static::$MESUREMENTS) : $measurement; 

        if (array_key_exists($measurement, static::$MESUREMENTS))
            return static::$MESUREMENTS[$measurement];

        throw new InvalidMesurementException('Invalid measurement');
    }

    /**
    * @param Query 
    * @param integer
    * @param mixed
    * @param mixed
    *
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
 
        $lat = $pdo->quote(floatval($lat));
        $lng = $pdo->quote(floatval($lng));
        $distance = intval($distance);

        $yards = $this->resolveYards($measurement);

        return $q->select(DB::raw("*, ( $yards * acos( cos( radians($lat) ) * cos( radians( $latColumn ) ) * cos( radians( $lngColumn ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( $latColumn ) ) ) ) AS distance"))
            ->having('distance', '<', $distance)
            ->orderby('distance', 'ASC');
    }       

}
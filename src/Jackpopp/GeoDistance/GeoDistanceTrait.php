<?php namespace Jackpopp\GeoDistance;

use Illuminate\Database\Capsule\Manager as Capsule;

trait GeoDistanceTrait {

    protected $latColumn = 'lat';

    protected $lngColumn = 'lng';

    protected $distance = 10;

    private static $MILES = 3959;

    private $KM = 6371;

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

    public function scopeWithin($q, $distance, $measurement, $lat = null, $lng = null)
    {
        $pdo = Capsule::connection()->getPdo();

        $latColumn = "{$this->getTable()}.{$this->getLatColumn()}";
        $lngColumn = "{$this->getTable()}.{$this->getLngColumn()}";

        $lat = $pdo->quote(floatval($lat));
        $lng = $pdo->quote(floatval($lng));
        $distance = intval($distance);

        return $q->select(Capsule::raw("*, ( {$this->getYards()} * acos( cos( radians($lat) ) * cos( radians( $latColumn ) ) * cos( radians( $lngColumn ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( $latColumn ) ) ) ) AS distance"))
            ->having('distance', '<', $distance)
            ->orderby('distance', 'ASC');
    }       

}
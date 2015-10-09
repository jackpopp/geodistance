<?php namespace Jackpopp\GeoDistance\QueryAdapters;

use DB;

/**
* 
*/
class QueryAdapter extends AbstractQueryAdapter
{

	public function within($query, $meanRadius, $lat, $lng, $minLat, $minLng, $maxLat, $maxLng)
	{
		return $query->select(DB::raw("*, ( $meanRadius * acos( cos( radians($lat) ) * cos( radians( {$this->getLatColumn()} ) ) * cos( radians( {$this->getLngColumn()} ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( {$this->getLatColumn()} ) ) ) ) AS distance"))
            ->from(DB::raw(
                "(
                    Select *
                    From {$this->getTable()}
                    Where {$this->getLatColumn()} Between $minLat And $maxLat
                    And {$this->getLngColumn()} Between $minLng And $maxLng
                ) As {$this->getTable()}"
            ))
            ->having('distance', '<=', $this->getDistance())
            ->orderby('distance', 'ASC');
	}

	public function outside($query, $meanRadius, $lat, $lng, $minLat, $minLng, $maxLat, $maxLng)
	{
		return $query->select(DB::raw("*, ( $meanRadius * acos( cos( radians($lat) ) * cos( radians( {$this->getLatColumn()} ) ) * cos( radians( {$this->getLngColumn()} ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( {$this->getLatColumn()} ) ) ) ) AS distance"))
        ->having('distance', '>=', $this->getDistance())
        ->orderby('distance', 'ASC');
	}

}
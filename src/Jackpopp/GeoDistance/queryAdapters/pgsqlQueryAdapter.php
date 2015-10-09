<?php namespace Jackpopp\GeoDistance\QueryAdapters;

use DB;

/**
* 
*/
class pgsqlQueryAdapter extends AbstractQueryAdapter
{
	function within($query, $meanRadius, $lat, $lng, $minLat = null, $minLng = null, $maxLat = null, $maxLng = null)
	{
		return $query->select(DB::raw("*, ( $meanRadius * acos( cos( radians($lat) ) * cos( radians( {$this->getLatColumn()} ) ) * cos( radians( {$this->getLngColumn()} ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( {$this->getLatColumn()} ) ) ) ) AS distance"))
        ->having(DB::raw("$meanRadius * acos( cos( radians($lat) ) * cos( radians( {$this->getLatColumn()} ) ) * cos( radians( {$this->getLngColumn()} ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( {$this->getLatColumn()} ) ) )"), '<=', $this->distance)
        ->groupBy("{$this->getTable()}.{$this->getModel()->getKeyName()}")
        ->orderby('distance', 'ASC');
	}

	function outside($query, $meanRadius, $lat, $lng, $minLat = null, $minLng = null, $maxLat = null, $maxLng = null)
	{
		return $query->select(DB::raw("*, ( $meanRadius * acos( cos( radians($lat) ) * cos( radians( {$this->getLatColumn()} ) ) * cos( radians( {$this->getLngColumn()} ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( {$this->getLatColumn()} ) ) ) ) AS distance"))
            ->having(DB::raw("$meanRadius * acos( cos( radians($lat) ) * cos( radians( {$this->getLatColumn()} ) ) * cos( radians( {$this->getLngColumn()} ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( {$this->getLatColumn()} ) ) )"), '=>', $this->distance)
            ->groupBy("{$this->getTable()}.{$this->getModel()->getKeyName()}")
            ->orderby('distance', 'ASC');
	}
}
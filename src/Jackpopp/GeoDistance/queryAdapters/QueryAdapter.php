<?php namespace Jackpopp\GeoDistance\QueryAdapters;

/**
* 
*/
class QueryAdapter extends AbstractQueryAdapter
{

	public function within(Builder $query)
	{
		return $query->select(DB::raw("*, ( $meanRadius * acos( cos( radians($lat) ) * cos( radians( $latColumn ) ) * cos( radians( $lngColumn ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( $latColumn ) ) ) ) AS distance"))
            ->from(DB::raw(
                "(
                    Select *
                    From {$this->getTable()}
                    Where $latColumn Between $minLat And $maxLat
                    And $lngColumn Between $minLng And $maxLng
                ) As {$this->getTable()}"
            ))
            ->having('distance', '<=', $distance)
            ->orderby('distance', 'ASC');
	}

	public function outside(Builder $query)
	{
		return $query->select(DB::raw("*, ( $meanRadius * acos( cos( radians($lat) ) * cos( radians( $latColumn ) ) * cos( radians( $lngColumn ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( $latColumn ) ) ) ) AS distance"))
        ->having('distance', '>=', $distance)
        ->orderby('distance', 'ASC');
	}

}
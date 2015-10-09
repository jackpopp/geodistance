<?php namespace Jackpopp\GeoDistance\QueryAdapters;

/**
* 
*/
abstract class AbstractQueryAdapter
{

	protected $table;

	protected $latColumn;

	protected $lngColumn;

	protected $distance;

	public function __construct($table, $latColumn, $lngColumn, $distance)
	{
		$this->table = $table;
		$this->latColumn = $latColumn;
		$this->lngColumn = $lngColumn;
		$this->distance = $distance;
	}

	public function getDistance($distance)
	{
		return floatval($this->$distance);
	}

	 // mean radius, lat, lng, max lat, max lng
	abstract public function within(Builder $query);

	// query, radius
	abstract public function outside(Builder $query);
}
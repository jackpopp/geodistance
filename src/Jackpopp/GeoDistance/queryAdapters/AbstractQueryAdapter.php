<?php namespace Jackpopp\GeoDistance\QueryAdapters;

/**
* 
*/
abstract class AbstractQueryAdapter
{
	protected $model;

	protected $table;

	protected $latColumn;

	protected $lngColumn;

	protected $distance;

	public function __construct($model, $table, $latColumn, $lngColumn, $distance)
	{
		$this->model = $model;
		$this->table = $table;
		$this->latColumn = $latColumn;
		$this->lngColumn = $lngColumn;
		$this->distance = $distance;
	}

	public function getModel()
	{
		return $this->model;
	}

	public function getDistance()
	{
		return floatval($this->distance);
	}

	public function getTable()
	{
		return $this->table;
	}

	public function getLatColumn()
	{
		return $this->latColumn;
	}

	public function getLngColumn()
	{
		return $this->lngColumn;
	}

	 // mean radius, lat, lng, max lat, max lng
	abstract public function within($query, $meanRadius, $lat, $lng, $minLat, $minLng, $maxLat, $maxLng);

	// query, radius
	abstract public function outside($query, $meanRadius, $lat, $lng);
}
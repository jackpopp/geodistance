# GeoDistance
GeoDistance allows you to search for locations within a radius using latitude and longitude values with your eloquent models.

###Setup

Add geodistance to your composer file.
```
"jackpopp/geodistance": "dev-master"
```

Add the geodistance trait to your eloquent model and lat/lng columns to your table.

```php
<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jackpopp\GeoDistance\GeoDistanceTrait;

class Location extends Model {

    use GeoDistanceTrait;

    protected $fillable = ['name', 'lat', 'lng'];
    
}
```

You can now search for locations with in a distance, using miles or kilometers

```php

$lat = 51.4833;
$lng = 3.1833;

$locations = Location::within(5, 'miles', $lat, $lng)->get();

$locations = Location::within(5, 'kilometers', $lat, $lng)->get();

// or 

$location = new Location();
$locations = $location->lat($lat)->lng($lng)->within(5, 'miles')->get();

```
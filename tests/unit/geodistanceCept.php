<?php 

require __DIR__.'/../../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Jackpopp\GeoDistance\GeoDistanceTrait;

// set up eloquent for testing purposes
$capsule = new Capsule;

$capsule->addConnection(array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'geodistance',
    'username'  => 'homestead',
    'password'  => 'secret',
    'charset'   => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix'    => ''
));

$capsule->setAsGlobal();
$capsule->bootEloquent();

// add a helper DB class to simulate the DB facade in laravel
class DB {

    static function connection()
    {
        return Capsule::connection();
    }

    static function raw($string)
    {
        return Capsule::raw($string);
    }

    static function select($query, $bindings = array(), $useReadPdo = true)
    {
        return static::connection()->select($query, $bindings = array(), $useReadPdo = true);
    }

}

class Location extends Model {

    use GeoDistanceTrait;

    protected $fillable = ['name', 'lat', 'lng'];

    public $timestamps = false;
    
}

Capsule::table('locations')->truncate();

Location::create([
    'name' => 'Cardiff', 
    'lat' => 51.4833, 
    'lng' => 3.1833
]);

Location::create([
    'name' => 'Newport', 
    'lat' => 51.5833, 
    'lng' => 3.0000
]);

Location::create([
    'name' => 'Swansea', 
    'lat' => 51.6167, 
    'lng' => 3.9500
]);

Location::create([
    'name' => 'London', 
    'lat' => 51.5072, 
    'lng' => 0.1275
]);

/*for ($i = 0; $i < 1000; $i++)
{
    Location::create(['location' => $i, 'lat' => $i, 'lng' => $i, 'updated_at' => $i, 'created_at' => $i]);
}*/

$I = new UnitTester($scenario);

$lat = 51.4833;
$lng = 3.1833;

$location = new Location();
$locations = $location->lat($lat)->lng($lng)->within(20, 'miles')->get();

$I->wantTo('find locations within 5 miles');
$locations = Location::within(5, 'miles', $lat, $lng)->get();
$I->assertEquals(1, $locations->count(), 'One location found within 5 miles');

$I->wantTo('find 2 locations within 132000 feet (25 miles)');
$locations = Location::within(132000, 'feet', $lat, $lng)->get();
$I->assertEquals(2, $locations->count(), 'One location found within 132000 feet');

$I->wantTo('find locations within 55 miles');
$locations = Location::within(55, 'miles', $lat, $lng)->get();
$I->assertEquals(3, $locations->count(), 'Three locations found within 55 miles');

$I->wantTo('find locations within 5 kilometers');
$locations = Location::within(5, 'kilometers', $lat, $lng)->get();
$I->assertEquals(1, $locations->count(), 'One location found within 5 kilometers');

$I->wantTo('find locations within 5 nautical miles');
$locations = Location::within(5, 'nautical_miles', $lat, $lng)->get();
$I->assertEquals(1, $locations->count(), 'One location found within 5 nautical miles');

$I->wantTo('find locations within 5000 meters');
$locations = Location::within(5000, 'meters', $lat, $lng)->get();
$I->assertEquals(1, $locations->count(), 'One location found within 5000 meters');

$I->wantTo('default to first mesurement if no paramater is passed');
$location = new Location();
$location = $location->lat($lat)->lng($lng)->within(20)->get();
$I->assertEquals(1, $locations->count(), 'One location found within 20 miles');

$I->wantTo('find  3 locations not within 1 mile');
$locations = Location::outside(1, 'miles', $lat, $lng)->get();
$I->assertEquals(3, $locations->count(), 'Three location found not within 1 miles');

$I->wantTo('find 1 locations not within 100 miles');
$locations = Location::outside(100, 'miles', $lat, $lng)->get();
$I->assertEquals(1, $locations->count(), 'Three location found not within 100 miles');

$I->wantTo('find 0 locations not within 200 miles');
$locations = Location::outside(200, 'miles', $lat, $lng)->get();
$I->assertEquals(0, $locations->count(), 'Three location found not within 200 miles');


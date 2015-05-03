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

class Location extends Model {

    use GeoDistanceTrait;

    protected $fillable = ['name', 'lat', 'lng'];
    
}

$I = new UnitTester($scenario);

$lat = 51.4833;
$lng = 3.1833;

$location = new Location();
$locations = $location->lat($lat)->lng($lng)->within(20, 'miles')->get();

$I->wantTo('find locations within 5 miles');
$locations = Location::within(5, 'miles', $lat, $lng)->get();
$I->assertEquals(1, $locations->count(), 'One location found within 1 miles');

$I->wantTo('find locations within 55 miles');
$locations = Location::within(55, 'miles', $lat, $lng)->get();
$I->assertEquals(3, $locations->count(), 'Three locations found within 55 miles');

$I->wantTo('find locations within 5 kilometers');
$locations = Location::within(5, 'kilometers', $lat, $lng)->get();
$I->assertEquals(1, $locations->count(), 'One location found within 5 kilometers');

$I->wantTo('default to first mesurement if no paramater is passed');
$location = new Location();
$location = $location->lat($lat)->lng($lng)->within(20)->get();
$I->assertEquals(1, $locations->count(), 'One location found within 20 miles');
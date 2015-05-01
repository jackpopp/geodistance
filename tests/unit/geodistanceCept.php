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
$I->wantTo('find locations within 20 miles');

$lat = 51.4815;
$lng = -3.1790;

$location = new Location();
$location = $location->lat($lat)->lng($lng)->within(20, 'miles')->get();

//$locations = Location::lat(51.4815)->lng(-3.1790)->within(20, 'miles')->get();

$locations = Location::within(20, 'miles', $lat, $lng)->get();
$I->assertEquals(1, $locations->count(), 'One location found within 20 miles');
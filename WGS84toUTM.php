<?php

/**
 * Converts a latitude/longitude pair to x-y coordinates in Universal Transverse Mercator projection
 * 
 * The formulas used in converting latitude-longitude values to UTM coordinates, 
 * are truncated versions of Transverse Mercator flattening series. 
 * They are accurate to around a millimeter within 3,000 km of the central meridian.
 *
 * Source: http://en.wikipedia.org/wiki/Universal_Transverse_Mercator_coordinate_system
 * Source: Steven Dutch's very helpful explanations on the UTM system: http://www.uwgb.edu/dutchs/UsefulData/UTMFormulas.HTM
 *
 * This implementation assumes the WGS84 ellipsoid for the Earth.
 *
 * @author Isil Demir
 */

/**
 * Equatorial radius of the Earth (Semi-Major axis of the ellipsoid)
 * @var float 
 */
$a = 6378137.0;

/**
 * Flattening of Earth
 * @var float
 */
$f = 1/298.257223563;

/**
 * Radius at the poles (Length of semi-minor axis of the ellipsoid)
 * @var float
 */
$b = (1 - $f) * $a;

/**
 * Scale
 * @var float
 */
$k_0 = 0.9996;

/**
 * Eccentricity - sqrt((1 - (b/a)*(b/a)))
 * @var float
 */
$e = 0.0818192;

/**
 * False easting (500km)
 * @var float
 */
$E_0 = 500000.0;

/**
 * False northing in southern hemisphere (10000km)
 * @var float
 */
$N_0 = 10000000.0;

/**
 * UTM Coordinate object
 */
class UTMCoordinate {
	public $x;
	public $y;
	public $zone;
	public $hemisphere;

	public function __construct($x = null, $y = null, $zone = null, $hemisphere = "") {
		$this->x = $x;
		$this->y = $y;
		$this->zone = $zone;
		$this->hemisphere = $hemisphere;
	}
}

/**
 * Converts a latitude/longitude pair to x-y coordinates in Universal Transverse Mercator projection
 *
 * @param float $lat Latitude (decimal degrees)
 * @param float $lng Longitude (decimal degrees)
 *
 * @return UTMCoordinate|string UTM easting, northing, zone and hemisphere if successful; error otherwise 
 */
function WGS84toUTM ($lat, $lng) {

	global $a, $f, $b, $k_0, $e, $E_0, $N_0;

	// Input validation
	if (!is_numeric($lat) || !is_numeric($lng)) return "Input values must be numeric";

	if ($lat < -90 || $lat > 90) return "Latitude must be between -90, 90";

	if ($lng < -180 || $lng > 180) return "Longitude must be between -180, 180";


	$phi = deg2rad($lat); // Latitude in radians

	$zone = 1 + floor(($lng+180)/6); // UTM zone
	$zone_central = 3 + 6*($zone - 1) - 180; // Central meridian for this zone


	// Calculate some intermediate values
	$esq = (1 - ($b/$a)*($b/$a)); // e squared 
	$e0sq = $e*$e / (1 - $e*$e); // e prime squared -> e' = e / sqrt(1 - e*e)
	$N = $a / sqrt(1 - pow($e*sin($phi), 2));
	$T = pow(tan($phi), 2);
	$C = $e0sq * pow(cos($phi), 2);
	$A = deg2rad(($lng - $zone_central)) * cos($phi);


	// Calculate M, arc length along the standard meridian
	$M = $phi * (1 - $esq * (1/4 + $esq * (3/64 + 5 * $esq/256)));
	$M = $M - sin(2 * $phi) * ($esq * (3/8 + $esq * (3/32 + 45 * $esq/1024)));
	$M = $M + sin(4 * $phi) * ($esq * $esq *(15/256 + $esq * 45/1024));
	$M = $M - sin(6 * $phi) * ($esq * $esq * $esq * (35/3072));
	$M = $M * $a;


	// Calculate UTM coordinates
	// Easting
	$x = $k_0 * $N * $A * (1 + $A*$A*((1-$T+$C)/6 + $A*$A* (5 - 18*$T + $T*$T + 72*$C -58*$e0sq)/120));
	$x += $E_0;

	// Northing
	$y = $k_0 * ($M + $N * tan($phi) * ($A*$A*(1/2 + $A*$A*((5 - $T + 9*$C + 4*$C*$C)/24 + $A*$A*(61 - 58*$T + $T*$T + 600*$C - 330*$e0sq)/720))));
	if ($y < 0) $y += $N_0;

	$hemisphere = ($phi < 0) ? 'S' : 'N';

	return new UTMCoordinate($x, $y, $zone, $hemisphere);

}

?>

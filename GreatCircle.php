<?php

/**
 * The great-circle or orthodromic distance is the shortest distance between any two points on the surface of a sphere.
 *
 * Source: http://en.wikipedia.org/wiki/Great-circle_distance
 *
 * This implementation uses the special case (a sphere, which is an ellipsoid with equal major and minor axes) 
 * of the Vincenty formula (which more generally is a method to compute distances on ellipsoids)
 *
 * This implementation assumes the WGS84 ellipsoid for the Earth.
 *
 * @author Isil Demir
 */

/**
 * Equatorial radius of the Earth 
 * @var float 
 */
$r = 6378137.0;

/**
 * Calculate the great-circle distance between two coordinates
 *
 * @param float $lat1 Latitude of the first coordinate (decimal degrees)
 * @param float $lng1 Longitude of the first coordinate (decimal degrees)
 * @param float $lat2 Latitude of the second coordinate (decimal degrees)
 * @param float $lng2 Longitude of the second coordinate (decimal degrees)
 *
 * @return int|string Returns the distance (meters) if successful, or error message
 */
function GreatCircleDistance($lat1, $lng1, $lat2, $lng2) {

	global $r;

	// Input validation
	if (!is_numeric($lat1) || !is_numeric($lng1) || !is_numeric($lat2) || !is_numeric($lng2)) return "Input values must be numeric";

	if ($lat1 < -90 || $lat2 < -90 || $lat1 > 90 || $lat2 > 90) return "Latitude must be between -90, 90";

	if ($lng1 < -180 || $lng2 < -180 || $lng1 > 180 || $lng2 > 180) return "Longitude must be between -180, 180";

	$phi1 = deg2rad($lat1);
	$phi2 = deg2rad($lat2);
	
	$delta_lambda = deg2rad($lng1-$lng2);

	// Sigma is the central angle between the two points
	$sigma = atan2(sqrt(pow(cos($phi2)*sin($delta_lambda), 2) + pow(cos($phi1)*sin($phi2) - sin($phi1)*cos($phi2)*cos($delta_lambda), 2)), sin($phi1)*sin($phi2) + cos($phi1)*cos($phi2)*cos($delta_lambda));

	return $r * $sigma;

}


?>
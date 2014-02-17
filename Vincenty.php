<?php

/**
 * Vincenty's formulae are two related iterative methods used in geodesy to calculate the distance between two points on the surface of a spheroid, 
 * developed by Thaddeus Vincenty (1975) They are based on the assumption that the figure of the Earth is an oblate spheroid, and hence are more 
 * accurate than methods such as great-circle distance which assume a spherical Earth.
 *
 * Source: http://en.wikipedia.org/wiki/Vincenty's_formulae
 *
 * This is an implementation of the inverse problem: 
 * Given the coordinates of the two points (φ1, L1) and (φ2, L2), the inverse problem finds the azimuths α1, α2 and the ellipsoidal distance s. 
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
 * Iteration limit, in case the equation does not converge. 
 * An example of a failure of the inverse method to converge is (φ1, L1) = (0°, 0°) and (φ2, L2) = (0.5°, 179.7°) for the WGS84 ellipsoid.
 * @var int
 */
$iteration_limit = 100;

/**
 * The desired degree of accuracy for λ to converge. For some "slow convergence" cases, (φ1, L1) = (0°, 0°) and (φ2, L2) = (0.5°, 179.5°) for the WGS84 ellipsoid, 
 * if the accuracy is lessened, the equation may converge with more iterations.
 * @var float
 */
$accuracy = 1e-12;

/**
 * Calculate the distance between two coordinates using Vincenty's formulae
 *
 * Given the coordinates of the two points (φ1, L1) and (φ2, L2), the inverse problem finds the azimuths α1, α2 and the ellipsoidal distance s.
 *
 * @param float $lat1 Latitude of the first coordinate (decimal degrees)
 * @param float $lng1 Longitude of the first coordinate (decimal degrees)
 * @param float $lat2 Latitude of the second coordinate (decimal degrees)
 * @param float $lng2 Longitude of the second coordinate (decimal degrees)
 * @param float &$s Distance between two coordinates (meters)
 * @param float &$azimuth12 Forward azimuth at the point
 * @param float &$azimuth21 Reverse azimuth at the point
 *
 * @return void|int|string Returns null if successful, 0 if the first and second points are the same, or error, if the equation does not converge
 */
function GeodesicDistance($lat1, $lng1, $lat2, $lng2, &$s, &$azimuth12, &$azimuth21) {

	global $a, $f, $b, $iteration_limit, $accuracy;

	// Input validation
	if (!is_numeric($lat1) || !is_numeric($lng1) || !is_numeric($lat2) || !is_numeric($lng2)) return "Input values must be numeric";

	if ($lat1 < -90 || $lat2 < -90 || $lat1 > 90 || $lat2 > 90) return "Latitude must be between -90, 90";

	if ($lng1 < -180 || $lng2 < -180 || $lng1 > 180 || $lng2 > 180) return "Longitude must be between -180, 180";

	// Calculate U1, U2 and L
	$L = deg2rad($lng2-$lng1);
	$U1 = atan((1 - $f) * tan(deg2rad($lat1)));
	$U2 = atan((1 - $f) * tan(deg2rad($lat2)));
	
	// Set initial value of λ = L. Then iteratively evaluate the following equations until λ converges
	$lambda = $L;
	$lambda_tmp;
	$limit = 0;

	do {

		$sin_sigma = sqrt(pow(cos($U2)*sin($lambda), 2) + pow(cos($U1)*sin($U2) - sin($U1)*cos($U2)*cos($lambda), 2));
		if ($sin_sigma == 0) return 0; // same points

		$cos_sigma = sin($U1)*sin($U2) + cos($U1)*cos($U2)*cos($lambda);
		$sigma = atan2($sin_sigma, $cos_sigma); // sigma is the arc length between points on the auxiliary sphere

		$sin_alpha = (cos($U1)*cos($U2)*sin($lambda)) / $sin_sigma;
		$cos_sq_alpha = 1 - pow($sin_alpha, 2);

		$cos_2sigmaM = $cos_sigma - 2*sin($U1)*sin($U2) / $cos_sq_alpha;
		if(is_nan($cos_2sigmaM)) $cos_2sigmaM = 0; // point on the equatorial line, cos_sq_alpha = 0

		$C = $f/16 * $cos_sq_alpha * (4 + $f*(4 - 3*$cos_sq_alpha));

		$lambda_tmp = $lambda;

		$lambda = $L + (1-$C)*$f*$sin_alpha*($sigma + $C*$sin_sigma*($cos_2sigmaM + $C*$cos_sigma*(-1 + 2*pow($cos_2sigmaM, 2))));

	} while (++$limit < $iteration_limit && abs($lambda - $lambda_tmp) > $accuracy);

	if ($limit == $iteration_limit) return "Equation did not converge";
	
	// When λ has converged to the desired degree of accuracy (10^−12 corresponds to approximately 0.06mm), evaluate the following:
	$u_sq = $cos_sq_alpha * (pow($a, 2) - pow($b, 2)) / pow($b, 2);
	$A = 1 + $u_sq/16384 * (4096 + $u_sq * (-768 + $u_sq * (320 - 175*$u_sq)));
	$B = $u_sq/1024 * (256 + $u_sq * (-128 + $u_sq * (74 - 47*$u_sq)));
	$delta_sigma = $B*$sin_sigma * ($cos_2sigmaM + $B/4 * ($cos_sigma*(-1+2*pow($cos_2sigmaM, 2)) - $B/6 * $cos_2sigmaM*(-3+4*pow($sin_sigma, 2))*(-3+4*pow($cos_2sigmaM, 2))));

	// Distance
	$s = $b * $A * ($sigma - $delta_sigma);
	
	// Azimuths
	$azimuth12 = atan2(cos($U2)*sin($lambda), cos($U1)*sin($U2) - sin($U1)*cos($U2)*cos($lambda));
	$azimuth21 = atan2(cos($U1)*sin($lambda), -sin($U1)*cos($U2) + cos($U1)*sin($U2)*cos($lambda));

}


?>
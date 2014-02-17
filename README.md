Geodesy
=======

This repo contains some algorithms used in Geodesy. Vincenty calculates the distance between two coordinates on a spheroid. WGS84toUTM converts WGS84 coordinates to UTM Mercator. GreatCircle calculates the great circle (orthodromic) distance between two coordinates on a sphere.

Vincenty.php
------------

Vincenty's formulae are two related iterative methods used in geodesy to calculate the distance between two points on the surface of a spheroid, developed by Thaddeus Vincenty (1975) They are based on the assumption that the figure of the Earth is an oblate spheroid, and hence are more accurate than methods such as great-circle distance which assume a spherical Earth.
Source: http://en.wikipedia.org/wiki/Vincenty's_formulae

This is an implementation of the inverse problem: Given the coordinates of the two points (φ1, L1) and (φ2, L2), the inverse problem finds the azimuths α1, α2 and the ellipsoidal distance s.

This implementation assumes the WGS84 ellipsoid for the Earth.

WGS84toUTM.php
--------------

Converts a latitude/longitude pair to x-y coordinates in Universal Transverse Mercator projection.

The formulas used in converting latitude-longitude values to UTM coordinates are truncated versions of Transverse Mercator flattening series. They are accurate to around a millimeter within 3,000 km of the central meridian.
Source: http://en.wikipedia.org/wiki/Universal_Transverse_Mercator_coordinate_system 
Source: Steven Dutch's very helpful explanations on the UTM system: http://www.uwgb.edu/dutchs/UsefulData/UTMFormulas.HTM

This implementation assumes the WGS84 ellipsoid for the Earth.

GreatCircle.php
---------------

The great-circle or orthodromic distance is the shortest distance between any two points on the surface of a sphere.
Source: http://en.wikipedia.org/wiki/Great-circle_distance

This implementation uses the special case, a sphere, which is an ellipsoid with equal major and minor axes of the Vincenty formula, which, more generally, is a method to compute distances on ellipsoids.

This implementation assumes the WGS84 ellipsoid for the Earth.
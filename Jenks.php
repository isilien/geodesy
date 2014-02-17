<?php

/**
* The Jenks optimization method, also called the Jenks natural breaks classification method, is a data classification method designed to 
* determine the best arrangement of values into different classes. This is done by seeking to minimize each class’s average deviation from 
* the class mean, while maximizing each class’s deviation from the means of the other groups.
*
* Source: http://en.wikipedia.org/wiki/Jenks_natural_breaks_optimization
*
* @author Isil Demir
*/
class Jenks {

	private $_means = array();
	private $_assigned = array();

	/**
	 * Calculate Jenks Natural breaks.
	 *
	 * @param int[]|float[] $datalist The list of data to be processed
	 * @param int $numclass The number of required breaks. Default value is 7.
	 *
	 * @return int[]|float[] Array with break values
	 */
	public function JenksBreaks($datalist, $numclass = 7) {

		// The list must be sorted
		sort($datalist, SORT_NUMERIC);

		$numdata = count($datalist);
		$counts = array();

		foreach($datalist as $data) {
			if(!in_array($data, $counts)) {
				array_push ($counts, $data);
			}
		}     

		if(count($counts) < $numclass) {
			$numclass = count($counts);
		}

		$freq = floor($numdata/$numclass);

		$numbers = array();
		for($i = 1; $i <= $numdata; $i++) {
			$this->_assigned[$i] = -1;
			$numbers[$i] = $datalist[($i-1)];
		}

		$j = 1;
		for($i = floor($freq/2); $i <= $numdata; $i++) {
			if((($i + floor($freq/2)) % $freq) == 0) {
				$this->_means[$j] = $numbers[$i];
				$j++;
			}
		}

		$blnChanged = 1;
		while($blnChanged) {
			$blnChanged = $this->reassignAll($numbers, $numdata, $numclass);
			if($blnChanged) { 
				$this->calculateMeans($numbers, $numdata, $numclass);
			}
		}

		$j = 0;
		$breaks[$j] = $numbers[1];
		for($i = 1; $i <= $numdata; $i++) {
			if($i > 1 && $this->_assigned[$i] != $this->_assigned[($i-1)]) {
				$j++;
				$breaks[$j] = ($numbers[$i] + $numbers[$i-1])/2;
			}
		}
		$breaks[$j+1] = $numbers[$numdata];

		return $breaks;

	}

	/**
	 * Re-assign values
	 * @param float[] $numbers Array of numbers
	 * @param int $numdata Number of elements in array
	 * @param int $numclass Number of Jenks breaks
	 * @return int Returns 1 if re-assigned, 0 otherwise. 
	 */
	private function reassignAll($numbers, $numdata, $numclass) {

		$reassign = 0;

		for($i = 1; $i <= $numdata; $i++) {

			$mindist = 1e20;

			for($j = 1; $j <= $numclass; $j++) {
				if(abs(($numbers[$i]-$this->_means[$j])) < $mindist) {
					$mindist = abs($numbers[$i] - $this->_means[$j]);
					$minclust = $j; 
				}
			}
			if($minclust != $this->_assigned[$i]) {
				$reassign = 1;
			}
			$this->_assigned[$i] = $minclust;
		}
		return $reassign;
	}

	/**
	 * Calculate means 
	 * @param float[] $numbers Array of numbers
	 * @param int $numdata Number of elements in array
	 * @param int $numclass Number of Jenks breaks
	 * @return void
	 */
	private function calculateMeans($numbers, $numdata, $numclass) {

		$counts = array();

		for($i = 1; $i <= $numclass; $i++) {
			$this->_means[$i] = 0;
			$counts[$i] = 0;
		}

		for($i = 1; $i <= $numdata; $i++) {
			$this->_means[$this->_assigned[$i]] += $numbers[$i];
			$counts[$this->_assigned[$i]]++;
		}

		for($i = 1; $i <= $numclass; $i++) {
			if($counts[$i]) {
				$this->_means[$i] = $this->_means[$i]/$counts[$i];
			}
			else {
				$this->_means[$i] = 0;
			}
		}
	}

}

?>

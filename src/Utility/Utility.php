<?php

namespace CodeSniffer\Utility;

use Cake\Utility\Hash;

/**
 * Main class for all app-wide utility methods
 *
 * @author Mark Scherer
 * @license MIT
 */
class Utility {

	/**
	 * Expands the values of an array of strings into a deep array.
	 * Opposite of flattenList().
	 *
	 * It needs at least a single separator to be present in all values
	 * as the key would otherwise be undefined. If data can contain such key-less
	 * rows, use $undefinedKey to avoid an exception being thrown. But it will
	 * effectivly collide with other values in that same key then.
	 *
	 * So `Some.Deep.Value` becomes `array('Some' => array('Deep' => array('Value')))`.
	 *
	 * @param array $data
	 * @param string $separator
	 * @param string $undefinedKey
	 * @return array
	 */
	public static function expandList(array $data, $separator = '.', $undefinedKey = null) {
		$result = array();
		foreach ($data as $value) {
			$keys = explode($separator, $value);
			$value = array_pop($keys);

			$keys = array_reverse($keys);
			if (!isset($keys[0])) {
				if ($undefinedKey === null) {
					throw new RuntimeException('Key-less values are not supported without $undefinedKey.');
				}
				$keys[0] = $undefinedKey;
			}
			$child = array($keys[0] => array($value));
			array_shift($keys);
			foreach ($keys as $k) {
				$child = array(
					$k => $child
				);
			}
			$result = Hash::merge($result, $child);
		}
		return $result;
	}

	/**
	 * Flattens a deep array into an array of strings.
	 * Opposite of expandList().
	 *
	 * So `array('Some' => array('Deep' => array('Value')))` becomes `Some.Deep.Value`.
	 *
	 * Note that primarily only string should be used.
	 * However, boolean values are casted to int and thus
	 * both boolean and integer values also supported.
	 *
	 * @param array $data
	 * @return array
	 */
	public static function flattenList(array $data, $separator = '.') {
		$result = array();
		$stack = array();
		$path = null;

		reset($data);
		while (!empty($data)) {
			$key = key($data);
			$element = $data[$key];
			unset($data[$key]);

			if (is_array($element) && !empty($element)) {
				if (!empty($data)) {
					$stack[] = array($data, $path);
				}
				$data = $element;
				reset($data);
				$path .= $key . $separator;
			} else {
				if (is_bool($element)) {
					$element = (int)$element;
				}
				$result[] = $path . $element;
			}

			if (empty($data) && !empty($stack)) {
				list($data, $path) = array_pop($stack);
				reset($data);
			}
		}
		return $result;
	}

}

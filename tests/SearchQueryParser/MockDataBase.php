<?php
namespace PhpPlatform\Tests\SearchQueryParser;

use PhpPlatform\Persist\Connection\Connection;

class MockDataBase implements Connection{
		public function query($queryString) {
	}

	public function autocommit($mode) {
	}

	public function lastError() {
	}

	public function lastInsertedId() {
	}

	public function close() {
	}

	public function startTransaction() {
	}

	public function commitTransaction() {
	}

	public function abortTransaction() {
	}

	public function encodeForSQLInjection($value) {
		return $value;
	}

	public function formatDate($dateStr = null, $includeTime = null) {
	}

	public function formatTime($hh = 0, $mm = 0, $ss = 0, $ampm = "AM") {
	}

	public function formatBoolean($value) {
	}

	public function outputDateFormat() {
	}

	public function outputTimeFormat() {
	}

	public function outputDateTimeFormat() {
	}

	public function setTimeZone($timeZone) {
	}

}
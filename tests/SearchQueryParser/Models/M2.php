<?php
namespace PhpPlatform\Tests\SearchQueryParser\Models;

/**
 * @tableName M2
 * @prefix m2
 */
class M2 extends M1{
	
	/**
	 * @columnName ID
	 * @type bigint
	 * @primary
	 * @autoIncrement
	 * @get
	 */
	private $id = null;
	
	/**
	 * @columnName M1_ID
	 * @type bigint
	 * @reference
	 * @get
	 */
	private $m1Id = null;
	
	/**
	 * @columnName ADDRESS
	 * @type varchar
	 * @set
	 * @get
	 */
	private $address = null;
	
	
}
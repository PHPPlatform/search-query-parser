<?php
namespace PhpPlatform\Tests\SearchQueryParser\Models;

use PhpPlatform\Persist\Model;

/**
 * @tableName M3
 * @prefix m3
 */
class M3 extends Model{
	
	/**
     * @columnName ID
     * @type bigint
     * @primary
     * @autoIncrement
     * @get
     */
    private $id = null;

    /**
     * @columnName NAME
     * @type varchar
     * @set
     * @get
     */
    private $name = null;

    /**
     * @columnName PHONE
     * @type varchar
     * @set
     * @get
     */
    private $phone = null;
	
}
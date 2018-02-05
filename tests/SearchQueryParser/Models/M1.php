<?php
namespace PhpPlatform\Tests\SearchQueryParser\Models;

/**
 * @tableName M1
 * @prefix m1
 */
class M1 {
	
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
     * @columnName USER_NAME
     * @type varchar
     * @set
     * @get
     */
    private $userName = null;
    
    /**
     * @columnName PASSWORD
     * @type varchar
     * @set
     */
    private $password = null;
	
	
}
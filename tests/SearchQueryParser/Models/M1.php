<?php
namespace PhpPlatform\Tests\SearchQueryParser\Models;

use PhpPlatform\Persist\Model;

/**
 * @tableName M1
 * @prefix m1
 */
class M1 extends Model{
	
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
	
    /**
     * @columnName M3_ID
     * @type bigint
     * @set
     * @get
     */
    private $m3Id = null;
    
    /**
     * @columnName M3_ID
     * @type varchar
     * @get
     * @foreignField "PhpPlatform\\Tests\\SearchQueryParser\\Models\\M3->name"
     */
    private $m3Name = null;
    
    /**
     * @columnName M3_ID
     * @type varchar
     * @get
     * @foreignField "PhpPlatform\\Tests\\SearchQueryParser\\Models\\M3->phone"
     */
    private $m3Phone = null;
    
}
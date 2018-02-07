<?php

namespace PhpPlatform\SearchQueryParser;

use PhpPlatform\Persist\Expression;

/**
 * This is a class representing the arguments required by PhpPlatform\Persist\Model::find($filters,$sort,$pagination,$where)
 */
class FindParams {
	public $filters = array();
	public $sort = array();
	public $pagination = null;
	
	/**
	 * @var Expression
	 */
	public $where = null;
}
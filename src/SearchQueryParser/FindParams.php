<?php

namespace PhpPlatform\SearchQueryParser;

/**
 * This is a class representing the arguments required by PhpPlatform\Persist\Model::find($filters,$sort,$pagination,$where)
 */
class FindParams {
	public $filters = array();
	public $sort = array();
	public $pagination = array();
	public $where = null;
}
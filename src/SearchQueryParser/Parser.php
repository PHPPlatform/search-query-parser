<?php

namespace ICircle\Accounts\Services\Utils;

use PhpPlatform\RESTFul\HTTPRequest;
use PhpPlatform\Errors\Exceptions\Http\_4XX\BadRequest;
use PhpPlatform\Persist\Expression;
use PhpPlatform\Persist\Model;
use PhpPlatform\Persist\Field;
use PhpPlatform\Errors\Exceptions\Application\BadInputException;
use PhpPlatform\Persist\RelationalMappingUtil;
use PhpPlatform\SearchQueryParser\FindParams;
use PhpPlatform\Persist\Reflection;

class Parser {
	
	/**
	 * This method parses query parameters from rest endpoint to method arguments required by PhpPlatform\Persist\Model::find($filters,$sort,$pagination,$where)
	 * 
	 * @param HTTPRequest $request is the HTTPRequest object received in the rest service 
	 * @param string $modelClassName is the full name of the Model which is served as REST Resource 
	 * @param string[] $excludeFromFullTextSearch is the array of fields which needs to be excluded from performing full text search 
	 */
	static public function parse($request,$modelClassName,$excludeFromFullTextSearch){
		$findParams = new FindParams();
		try{
			// full text search query
			$findParams->where = self::parseFullTextSearch($request, $modelClassName, $excludeFromFullTextSearch);
			
			// filters
			
			
			// sort
			$sort = array();
			$sortParams = $request->getQueryParam('s');
			if($sortParams!= null){
				$sortParams= base64_decode($sortParams);
				$sortParams= json_decode($sortParams,true);
				if(!is_array($sortParams)){
					throw new BadInputException("query parameter s is invalid");
				}
				
				foreach ($sortParams as $field=>$value){
					if(in_array($field, $fields)){
						if($value != Model::SORTBY_ASC && $value != Model::SORTBY_DESC){
							throw new BadRequest("query parameter s is invalid");
						}
						$sort[$field] = $value;
					}else{
						throw new BadRequest("query parameter s is invalid");
					}
				}
			}
			
			// pagination
			$pagination = null;
			$paginationParam = $request->getQueryParam('p');
			if($paginationParam != null){
				$paginationParam = preg_split('/-/', $paginationParam);
				if(count($paginationParam) != 2 || 
						(!is_numeric($paginationParam[0]) || !is_int($paginationParam[0]+0)) || 
						(!is_numeric($paginationParam[0]) || !is_int($paginationParam[1]+0)) ){
					throw new BadRequest('query parameter p is invalid');
				}
				$pagination = array('pageNumber'=>$paginationParam[0],'pageSize'=>$paginationParam[1]);
			}
			
		}catch (\Exception $e){
			throw new BadRequest("Bad Search Params");
		}
		return ["filters" => $filters,"sort"=>$sort,"pagination"=>$pagination,"where"=>$whereExpression];
	}
	
	private static function parseFullTextSearch($request,$modelClassName,$excludeFromFullTextSearch){
		$whereExpression = null;
		$fullTextSearchQuery = $request->getQueryParam('q');
		if($fullTextSearchQuery != null){
			$fullTextSearchExpressions = array();
			$classList = RelationalMappingUtil::getClassConfiguration($modelClassName);
			foreach ($classList as $className => $class){
				foreach ($class['fields'] as $fieldName => $field){
					if(RelationalMappingUtil::_isGet($field) && !RelationalMappingUtil::_isAutoIncrement($field) && !in_array($fieldName, $excludeFromFullTextSearch)){
						$fullTextSearchExpressions[] = new Expression(Model::OPERATOR_LIKE, [new Field($className, $field), $fullTextSearchQuery]);
					}
				}
			}
			$whereExpression = new Expression(Model::OPERATOR_OR, $fullTextSearchExpressions);
		}
		return $whereExpression;
	}
	
	private static function parseFilters($request,$modelClassName){
		$filters = array();
		$fieldSpecificFilters = $request->getQueryParam('f');
		if($fieldSpecificFilters != null){
			$fieldSpecificFilters = base64_decode($fieldSpecificFilters);
			$fieldSpecificFilters = json_decode($fieldSpecificFilters,true);
			if(!is_array($fieldSpecificFilters)){
				throw new BadInputException("query parameter f is invalid");
			}
			
			foreach ($fieldSpecificFilters as $field=>$value){
				Reflection::hasProperty($modelClassName, $field);
				if(in_array($field, $fields)){
					if(is_scalar($value)){
						$filters[$field] = $value;
					}else if(is_array($value)){
						foreach ($value as $operator=>$operands){
							// expression validates for the valid expression syntax
							new Expression($operator, [new Field($modelClassName, $field), $operands]);
						}
						$filters[$field] = $value;
					}else{
						throw new BadRequest("query parameter f is invalid");
					}
				}else{
					throw new BadRequest("query parameter f is invalid");
				}
			}
		}
		return $filters;
	}
	
}
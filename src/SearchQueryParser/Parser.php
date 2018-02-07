<?php

namespace PhpPlatform\SearchQueryParser;

use PhpPlatform\RESTFul\HTTPRequest;
use PhpPlatform\Errors\Exceptions\Http\_4XX\BadRequest;
use PhpPlatform\Persist\Expression;
use PhpPlatform\Persist\Model;
use PhpPlatform\Persist\Field;
use PhpPlatform\Persist\RelationalMappingUtil;

class Parser {
	
	/**
	 * This method parses query parameters from rest endpoint to method arguments required by PhpPlatform\Persist\Model::find($filters,$sort,$pagination,$where)
	 * 
	 * @param HTTPRequest $request is the HTTPRequest object received in the rest service 
	 * @param string $modelClassName is the full name of the Model which is served as REST Resource 
	 * @param string[] $excludeFromFullTextSearch is the array of fields which needs to be excluded from performing full text search 
	 * 
	 * @return FindParams
	 */
	static public function parse($request,$modelClassName,$excludeFromFullTextSearch = array()){
		$findParams = new FindParams();
		$findParams->where = self::parseFullTextSearch($request, $modelClassName, $excludeFromFullTextSearch);
		$findParams->filters = self::parseFilters($request, $modelClassName);
		$findParams->sort = self::parseSort($request, $modelClassName);
		$findParams->pagination = self::parsePagination($request);
		return $findParams;
	}
	
	private static function parseFullTextSearch($request,$modelClassName,$excludeFromFullTextSearch){
		if(!is_array($excludeFromFullTextSearch)){
			$excludeFromFullTextSearch = array();
		}
		$whereExpression = null;
		$fullTextSearchQuery = $request->getQueryParam('q');
		if($fullTextSearchQuery != null){
			$fullTextSearchExpressions = array();
			$classList = RelationalMappingUtil::getClassConfiguration($modelClassName);
			foreach ($classList as $className => $class){
				foreach ($class['fields'] as $fieldName => $field){
					if(RelationalMappingUtil::_isGet($field) &&
						!RelationalMappingUtil::_isAutoIncrement($field) &&
						!RelationalMappingUtil::_isReference($field) &&
						!in_array($fieldName, $excludeFromFullTextSearch)){
							if(RelationalMappingUtil::_isForeignField($field)){
								$foreignClassAndField = preg_split("/\-\>/",$field['foreignField']);
								$foreignClassName = $foreignClassAndField[0];
								$foreignFieldName = $foreignClassAndField[1];
								$fullTextSearchExpressions[] = new Expression(Model::OPERATOR_LIKE, [new Field($foreignClassName, $foreignFieldName), $fullTextSearchQuery]);
							}else{
								$fullTextSearchExpressions[] = new Expression(Model::OPERATOR_LIKE, [new Field($className, $fieldName), $fullTextSearchQuery]);
							}
					}
				}
			}
			if(count($fullTextSearchExpressions) == 1){
				$whereExpression = $fullTextSearchExpressions[0];
			}else if(count($fullTextSearchExpressions) > 1){
				$whereExpression = new Expression(Model::OPERATOR_OR, $fullTextSearchExpressions);
			}
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
				throw new BadRequest(['f'=>'invalid']);
			}
			
			$classList = RelationalMappingUtil::getClassConfiguration($modelClassName);
			
			foreach ($classList as $className=>$class){
				foreach ($class['fields'] as $fieldName=>$field){
					if(RelationalMappingUtil::_isGet($field) && array_key_exists($fieldName, $fieldSpecificFilters)){
						$filterValue = $fieldSpecificFilters[$fieldName];
						if(is_scalar($filterValue)){
							$filters[$fieldName]=$filterValue;
						}else if (is_array($filterValue) && count($filterValue) == 1){
							foreach ($filterValue as $operator=>$operands){
								// expression validates for the valid expression syntax
								try{
								    new Expression($operator, [new Field($className, $fieldName), $operands]);
								}catch (\Exception $e){
									throw new BadRequest(['f'=>'invalid']);
								}
							}
							$filters[$fieldName] = $filterValue;
						}else{
							throw new BadRequest(['f'=>'invalid']);
						}
						unset($fieldSpecificFilters[$fieldName]);
					}
				}
			}
			
			if(count($fieldSpecificFilters) != 0){
				throw new BadRequest(['f'=>'invalid']);
			}
		}
		return $filters;
	}
	
	private static function parseSort($request,$modelClassName){
		$sort = array();
		$sortParams = $request->getQueryParam('s');
		if($sortParams!= null){
			$sortParams= base64_decode($sortParams);
			$sortParams= json_decode($sortParams,true);
			if(!is_array($sortParams)){
				throw new BadRequest(['s'=>'invalid']);
			}
			
			$classList = RelationalMappingUtil::getClassConfiguration($modelClassName);
			
			foreach ($classList as $class){
				foreach ($class['fields'] as $fieldName=>$field){
					if(RelationalMappingUtil::_isGet($field) && array_key_exists($fieldName, $sortParams)){
						$sortValue = $sortParams[$fieldName];
						if($sortValue != Model::SORTBY_ASC && $sortValue != Model::SORTBY_DESC){
							throw new BadRequest(['s'=>'invalid']);
						}
						$sort[$fieldName] = $sortValue;
						unset($sortParams[$fieldName]);
					}
				}
			}
			if(count($sortParams) != 0){
				throw new BadRequest(['s'=>'invalid']);
			}
		}
		return $sort;
	}
	
	private static function parsePagination($request){
		$pagination = null;
		$paginationParam = $request->getQueryParam('p');
		if($paginationParam != null){
			$paginationParam = preg_split('/-/', $paginationParam);
			if(count($paginationParam) != 2 ||
					(!is_numeric($paginationParam[0]) || !is_int($paginationParam[0]+0)) ||
					(!is_numeric($paginationParam[1]) || !is_int($paginationParam[1]+0)) ){
						throw new BadRequest(['p'=>'invalid']);
			}
			$pagination = array('pageNumber'=>$paginationParam[0],'pageSize'=>$paginationParam[1]);
		}
		return $pagination;
	}
	
}
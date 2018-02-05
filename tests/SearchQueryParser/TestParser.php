<?php
namespace PhpPlatform\Tests\SearchQueryParser;


use PhpPlatform\RESTFul\HTTPRequest;
use PhpPlatform\SearchQueryParser\FindParams;
use PhpPlatform\SearchQueryParser\Parser;

class TestParser extends \PHPUnit_Framework_TestCase{
	
	/**
	 * @dataProvider parseDataProvider
	 * 
	 * @param HTTPRequest|callable $request
	 * @param string $modelClassName
	 * @param string[] $excludeFromFullTextSearch
	 * @param array $expectedFindParams
	 * @param string $expectedException
	 */
	function testParse($request,$modelClassName, $excludeFromFullTextSearch, $expectedFindParams,$expectedException = null){
		if(is_callable($request)){
			$request = call_user_func($request);
		}
		try{
			$findParams = Parser::parse($request, $modelClassName, $excludeFromFullTextSearch);
			
			$this->assertEquals($expectedFindParams['filters'], $findParams->filters);
			$this->assertEquals($expectedFindParams['sort'], $findParams->sort);
			$this->assertEquals($expectedFindParams['pagination'], $findParams->pagination);
			$this->assertEquals($expectedFindParams['where'], $findParams->where);
			
		}catch (\Exception $e){
			$this->assertEquals($expectedException, $e->getMessage());
		}
	}
	
	function parseDataProvider(){
		return [
			"without any search params"=>[
				$this->getHttpRequestWithQueryParameters([
						
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				['filters'=>[],'sort'=>[],'pagination'=>null,'where'=>null]
			],
			"with filters"=>[
				$this->getHttpRequestWithQueryParameters([
					'f'=>base64_encode(json_encode(['name'=>'myName']))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				['filters'=>['name'=>'myName'],'sort'=>[],'pagination'=>null,'where'=>null]
			]
		];
	}
	
	/**
	 * @param string[][] $queryParams
	 * 
	 * @return HTTPRequest
	 */
	private function getHttpRequestWithQueryParameters($queryParams){
		
		$httpRequestStaticInstance = new \ReflectionProperty('PhpPlatform\RESTFul\HTTPRequest', 'instance');
		$httpRequestStaticInstance->setAccessible(true);
		$httpRequestStaticInstance->setValue(null, null);
		
		$_GET = $queryParams;
		return HTTPRequest::getInstance();
	}
	
}
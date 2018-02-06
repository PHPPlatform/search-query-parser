<?php
namespace PhpPlatform\Tests\SearchQueryParser;


use PhpPlatform\RESTFul\HTTPRequest;
use PhpPlatform\SearchQueryParser\FindParams;
use PhpPlatform\SearchQueryParser\Parser;
use PhpPlatform\Errors\Exceptions\Http\_4XX\BadRequest;

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
			
		}catch (BadRequest $e){
			$this->assertEquals($expectedException, $e->getBody());
		}
	}
	
	function parseDataProvider(){
		$cases = [
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
			],
			"with filters with invalid field"=>[
				$this->getHttpRequestWithQueryParameters([
						'f'=>base64_encode(json_encode(['address'=>'myAddress']))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				[],
				['f'=>'invalid']
			],
			"with filters without base64 encoded"=>[
				$this->getHttpRequestWithQueryParameters([
					'f'=>json_encode(['name'=>'myName'])
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				[],
				['f'=>'invalid']
			],
			"with filters with invalid filter value"=>[
				$this->getHttpRequestWithQueryParameters([
					'f'=>base64_encode(json_encode(['name'=>['and'=>'myName']]))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				[],
				['f'=>'invalid']
			],
			"with filters for non get fields"=>[
				$this->getHttpRequestWithQueryParameters([
					'f'=>base64_encode(json_encode(['password'=>'myName']))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				[],
				['f'=>'invalid']
			],
			"with filters with other operators"=>[
				$this->getHttpRequestWithQueryParameters([
					'f'=>base64_encode(json_encode(['name'=>['LIKE'=>'myName']]))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				['filters'=>['name'=>['LIKE'=>'myName']],'sort'=>[],'pagination'=>null,'where'=>null]
			],
			"with filters for child model"=>[
				$this->getHttpRequestWithQueryParameters([
					'f'=>base64_encode(json_encode(['name'=>['LIKE'=>'myName']]))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M2',
				null,
				['filters'=>['name'=>['LIKE'=>'myName']],'sort'=>[],'pagination'=>null,'where'=>null]
			],
			"with filters for reference and autoIncrement fields"=>[
				$this->getHttpRequestWithQueryParameters([
						'f'=>base64_encode(json_encode(['id'=>'1','m1Id'=>'2']))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M2',
				null,
				['filters'=>['id'=>'1','m1Id'=>'2'],'sort'=>[],'pagination'=>null,'where'=>null]
			],
				
			"with sort"=>[
				$this->getHttpRequestWithQueryParameters([
					's'=>base64_encode(json_encode(['name'=>'ASC']))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				['filters'=>[],'sort'=>['name'=>'ASC'],'pagination'=>null,'where'=>null]
			],
			"with sort with invalid field"=>[
				$this->getHttpRequestWithQueryParameters([
					's'=>base64_encode(json_encode(['address'=>'ASC']))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				[],
				['s'=>'invalid']
			],
			"with sort without base64 encoded"=>[
				$this->getHttpRequestWithQueryParameters([
					's'=>json_encode(['name'=>'myName'])
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				[],
				['s'=>'invalid']
			],
			"with sort with invalid sort value"=>[
				$this->getHttpRequestWithQueryParameters([
					's'=>base64_encode(json_encode(['name'=>['and'=>'myName']]))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				[],
				['s'=>'invalid']
			],
			"with sort with invalid sort option"=>[
				$this->getHttpRequestWithQueryParameters([
						's'=>base64_encode(json_encode(['name'=>'increasing']))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				[],
				['s'=>'invalid']
			],
			"with sort for non-get fields"=>[
				$this->getHttpRequestWithQueryParameters([
						's'=>base64_encode(json_encode(['password'=>'ASC']))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M1',
				null,
				[],
				['s'=>'invalid']
			],
			"with sort for child model"=>[
				$this->getHttpRequestWithQueryParameters([
					's'=>base64_encode(json_encode(['name'=>'DESC']))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M2',
				null,
				['filters'=>[],'sort'=>['name'=>'DESC'],'pagination'=>null,'where'=>null]
			],
			"with sort for reference and autoIncrement fields"=>[
				$this->getHttpRequestWithQueryParameters([
					's'=>base64_encode(json_encode(['id'=>'ASC','m1Id'=>'DESC']))
				]),
				'PhpPlatform\Tests\SearchQueryParser\Models\M2',
				null,
				['filters'=>[],'sort'=>['id'=>'ASC','m1Id'=>'DESC'],'pagination'=>null,'where'=>null]
			]
				
		];
		//return [$cases['with filters with other operators']];
		return $cases;
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
# A Library for parsing search query parameters

[![Build Status](https://travis-ci.org/PHPPlatform/search-query-parser.svg?branch=master)](https://travis-ci.org/PHPPlatform/search-query-parser)

When a Model from [php-platform/persist](https://github.com/PHPPlatform/persist) is exposed as REST Resource ([php-platform/restful](https://github.com/PHPPlatform/restful)), It is required that the REST End point to list all resources must support **filter** , **sort** and **pagination**

Models extending [php-platform/persist](https://github.com/PHPPlatform/persist) supports **filter** , **sort** and **pagination** through arguments to [PhpPlatform\Persist\Model::find($filters,$sort,$pagination,$where)](https://github.com/PHPPlatform/persist/blob/master/src/Persist/Model.php#L324) method

So this library parses query parameters from rest endpoint to method arguments required by `PhpPlatform\Persist\Model::find($filters,$sort,$pagination,$where)`


## How to use

``` php
$searchParams = PhpPlatform\SearchQueryParser\Parser::parse(PhpPlatform\RESTFul\HTTPRequest $request,string $modelClassName, array $excludeFromFullTextSearch);
```

#### where
- **$request** is the HTTPRequest object received in the rest service
- **$modelClassName** is the full name of the Model which is served as REST Resource
- **$excludeFromFullTextSearch** is the array of fields which needs to be excluded from performing full text search

#### returns
An instance of `PhpPlatform\SearchQueryParser\FindParams`

## Parse rules

| Query Param | Method Parameter | Format |
| -----------------|-----------|--------|
| q | $where | `string` for full text search , a complex `$where` expression is formed matching all the `get` fields from the Model , excluding the once mentioned in `$excludeFromFullTextSearch`. *Auto Increment* fields are excluded by default |
| f | $filters | `base64` encoded `json` string representing the `$filters` object |
| s | $sort | `base64` encoded `json` string representing the `$sort` object |
| p | $pagination | `'<pageNumber>-<pageSize>'` |

> Note 1 : This API throws `PhpPlatform\Errors\Exceptions\Http\_4XX\BadRequest` Exception if the query parameters are invalid
> Note 2 : All of the above query parameters are optional

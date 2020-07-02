<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;


trait ApiResponser {
    private function successResponse($data, $code)
    {
        return response()->json($data, $code);
    }

    protected function errorResponse($message, $code)
    {
        return response()->json(['data' => $message, 'code' => $code], $code);
    }

    protected function showAll(Collection $collection, $code = 200)
    {
        if($collection->isEmpty()) {
            return $this->successResponse(['data' => $collection], $code);
        }

        $transformer = $collection->first()->transformer;

        $collection = $this->filterData($collection, $transformer);
        $collection = $this->sortData($collection, $transformer);
        $collection = $this->paginate($collection);
        $collection = $this->transformData($collection, $transformer);
        $collection = $this->cacheResponse($collection);

        return $this->successResponse(['data' => $collection], $code);
    }

    protected function showOne(Model $instance, $code = 200)
    {
        $transformer = $instance->transformer;
        $instance = $this->transformData($instance, $transformer);

        return $this->successResponse(['data' => $instance], $code);
    }

    protected function showMessage($message, $code = 200)
    {
        return $this->successResponse(['message' => $message], $code);
    }

    protected function filterData(Collection $collection, $transformer)
    {
        foreach(request()->query() as $query => $value) {
            $attribute = $transformer::originalAttribute($query);

            if(isset($attribute, $value)) {
                $collection = $collection->where($attribute, $value);
            }
        }

        return $collection;
    }

    protected function sortData(Collection $collection, $transformer)
    {
        if(request()->has('sort_by')) {
            $attribute = $transformer::originalAttribute(request()->sort_by);
            $collection = $collection->sortBy->{$attribute};
        }

        return $collection;
    }

    protected function paginate(Collection $collection)
    {
        $rules = [
            'per_page' => 'integer|min:2|max:50',
        ];

        Validator::validate(request()->all(), $rules);

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $perPage = 3;

        if(request()->has('per_page')) {
            $perPage = (int)request()->per_page;
        }
        $results = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginated = new LengthAwarePaginator(
            $results,
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath()
            ]
        );

        $paginated->appends(request()->all());

        return $paginated;
    }

    protected function transformData($data, $transformer)
    {
        $transformation = fractal($data, new $transformer);

        return $transformation->toArray();
    }

    protected function cacheResponse($data)
    {
        // grep request url
        $url = request()->url();

        // grep query params
        $queryParams = request()->query();

        // sort keys
        ksort($queryParams);

        // convert query params to string
        $queryString = http_build_query($queryParams);

        $fullUrl = "{$url}?{$queryString}";

        $seconds = 60;

        return Cache::remember($fullUrl, $seconds, function() use($data) {
           return $data;
        });
    }
}

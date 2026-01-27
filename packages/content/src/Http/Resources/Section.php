<?php

namespace Lyre\Content\Http\Resources;

use Illuminate\Http\Request;
use Lyre\Content\Models\Section as SectionModel;
use Lyre\Resource;

class Section extends Resource
{
    public function __construct(SectionModel $model)
    {
        parent::__construct($model);
    }

    public function toArray(Request $request): array
    {
        $result = parent::toArray($request);

        if (isset($result['data'])) {

            $data = [];

            foreach ($result['data'] as $item) {
                $data[\Illuminate\Support\Str::lower($item->name)] = dataRepository()->resolve($item->resource);
            }

            $result['data'] = $data;
        }

        return $result;
    }
}

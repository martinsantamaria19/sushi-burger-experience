<?php

namespace App\Repositories\Eloquent;

use App\Models\Restaurant;
use App\Repositories\Interfaces\RestaurantRepositoryInterface;

class RestaurantRepository extends BaseRepository implements RestaurantRepositoryInterface
{
    public function __construct(Restaurant $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function findByUuid(string $uuid)
    {
        return $this->model->where('uuid', $uuid)->first();
    }
}

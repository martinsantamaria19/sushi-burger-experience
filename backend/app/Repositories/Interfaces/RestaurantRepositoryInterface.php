<?php

namespace App\Repositories\Interfaces;

interface RestaurantRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug);
    public function findByUuid(string $uuid);
}

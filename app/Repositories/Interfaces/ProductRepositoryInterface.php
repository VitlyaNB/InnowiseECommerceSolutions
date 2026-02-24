<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface
{
    public function getAll(int $perPage = 15);
    public function getByCategory(int $categoryId);
    public function getById(int $id);
    public function create(array $data);
}

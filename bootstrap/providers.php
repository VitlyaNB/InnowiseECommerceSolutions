<?php

use App\Providers\AppServiceProvider;
use Matchish\ScoutElasticSearch\ElasticSearchServiceProvider;

return [
    AppServiceProvider::class,
    ElasticSearchServiceProvider::class,
];

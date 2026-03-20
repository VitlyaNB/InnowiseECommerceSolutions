<?php

namespace App\Infrastructure\Persistence;

use App\Infrastructure\Interfaces\TransactionManagerInterface;
use Closure;
use Illuminate\Support\Facades\DB;

final readonly class EloquentTransactionManager implements TransactionManagerInterface
{
    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    public function commit(): void
    {
        DB::commit();
    }

    public function rollBack(): void
    {
        DB::rollBack();
    }

    public function transaction(callable $callback): mixed
    {
        return DB::transaction(Closure::fromCallable($callback));
    }
}

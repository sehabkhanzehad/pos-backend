<?php

namespace App\Http\Resources\Tratis;

use Illuminate\Support\Collection;

trait HasRelationship
{
    protected function relationship(string $relation, string $type): array
    {
        return [
            'data' => $this->whenLoaded(
                $relation,
                fn() => $this->toIdentifiers($this->{$relation}, $type)
            ),
        ];
    }

    private function toIdentifiers(Collection $items, string $type): array
    {
        return $items->map(fn($model) => [
            'type' => $type,
            'id'   => $model->id,
        ])->values()->all();
    }
}

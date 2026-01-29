<?php

namespace App\Services\User;

use App\Models\Favorite;

class FavoriteService
{
    protected array $typeMap = [
        'product' => \App\Models\Product::class,
        'recipe'  => \App\Models\Recipe::class,
        'brand'   => \App\Models\Brand::class,
        'basket'  => \App\Models\Basket::class,
    ];

    public function toggle(int $userId, string $type, int $id): bool
    {
        $modelClass = $this->getModelClass($type);

        $favorite = Favorite::where([
            'user_id'           => $userId,
            'favoriteable_type' => $modelClass,
            'favoriteable_id'   => $id,
        ])->first();

        if ($favorite) {
            $favorite->delete();
            return false;
        }

        Favorite::create([
            'user_id'           => $userId,
            'favoriteable_type' => $modelClass,
            'favoriteable_id'   => $id,
        ]);

        return true;
    }

    public function list(int $userId, ?string $type = null)
    {
        $query = Favorite::where('user_id', $userId)
            ->with('favoriteable');

        if ($type) {
            $query->where('favoriteable_type', $this->getModelClass($type));
        }

        return $query->get()
            ->map(function ($fav) {
                return $fav->favoriteable?->toSectionArray();
            })
            ->filter()
            ->values();
    }

    protected function getModelClass(string $type): string
    {
        if (!isset($this->typeMap[$type])) {
            throw new \InvalidArgumentException("Invalid favorite type: {$type}");
        }

        return $this->typeMap[$type];
    }
}

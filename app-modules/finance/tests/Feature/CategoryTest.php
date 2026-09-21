<?php

use Tequia\Finance\Enums\CategoryType;
use Tequia\Finance\Models\Category;

it('casts type to a CategoryType enum', function () {
    $category = Category::factory()->create(['type' => CategoryType::Expense]);

    expect($category->fresh()->type)->toBe(CategoryType::Expense);
});

it('can exist without a parent category', function () {
    $category = Category::factory()->create(['parent_id' => null]);

    expect($category->parent)->toBeNull();
});

it('belongs to a parent category', function () {
    $parent = Category::factory()->create(['name' => 'Transporte']);
    $child = Category::factory()->create(['name' => 'Combustible', 'parent_id' => $parent->id]);

    expect($child->parent)->toBeInstanceOf(Category::class)
        ->and($child->parent->id)->toBe($parent->id);
});

it('lists its child categories', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);

    expect($parent->fresh()->children)->toHaveCount(1)
        ->and($parent->children->first()->id)->toBe($child->id);
});

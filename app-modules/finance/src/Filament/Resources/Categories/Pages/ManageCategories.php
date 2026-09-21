<?php

namespace Tequia\Finance\Filament\Resources\Categories\Pages;

use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tequia\Finance\Enums\CategoryType;
use Tequia\Finance\Filament\Resources\Categories\CategoryResource;
use Tequia\Finance\Models\Category;

class ManageCategories extends Page
{
    protected static string $resource = CategoryResource::class;

    protected string $view = 'finance::filament.resources.categories.pages.manage-categories';

    public string $activeType = 'expense';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $categories = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $parentOptions = [];

    public ?int $editingId = null;

    public string $name = '';

    public string $type = 'expense';

    /**
     * Sin tipo estricto a propósito: el <select> envía "" cuando no hay selección,
     * y un ?int no admite esa asignación (PHP la rechaza por no ser numérica).
     */
    public $parentId = null;

    public function mount(): void
    {
        $this->refreshCategories();
    }

    public function setActiveType(string $type): void
    {
        $this->activeType = $type;
        $this->refreshCategories();
    }

    public function openCreateModal(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->type = $this->activeType;
        $this->parentId = null;
        $this->refreshParentOptions();
        $this->dispatch('open-modal', id: 'category-form-modal');
    }

    public function openEditModal(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->type = $category->type->value;
        $this->parentId = $category->parent_id;
        $this->refreshParentOptions();
        $this->dispatch('open-modal', id: 'category-form-modal');
    }

    public function save(): void
    {
        $data = Validator::make(
            [
                'name' => $this->name,
                'type' => $this->type,
                'parent_id' => $this->parentId ?: null,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', Rule::enum(CategoryType::class)],
                'parent_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('categories', 'id')->where('type', $this->type),
                    Rule::notIn([$this->editingId]),
                ],
            ],
        )->validate();

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($data);
        } else {
            Category::create($data);
        }

        $this->dispatch('close-modal', id: 'category-form-modal');
        $this->activeType = $data['type'];
        $this->refreshCategories();
    }

    public function delete(int $categoryId): void
    {
        Category::findOrFail($categoryId)->delete();

        $this->refreshCategories();
    }

    private function refreshParentOptions(): void
    {
        $this->parentOptions = Category::query()
            ->where('type', $this->type)
            ->when($this->editingId, fn ($query) => $query->whereKeyNot($this->editingId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => ['id' => $category->id, 'name' => $category->name])
            ->all();
    }

    private function refreshCategories(): void
    {
        $this->categories = Category::query()
            ->where('type', $this->activeType)
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->orderBy('name')])
            ->withCount('movements')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => $this->serializeCategory($category))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'movementsCount' => $category->movements_count,
            'children' => $category->children
                ->map(fn (Category $child): array => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'movementsCount' => $child->movements()->count(),
                ])
                ->all(),
        ];
    }
}

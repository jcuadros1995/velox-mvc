<?php
// app/Services/CategoryService.php

class CategoryService
{
    private CategoryRepository $repository;

    public function __construct()
    {
        $this->repository = new CategoryRepository();
    }

    public function getAllForAdmin(): array
    {
        return $this->repository->getAllForAdmin();
    }

    public function getParents(?int $excludeId = null): array
    {
        return $this->repository->getParents($excludeId);
    }

    public function get(int $id): ?array
    {
        return $this->repository->find($id);
    }

    public function create(array $input): int
    {
        $data = $this->prepare($input);

        return $this->repository->create($data);
    }

    public function update(int $id, array $input): bool
    {
        $category = $this->repository->find($id);

        if (!$category) {
            throw new DomainException('La categoría no existe.', 404);
        }

        $data = $this->prepare($input, $id);

        return $this->repository->update($id, $data);
    }

    public function setActive(int $id, bool $active): bool
    {
        if (!$this->repository->find($id)) {
            throw new DomainException('La categoría no existe.', 404);
        }

        return $this->repository->setActive($id, $active ? 1 : 0);
    }

    public function getPage(int $categoryId): ?array
    {
        return $this->repository->getPageByCategoryId($categoryId);
    }

    public function savePage(int $categoryId, array $input): int
    {
        $category = $this->repository->find($categoryId);

        if (!$category) {
            throw new DomainException('La categoría no existe.', 404);
        }

        $slug = $this->slugify(
            trim((string) ($input['slug'] ?? ''))
        );

        if ($slug === '') {
            throw new DomainException('El slug de la página es obligatorio.', 422);
        }

        $title = trim((string) ($input['title'] ?? ''));

        if ($title === '') {
            $title = $category['name'];
        }

        return $this->repository->savePage($categoryId, [
            'slug' => $slug,
            'title' => $title,
            'meta_title' => $this->nullableString($input['meta_title'] ?? null),
            'meta_description' => $this->nullableString($input['meta_description'] ?? null),
            'h1' => $this->nullableString($input['h1'] ?? null),
            'intro_text' => $this->nullableString($input['intro_text'] ?? null),
            'hero_image' => $this->nullableString($input['hero_image'] ?? null),
            'content_top' => $this->nullableString($input['content_top'] ?? null),
            'content_bottom' => $this->nullableString($input['content_bottom'] ?? null),
            'faq_enabled' => $this->toBool($input['faq_enabled'] ?? 0),
            'is_published' => $this->toBool($input['is_published'] ?? 0)
        ]);
    }

    private function prepare(array $input, ?int $currentId = null): array
    {
        $name = trim((string) ($input['name'] ?? ''));

        if ($name === '') {
            throw new DomainException('El nombre de la categoría es obligatorio.', 422);
        }

        if (mb_strlen($name) > 100) {
            throw new DomainException('El nombre de la categoría no puede superar 100 caracteres.', 422);
        }

        $slug = $this->slugify(
            trim((string) ($input['slug'] ?? ''))
        );

        if ($slug === '') {
            $slug = $this->slugify($name);
        }

        $parentId = null;

        if (isset($input['parent_id']) && $input['parent_id'] !== '') {
            $parentId = (int) $input['parent_id'];

            if ($parentId <= 0) {
                $parentId = null;
            }
        }

        if ($currentId !== null) {
            if ($parentId === $currentId) {
                throw new DomainException('Una categoría no puede ser su propia categoría padre.', 422);
            }

            if ($parentId !== null) {
                $descendants = $this->repository->getDescendantIds($currentId);

                if (in_array($parentId, $descendants, true)) {
                    throw new DomainException('No puedes asignar una subcategoría como categoría padre.', 422);
                }
            }
        }

        if ($parentId !== null && !$this->repository->find($parentId)) {
            throw new DomainException('La categoría padre seleccionada no existe.', 422);
        }

        $catalogType = strtolower(
            trim((string) ($input['catalog_type'] ?? 'general'))
        );

        if (!in_array($catalogType, ['general', 'restaurant'], true)) {
            $catalogType = 'general';
        }

        return [
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $slug,
            'catalog_type' => $catalogType,
            'description' => $this->nullableString($input['description'] ?? null),
            'is_active' => $this->toBool($input['is_active'] ?? 0)
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function toBool(mixed $value): int
    {
        return in_array($value, [1, '1', true, 'true', 'on'], true)
            ? 1
            : 0;
    }

    private function slugify(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $converted = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $value
        );

        if ($converted !== false) {
            $value = $converted;
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim((string) $value, '-');

        return substr($value, 0, 120);
    }
}

<?php

namespace App\Services\Ai\ContextBuilders;

use App\Models\Course;
use App\Models\CourseMaterial;
use Illuminate\Support\Collection;

class MaterialsContextBuilder
{
    /**
     * @param  Collection<int, CourseMaterial>|list<CourseMaterial>  $materials
     * @return array<string, mixed>
     */
    public function build(Course $course, Collection|array $materials): array
    {
        $items = collect($materials);

        return [
            'course' => [
                'code' => $course->code,
                'title' => $course->title,
            ],
            'materials' => $items->map(function (CourseMaterial $material) {
                return [
                    'id' => $material->id,
                    'title' => $material->title,
                    'description' => $material->description,
                    'category' => $material->category?->value ?? $material->category,
                    'excerpt' => $this->excerpt($material),
                ];
            })->values()->all(),
        ];
    }

    public function excerpt(CourseMaterial $material, int $max = 1200): string
    {
        $parts = array_filter([
            $material->title,
            $material->description,
            $material->external_url ? 'URL: '.$material->external_url : null,
            $material->file_name ? 'File: '.$material->file_name : null,
        ]);

        $text = implode("\n", $parts);

        return mb_substr($text, 0, $max);
    }
}

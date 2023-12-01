<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;

class Article
{
    private string $title;
    private string $description;
    private string $picture;
    private ?Carbon $createdAt;
    private ?int $id;
    private ?Carbon $updatedAt;

    public function __construct(
        string  $title,
        string  $description,
        string  $picture = null,
        ?string $createdAt = null,
        ?int    $id = null,
        ?string $updatedAt = null
    )
    {
        $this->title = $title;
        $this->description = $description;
        $this->picture = $picture ?? "http://placekitten.com/500/500";
        $this->createdAt = $createdAt == null ? Carbon::now() : new Carbon($createdAt);
        $this->id = $id;
        $this->updatedAt = $updatedAt ? Carbon::parse($updatedAt) : null;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPicture(): string
    {
        return $this->picture;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdatedAt(): ?Carbon
    {
        return $this->updatedAt;
    }

    public function update(array $data): void
    {
        $this->title = $data['title'] ?? $this->title;
        $this->description = $data['description'] ?? $this->description;
        $this->picture = $data['picture'] ?? $this->picture;
        $this->updatedAt = Carbon::now();
    }
}
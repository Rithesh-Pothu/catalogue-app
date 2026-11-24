<?php

namespace App\Catalogue;

/**
 * A product in our own shape. Nothing outside the product source adapters
 * knows the field names of the system the data came from.
 */
final readonly class Product
{
    public function __construct(
        public int $id,
        public string $title,
        public float $price,
        public string $description,
        public string $category,
        public string $image,
        public ?float $rating = null,
    ) {}

    public function withoutRating(): self
    {
        return new self(
            $this->id,
            $this->title,
            $this->price,
            $this->description,
            $this->category,
            $this->image,
            null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'description' => $this->description,
            'category' => $this->category,
            'image' => $this->image,
            'rating' => $this->rating,
        ];
    }
}

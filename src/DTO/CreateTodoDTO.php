<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTodoDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
<?php
namespace Pyncer\Database\Table;

trait CommentTrait
{
    private ?string $comment = null;

    public function getComment(): ?string
    {
        return $this->comment;
    }
    public function setComment(?string $value): static
    {
        $this->comment = ($value === '' ? null : $value);
        return $this;
    }
}

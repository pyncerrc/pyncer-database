<?php
namespace Pyncer\Database\Table;

interface CommentInterface
{
    public function getComment(): ?string;
    public function setComment(?string $value): static;
}

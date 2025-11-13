<?php

namespace Entities;

abstract class User
{
    protected int $id;
    protected string $name;
    protected string $role;

    public function __construct(int $id, string $name, string $role)
    {
        $this->id = $id;
        $this->name = $name;
        $this->role = $role;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    abstract public function getTextsToEdit(Storage $storage): array;
}

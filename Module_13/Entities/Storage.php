<?php

namespace Entities;

abstract class Storage
{
    abstract public function create(object $object): string;
    abstract public function read(string $slug): ?object;
    abstract public function update(string $slug, object $object): void;
    abstract public function delete(string $slug): void;
    abstract public function list(): array;
}

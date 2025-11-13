<?php

namespace Entities;

class FileStorage extends Storage
{
    private string $storagePath;

    public function __construct(string $storagePath = '')
    {
        $this->storagePath = $storagePath ?: __DIR__ . '/../storage/';
        
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0777, true);
        }
    }

    public function create(object $object): string
    {
        if (!$object instanceof TelegraphText) {
            throw new \InvalidArgumentException('FileStorage поддерживает только объекты TelegraphText');
        }

        $baseFilename = $object->slug . '_' . date('Y-m-d');
        $filename = $baseFilename;
        $counter = 1;

        while (file_exists($this->storagePath . $filename)) {
            $filename = $baseFilename . '_' . $counter;
            $counter++;
        }

        $object->slug = $filename;

        $serializedData = serialize($object);
        file_put_contents($this->storagePath . $filename, $serializedData);

        return $filename;
    }

    public function read(string $slug): ?TelegraphText
    {
        $filepath = $this->storagePath . $slug;
        
        if (!file_exists($filepath)) {
            return null;
        }

        $fileContent = file_get_contents($filepath);
        
        if (empty($fileContent)) {
            return null;
        }

        $object = unserialize($fileContent);
        
        if (!$object instanceof TelegraphText) {
            return null;
        }

        return $object;
    }

    public function update(string $slug, object $object): void
    {
        if (!$object instanceof TelegraphText) {
            throw new \InvalidArgumentException('FileStorage поддерживает только объекты TelegraphText');
        }

        $filepath = $this->storagePath . $slug;
        
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Файл {$slug} не найден");
        }

        $object->slug = $slug;

        $serializedData = serialize($object);
        file_put_contents($filepath, $serializedData);
    }

    public function delete(string $slug): void
    {
        $filepath = $this->storagePath . $slug;
        
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    public function list(): array
    {
        $files = scandir($this->storagePath);
        $objects = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filepath = $this->storagePath . $file;
            if (is_file($filepath)) {
                $fileContent = file_get_contents($filepath);
                
                if (!empty($fileContent)) {
                    $object = unserialize($fileContent);
                    
                    if ($object instanceof TelegraphText) {
                        $objects[] = $object;
                    }
                }
            }
        }

        return $objects;
    }
}

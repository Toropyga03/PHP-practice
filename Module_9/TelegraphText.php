<?php

abstract class Storage
{
    abstract public function create(object $object): string;
    abstract public function read(string $slug): ?object;
    abstract public function update(string $slug, object $object): void;
    abstract public function delete(string $slug): void;
    abstract public function list(): array;
}

abstract class User
{
    public int $id;
    public string $name;
    public string $role;
    
    abstract public function getTextsToEdit(): array;
}

class FileStorage extends Storage
{
    private string $storagePath;
    
    public function __construct(string $storagePath = '')
    {
        $this->storagePath = $storagePath ?: __DIR__ . '/storage/';
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0777, true);
        }
    }
    
    public function create(object $object): string
    {
        $baseSlug = $object->slug ?? 'unknown';
        $date = date('Y-m-d');
        $filename = $baseSlug . '_' . $date;
        $extension = '.txt';
        
        $counter = 0;
        $fullPath = $this->storagePath . $filename . $extension;
        
        while (file_exists($fullPath)) {
            $counter++;
            $fullPath = $this->storagePath . $filename . '_' . $counter . $extension;
        }
        
        if ($counter > 0) {
            $filename = $filename . '_' . $counter;
        }
        
        $object->slug = $filename;
        $serializedData = serialize($object);
        file_put_contents($fullPath, $serializedData);
        
        return $filename;
    }
    
    public function read(string $slug): ?object
    {
        $filename = $this->storagePath . $slug . '.txt';
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $fileContent = file_get_contents($filename);
        
        if (empty($fileContent)) {
            return null;
        }
        
        return unserialize($fileContent);
    }
    
    public function update(string $slug, object $object): void
    {
        $filename = $this->storagePath . $slug . '.txt';
        
        if (!file_exists($filename)) {
            throw new RuntimeException("File with slug '{$slug}' not found");
        }
        
        $object->slug = $slug;
        $serializedData = serialize($object);
        file_put_contents($filename, $serializedData);
    }
    
    public function delete(string $slug): void
    {
        $filename = $this->storagePath . $slug . '.txt';
        
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
    
    public function list(): array
    {
        $files = scandir($this->storagePath);
        $objects = [];
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || pathinfo($file, PATHINFO_EXTENSION) !== 'txt') {
                continue;
            }
            
            $slug = pathinfo($file, PATHINFO_FILENAME);
            $object = $this->read($slug);
            
            if ($object !== null) {
                $objects[] = $object;
            }
        }
        
        return $objects;
    }
}

class TelegraphText
{
    public string $title;
    public string $text;
    public string $author;
    public string $published;
    public string $slug;

    public function __construct(string $title, string $author, string $text)
    {
        $this->title = $title;
        $this->author = $author;
        $this->text = $text;
        $this->published = date('Y-m-d H:i:s');
        $this->slug = $this->generateSlug($title);
    }

    private function generateSlug(string $title): string
    {
        return str_replace(' ', '-', $title);
    }

    public function editText(string $title, string $text): void
    {
        $this->title = $title;
        $this->text = $text;
        $this->slug = $this->generateSlug($title);
    }
}

class Author extends User
{
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->role = 'author';
    }
    
    public function getTextsToEdit(): array
    {
        return [];
    }
}

class Administrator extends User
{
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->role = 'administrator';
    }
    
    public function getTextsToEdit(): array
    {
        $storage = new FileStorage();
        return $storage->list();
    }
}

/*
// Демонстрация работы
echo "=== Демонстрация работы FileStorage ===\n\n";

// Создаем хранилище
$storage = new FileStorage();

// Создаем тексты
$text1 = new TelegraphText("Первый текст", "Автор 1", "Содержимое первого текста");
$text2 = new TelegraphText("Второй текст", "Автор 2", "Содержимое второго текста");

echo "Создание текстов в хранилище:\n";
$slug1 = $storage->create($text1);
echo "Создан текст со slug: {$slug1}\n";

$slug2 = $storage->create($text2);
echo "Создан текст со slug: {$slug2}\n\n";

// Чтение текстов
echo "Чтение текстов из хранилища:\n";
$loadedText1 = $storage->read($slug1);
if ($loadedText1 instanceof TelegraphText) {
    echo "Прочитан текст: {$loadedText1->title} от {$loadedText1->author}\n";
}

$loadedText2 = $storage->read($slug2);
if ($loadedText2 instanceof TelegraphText) {
    echo "Прочитан текст: {$loadedText2->title} от {$loadedText2->author}\n\n";
}

// Обновление текста
echo "Обновление текста:\n";
if ($loadedText1 instanceof TelegraphText) {
    $loadedText1->editText("Обновленный первый текст", "Новое содержимое первого текста");
    $storage->update($slug1, $loadedText1);
    echo "Текст обновлен: {$loadedText1->title}\n\n";
}

// Список всех текстов
echo "Список всех текстов в хранилище:\n";
$allTexts = $storage->list();
foreach ($allTexts as $text) {
    if ($text instanceof TelegraphText) {
        echo "- {$text->title} (slug: {$text->slug})\n";
    }
}
echo "\n";

// Удаление текста
echo "Удаление текста с slug: {$slug2}\n";
$storage->delete($slug2);

// Проверяем список после удаления
echo "Список текстов после удаления:\n";
$remainingTexts = $storage->list();
foreach ($remainingTexts as $text) {
    if ($text instanceof TelegraphText) {
        echo "- {$text->title} (slug: {$text->slug})\n";
    }
}
*/

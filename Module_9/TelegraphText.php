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
        if (!$object instanceof TelegraphText) {
            throw new InvalidArgumentException('Only TelegraphText objects can be stored');
        }
        
        $baseSlug = $object->slug;
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
    
    public function read(string $slug): ?TelegraphText
    {
        $filename = $this->storagePath . $slug . '.txt';
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $fileContent = file_get_contents($filename);
        
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
            throw new InvalidArgumentException('Only TelegraphText objects can be stored');
        }
        
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
        $texts = [];
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || pathinfo($file, PATHINFO_EXTENSION) !== 'txt') {
                continue;
            }
            
            $slug = pathinfo($file, PATHINFO_FILENAME);
            $text = $this->read($slug);
            
            if ($text instanceof TelegraphText) {
                $texts[] = $text;
            }
        }
        
        return $texts;
    }
}

class TelegraphText
{
    public string $title;
    public string $text;
    public string $author;
    public string $published;
    public string $slug;
    
    const FILE_EXTENSION = '.txt';

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

    public function storeText(): string
    {
        $data = [
            'text' => $this->text,
            'title' => $this->title,
            'author' => $this->author,
            'published' => $this->published
        ];

        $serializedData = serialize($data);

        $filename = $this->slug . self::FILE_EXTENSION;
        file_put_contents($filename, $serializedData);
        
        return $this->slug;
    }

    public static function loadText(string $slug): ?TelegraphText
    {
        $filename = $slug . self::FILE_EXTENSION;
        
        if (!file_exists($filename)) {
            echo "ОШИБКА: Файл '{$filename}' не существует!\n\n";
            return null;
        }
        
        $fileContent = file_get_contents($filename);
                
        if (empty($fileContent)) {
            echo "ОШИБКА: Файл '{$filename}' пустой!\n\n";
            return null;
        }
        
        $data = unserialize($fileContent);
        
        if (!is_array($data)) {
            echo "ОШИБКА: Не удалось десериализовать данные!\n\n";
            return null;
        }
        
        $telegraphText = new TelegraphText(
            $data['title'] ?? 'Без названия',
            $data['author'] ?? 'Неизвестный автор',
            $data['text'] ?? 'Текст отсутствует'
        );

        $telegraphText->published = $data['published'] ?? date('Y-m-d H:i:s');
        $telegraphText->slug = $slug;
        
        return $telegraphText;
    }

    public function editText(string $title, string $text): void
    {
        $this->title = $title;
        $this->text = $text;      
    }
}

// Демонстрация работы
echo "=== Демонстрация работы FileStorage ===\n\n";

// Создаем хранилище
$storage = new FileStorage();

// Создаем тексты
$text1 = new TelegraphText("Первый текст", "Автор 1", "Содержимое первого текста");
$text2 = new TelegraphText("Второй текст", "Автор 2", "Содержимое второго текста");

// Сохраняем тексты в хранилище
echo "Создание текстов в хранилище:\n";
$slug1 = $storage->create($text1);
echo "Создан текст со slug: {$slug1}\n";

$slug2 = $storage->create($text2);
echo "Создан текст со slug: {$slug2}\n\n";

// Чтение текстов
echo "Чтение текстов из хранилища:\n";
$loadedText1 = $storage->read($slug1);
if ($loadedText1) {
    echo "Прочитан текст: {$loadedText1->title} от {$loadedText1->author}\n";
}

$loadedText2 = $storage->read($slug2);
if ($loadedText2) {
    echo "Прочитан текст: {$loadedText2->title} от {$loadedText2->author}\n\n";
}

// Обновление текста
echo "Обновление текста:\n";
if ($loadedText1) {
    $loadedText1->editText("Обновленный первый текст", "Новое содержимое первого текста");
    $storage->update($slug1, $loadedText1);
    echo "Текст обновлен: {$loadedText1->title}\n\n";
}

// Список всех текстов
echo "Список всех текстов в хранилище:\n";
$allTexts = $storage->list();
foreach ($allTexts as $text) {
    echo "- {$text->title} (slug: {$text->slug})\n";
}
echo "\n";

// Удаление текста
echo "Удаление текста с slug: {$slug2}\n";
$storage->delete($slug2);

// Проверяем список после удаления
echo "Список текстов после удаления:\n";
$remainingTexts = $storage->list();
foreach ($remainingTexts as $text) {
    echo "- {$text->title} (slug: {$text->slug})\n";
}
echo "\n";

// Проверяем оригинальный функционал TelegraphText
echo "=== Проверка оригинального функционала TelegraphText ===\n\n";

$telegraph = new TelegraphText(
    "Заголовок первого текста",
    "Иван Иванов", 
    "Это содержимое моего первого текста."
);

$telegraph->editText(
    "Обновленный заголовок",
    "Это обновленное содержимое первого текста после редактирования."
);

$slug1 = $telegraph->storeText();

$loadedText1 = TelegraphText::loadText($slug1);
echo $loadedText1->title . "\n";

$loadedText1->editText(
    "Дважды обновленный заголовок",
    "Это обновленное содержимое первого текста после второго редактирования."
);

$slug2 = $loadedText1->storeText();

$loadedText2 = TelegraphText::loadText($slug2);
echo $loadedText2->title . "\n";

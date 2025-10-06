<?php

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

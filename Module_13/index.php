<?php

require_once 'autoload.php';

use Entities\TelegraphText;
use Entities\FileStorage;
use Core\Templates\Swig;
use Core\Templates\Spl;

// Создаем объект TelegraphText
$telegraphText = new TelegraphText(
    'Заголовок статьи',
    'Автор статьи',
    'Текст статьи для демонстрации работы шаблонов и хранилища.'
);

echo "=== ДЕМОНСТРАЦИЯ РАБОТЫ TELEGRAPH TEXT ===\n\n";

// Демонстрация работы с FileStorage
echo "1. Работа с FileStorage:\n";
$storage = new FileStorage();

// Сохраняем объект в хранилище
$slug = $storage->create($telegraphText);
echo "   Объект сохранен с slug: " . $slug . "\n";

// Читаем объект из хранилища
$loadedText = $storage->read($slug);
if ($loadedText) {
    echo "   Объект успешно загружен из хранилища\n";
}

// Получаем список всех объектов
$allTexts = $storage->list();
echo "   Всего объектов в хранилище: " . count($allTexts) . "\n\n";

// Используем Swig шаблон
echo "2. SWIG шаблон:\n";
$swig = new Swig('telegraph_text');
$swig->addVariablesToTemplate(['slug', 'text']);
echo $swig->render($telegraphText) . "\n";

// Используем Spl шаблон  
echo "3. SPL шаблон:\n";
$spl = new Spl('telegraph_text');
$spl->addVariablesToTemplate(['slug', 'title', 'text']);
echo $spl->render($telegraphText) . "\n";

echo "Программа успешно завершена!\n";

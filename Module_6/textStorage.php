<?php

$textStorage = [];

function add(string $title, string $text, array &$storage): void
{
    $storage[] = [
        'title' => $title,
        'text' => $text
    ];
}

// 3. Тестируем функцию add
add('Первый заголовок', 'Текст 1', $textStorage);
add('Второй заголовок', 'Текст 2', $textStorage);

// Выводим содержимое массива
echo "Содержимое массива после добавления двух текстов:\n";
print_r($textStorage);
echo "\n";

function remove(int $index, array &$storage): bool
{
    if (array_key_exists($index, $storage)) {
        unset($storage[$index]);
        $storage = array_values($storage);
        return true;
    }
    return false;
}

// 5. Тестируем функцию remove
echo "Результат удаления элемента с индексом 0: ";
var_dump(remove(0, $textStorage));

echo "Результат удаления элемента с индексом 5: ";
var_dump(remove(5, $textStorage));

// 6. Выводим содержимое массива после удаления
echo "Содержимое массива после удаления элементов:\n";
print_r($textStorage);
echo "\n";

function edit(int $index, string $title, string $text, array &$storage): bool
{
    if (array_key_exists($index, $storage)) { 
        $storage[$index]['title'] = $title;
        $storage[$index]['text'] = $text;
        return true;
    }
    return false;
}

// Тестируем функцию edit
echo "Результат редактирования элемента с индексом 0: ";
var_dump(edit(0, 'Обновленный заголовок', $textStorage[0]['text'], $textStorage));

echo "Результат редактирования элемента с индексом 5: ";
var_dump(edit(5, 'Новый заголовок', 'Новый текст', $textStorage));

// Выводим содержимое массива после редактирования
echo "Содержимое массива после редактирования:\n";
print_r($textStorage);

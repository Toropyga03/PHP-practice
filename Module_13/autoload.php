<?php

spl_autoload_register(function ($className) {
    // Преобразуем namespace в путь к файлу
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    
    // Определяем возможные пути для классов
    $paths = [
        'Entities' => 'Entities/',
        'Interfaces' => 'Interfaces/',
        'Core' => 'Core/'
    ];
    
    // Ищем класс в соответствующих папках
    foreach ($paths as $namespace => $directory) {
        if (strpos($className, $namespace) === 0) {
            $relativeClass = substr($className, strlen($namespace));
            $file = __DIR__ . '/' . $directory . $relativeClass . '.php';
            
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
    
    // Альтернативный поиск по структуре папок
    $directories = ['Entities', 'Interfaces', 'Core/Templates', 'Core'];
    
    foreach ($directories as $directory) {
        $file = __DIR__ . '/' . $directory . '/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

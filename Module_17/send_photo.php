<?php
session_start();

// Инициализация счетчика загрузок в сессии
if (!isset($_SESSION['upload_count'])) {
    $_SESSION['upload_count'] = 0;
}

$error = '';
$success = '';

// Обработка POST запроса
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Проверяем, не превышен ли лимит загрузок
        if ($_SESSION['upload_count'] >= 1) {
            throw new Exception('Вы уже загрузили файл в этой сессии. Лимит - 1 файл.');
        }

        // Проверяем, был ли загружен файл
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] == UPLOAD_ERR_NO_FILE) {
            throw new Exception('Файл не был загружен.');
        }

        $file = $_FILES['photo'];

        // Проверяем ошибки загрузки
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception('Размер файла превышает допустимый лимит.');
                default:
                    throw new Exception('Произошла ошибка при загрузке файла.');
            }
        }

        // Проверяем размер файла (2 МБ)
        if ($file['size'] > 2097152) {
            throw new Exception('Размер файла не должен превышать 2 МБ.');
        }

        // Проверяем тип файла
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $file['tmp_name']);
        finfo_close($file_info);

        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception('Разрешены только файлы форматов JPEG, JPG и PNG.');
        }

        // Проверяем расширение файла
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new Exception('Разрешены только файлы с расширениями JPG, JPEG и PNG.');
        }

        // Создаем папку images, если она не существует
        if (!is_dir('images')) {
            mkdir('images', 0755, true);
        }

        // Генерируем уникальное имя файла
        $new_filename = uniqid() . '.' . $file_extension;
        $destination = 'images/' . $new_filename;

        // Перемещаем загруженный файл
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Не удалось сохранить файл.');
        }

        // Увеличиваем счетчик загрузок
        $_SESSION['upload_count']++;
        
        // Перенаправляем на загруженный файл
        header('Location: ' . $destination);
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузка фотографии</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: #dc3545;
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info {
            color: #856404;
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Загрузка фотографии</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($_SESSION['upload_count'] >= 1): ?>
            <div class="info">
                Вы уже загрузили файл в этой сессии. Лимит - 1 файл.
            </div>
        <?php endif; ?>

        <div class="info">
            <strong>Требования к файлу:</strong>
            <ul>
                <li>Форматы: JPEG, JPG, PNG</li>
                <li>Максимальный размер: 2 МБ</li>
                <li>Лимит: 1 файл за сессию</li>
            </ul>
        </div>

        <form action="send_photo.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="photo">Выберите фотографию:</label>
                <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png" required>
            </div>
            
            <button type="submit">Загрузить фотографию</button>
        </form>
    </div>
</body>
</html>
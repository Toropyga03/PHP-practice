<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = $_POST['url'];
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $error = "Некорректный URL";
    } else {
        $processorUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/HTMLProcessor.php';
        
        $ch = curl_init($processorUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['url' => $url]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Парсер изображений</title>
    <style>
        .images { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
        .image-item { width: 200px; border: 1px solid #ddd; padding: 10px; }
        .image-item img { max-width: 100%; height: auto; }
    </style>
</head>
<body>
    <h2>Парсер изображений</h2>
    <form method="POST">
        <input type="text" name="url" placeholder="Введите URL страницы"
               value="<?= htmlspecialchars($_POST['url'] ?? '') ?>" size="60">
        <button type="submit">Найти изображения</button>
    </form>
    
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php elseif (isset($httpCode)): ?>
        <?php if ($httpCode == 200): ?>
            <?php 
            $result = json_decode($response, true);
            $images = $result['images'] ?? [];
            ?>
            <h3>Найдено изображений: <?= count($images) ?></h3>
            <div class="images">
                <?php foreach ($images as $img): ?>
                    <?php
                    $fullImageUrl = $img;
                    if (!preg_match('/^https?:\/\//', $img)) {
                        $base = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);
                        $fullImageUrl = rtrim($base, '/') . '/' . ltrim($img, '/');
                    }
                    ?>
                    <div class="image-item">
                        <img src="<?= htmlspecialchars($fullImageUrl) ?>" 
                             onerror="this.style.display='none'">
                        <div style="font-size: 12px; word-break: break-all; margin-top: 5px;">
                            <?= htmlspecialchars($img) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: red;">Картинки не найдены</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
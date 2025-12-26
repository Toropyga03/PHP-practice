<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$url = $_POST['url'] ?? '';

function getHTMLFromURL($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}

function parseImagesFromHTML($html) {
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $tags = $doc->getElementsByTagName('img');
    $images = [];
    foreach ($tags as $tag) {
        $images[] = $tag->getAttribute('src');
    }
    return $images;
}

$html = getHTMLFromURL($url);
$images = parseImagesFromHTML($html);

if (empty($images)) {
    http_response_code(404);
    echo json_encode(['images' => []]);
} else {
    http_response_code(200);
    echo json_encode(['images' => $images]);
}

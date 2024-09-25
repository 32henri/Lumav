<?php

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://www.1a.ee/c/arvutitehnika-burootarbed/sulearvutid-ja-tarvikud/sulearvutid/373');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);
curl_close($ch);

echo '<head>';
echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
echo '</head>';
echo '<body>';

echo '<h1>Web Scraping: Capturing Prices, Closest Titles/Labels, and Data Names using cURL</h1>';

// Capture images
preg_match_all(
   '!https?://[^\s"]+\.(?:png|jpg|jpeg|gif)!i',    
   $output, $data
);

echo "<h2>Images Found:</h2>";
foreach ($data[0] as $list) {
    echo "<img src='$list' style='max-width:200px;'/>";
}

// Capture prices and labels
preg_match_all(
   '/(<[^>]+>([^<]*?)(\d+[.,]?\d*)(?:\s*<span class="price-html-decimal">([^<]*)<\/span>)?\s*€([^<]*?)<\/[^>]+>)/',  
   $output, 
   $matches
);

echo "<h2>Prices and Closest Titles/Labels Found:</h2>";
if (!empty($matches[0])) {
    echo '<ul>';
    foreach ($matches[0] as $index => $full_match) {
        $context = strip_tags($matches[2][$index] . ' ' . $matches[5][$index]);  
        $price = $matches[3][$index]; 
        $decimal = !empty($matches[4][$index]) ? $matches[4][$index] : '00'; // Default decimal if not present
        
        echo "<li><strong>$context:</strong> $price.$decimal €</li>";
    }
    echo '</ul>';
} else {
    echo "No prices with € symbol and nearby labels found.";
}

// Capture data-name attributes
preg_match_all(
    '/data-name=["\']([^"\']+)["\']/',
    $output,
    $dataNames
);

echo "<h2>Data Names Found:</h2>";
if (!empty($dataNames[1])) {
    echo '<ul>';
    foreach ($dataNames[1] as $name) {
        echo "<li>$name</li>";
    }
    echo '</ul>';
} else {
    echo "No data-name attributes found.";
}

echo '</body>';
?>

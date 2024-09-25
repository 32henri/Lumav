<?php

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://www.decora.ee/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);
curl_close($ch);

echo '<head>';
echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
echo '</head>';
echo '<body>';

echo '<h1>Web Scraping: Capturing Prices and Closest Titles/Labels using cURL</h1>';

preg_match_all(
   '!https?://[^\s"]+\.(?:png|jpg|jpeg|gif)!i',    
   $output, $data
);

echo "<h2>Images Found:</h2>";
foreach ($data[0] as $list) {
    echo "<img src='$list' style='max-width:200px;'/>";
}

preg_match_all(
   '/(<[^>]+>([^<]*?)(\d+[.,]?\d*\s*€)([^<]*?)<\/[^>]+>)/',  
   $output, 
   $matches
);

echo "<h2>Prices and Closest Titles/Labels Found:</h2>";
if (!empty($matches[0])) {
    echo '<ul>';
    foreach ($matches[0] as $index => $full_match) {
        $context = strip_tags($matches[2][$index] . ' ' . $matches[4][$index]);  
        $price = $matches[3][$index]; 
        
        echo "<li><strong>$context:</strong> $price</li>";
    }
    echo '</ul>';
} else {
    echo "No prices with € symbol and nearby labels found.";
}

echo '</body>';
?>

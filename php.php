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

// Function to capture images
function captureImages($output) {
    $images = [];

    // Method 1: Using regex
    preg_match_all('!https?://[^\s"]+\.(?:png|jpg|jpeg|gif)!i', $output, $data);
    if (!empty($data[0])) {
        $images = $data[0];
    } else {
        // Method 2: Using DOMDocument if regex fails
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML($output);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//img/@src');
        foreach ($nodes as $node) {
            $images[] = $node->nodeValue;
        }
    }

    return $images;
}

// Function to capture prices and labels
function capturePricesAndLabels($output) {
    $prices = [];

    // Method 1: Using regex
    preg_match_all('/(<[^>]+>([^<]*?)(\d+[.,]?\d*)(?:\s*<span class="price-html-decimal">([^<]*)<\/span>)?\s*€([^<]*?)<\/[^>]+>/', $output, $matches);
    if (!empty($matches[0])) {
        foreach ($matches[0] as $index => $full_match) {
            $context = strip_tags($matches[2][$index] . ' ' . $matches[5][$index]);
            $price = $matches[3][$index];
            $decimal = !empty($matches[4][$index]) ? $matches[4][$index] : '00'; // Default decimal if not present
            $prices[] = ['price' => $price . '.' . $decimal, 'title' => $context];
        }
    } else {
        // Method 2: Using DOMDocument if regex fails
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML($output);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        $priceNodes = $xpath->query('//span[contains(@class, "price") or contains(@class, "amount")]/text()');
        $titleNodes = $xpath->query('//h1 | //span[contains(@class, "title")]');

        foreach ($priceNodes as $index => $priceNode) {
            $price = trim($priceNode->nodeValue);
            $title = $index < $titleNodes->length ? trim($titleNodes->item($index)->nodeValue) : 'No title';
            $prices[] = ['price' => $price, 'title' => $title];
        }
    }

    return $prices;
}

// Function to capture data-name attributes
function captureDataNames($output) {
    $dataNames = [];

    // Method 1: Using regex
    preg_match_all('/data-name=["\']([^"\']+)["\']/', $output, $matches);
    if (!empty($matches[1])) {
        $dataNames = $matches[1];
    } else {
        // Method 2: Using DOMDocument if regex fails
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML($output);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//*[@data-name]');
        foreach ($nodes as $node) {
            $dataNames[] = $node->getAttribute('data-name');
        }
    }

    return $dataNames;
}

// Extract images
$images = captureImages($output);
echo "<h2>Images Found:</h2>";
if (!empty($images)) {
    foreach ($images as $list) {
        echo "<img src='$list' style='max-width:200px;'/>";
    }
} else {
    echo "<p>No images found.</p>";
}

// Extract prices and labels
$pricesAndLabels = capturePricesAndLabels($output);
echo "<h2>Prices and Closest Titles/Labels Found:</h2>";
if (!empty($pricesAndLabels)) {
    echo '<ul>';
    foreach ($pricesAndLabels as $item) {
        echo "<li><strong>{$item['title']}:</strong> {$item['price']} €</li>";
    }
    echo '</ul>';
} else {
    echo "<p>No prices with € symbol and nearby labels found.</p>";
}

// Extract data-name attributes
$dataNames = captureDataNames($output);
echo "<h2>Data Names Found:</h2>";
if (!empty($dataNames)) {
    echo '<ul>';
    foreach ($dataNames as $name) {
        echo "<li>$name</li>";
    }
    echo '</ul>';
} else {
    echo "<p>No data-name attributes found.</p>";
}

echo '</body>';
?>

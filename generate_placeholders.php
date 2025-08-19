<?php
/**
 * Generate placeholder images for Ola Store Electronics
 * This script creates simple placeholder images for development purposes
 */

// Create placeholder images directory if it doesn't exist
$directories = [
    'assets/images/products',
    'assets/images/hero'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Generate hero images
$heroImages = [
    'iphone-hero.jpg' => [800, 600, 'iPhone 15 Pro'],
    'macbook-hero.jpg' => [800, 600, 'MacBook Air M2'],
    'watch-hero.jpg' => [800, 600, 'Apple Watch Series 9']
];

foreach ($heroImages as $filename => $config) {
    $width = $config[0];
    $height = $config[1];
    $title = $config[2];
    
    createPlaceholderImage($width, $height, $title, "assets/images/hero/$filename");
}

// Generate product images
$productImages = [
    'iphone-15-pro-1.jpg' => [400, 400, 'iPhone 15 Pro'],
    'iphone-15-pro-2.jpg' => [400, 400, 'iPhone 15 Pro Back'],
    'macbook-air-m2-1.jpg' => [400, 400, 'MacBook Air M2'],
    'macbook-air-m2-2.jpg' => [400, 400, 'MacBook Air M2 Side'],
    'samsung-galaxy-s24.jpg' => [400, 400, 'Samsung Galaxy S24'],
    'apple-watch-series-9.jpg' => [400, 400, 'Apple Watch Series 9'],
    'dell-xps-13.jpg' => [400, 400, 'Dell XPS 13'],
    'airpods-pro.jpg' => [400, 400, 'AirPods Pro'],
    'placeholder.jpg' => [400, 400, 'Product Placeholder']
];

foreach ($productImages as $filename => $config) {
    $width = $config[0];
    $height = $config[1];
    $title = $config[2];
    
    createPlaceholderImage($width, $height, $title, "assets/images/products/$filename");
}

echo "Placeholder images generated successfully!\n";

function createPlaceholderImage($width, $height, $title, $filepath) {
    // Create image
    $image = imagecreatetruecolor($width, $height);
    
    // Define colors
    $bgColor = imagecolorallocate($image, 245, 245, 247); // Light gray background
    $primaryColor = imagecolorallocate($image, 0, 122, 255); // Blue
    $textColor = imagecolorallocate($image, 58, 58, 60); // Dark gray text
    $accentColor = imagecolorallocate($image, 255, 149, 0); // Orange
    
    // Fill background
    imagefill($image, 0, 0, $bgColor);
    
    // Add gradient overlay
    for ($i = 0; $i < $height; $i++) {
        $alpha = 127 - (127 * $i / $height);
        $color = imagecolorallocatealpha($image, 255, 255, 255, $alpha);
        imageline($image, 0, $i, $width, $i, $color);
    }
    
    // Add border
    imagerectangle($image, 0, 0, $width - 1, $height - 1, $primaryColor);
    
    // Add title text
    $fontSize = min($width, $height) / 15;
    $fontFile = __DIR__ . '/arial.ttf'; // Use default font if available
    
    if (file_exists($fontFile)) {
        // Calculate text position
        $bbox = imagettfbbox($fontSize, 0, $fontFile, $title);
        $textWidth = $bbox[4] - $bbox[0];
        $textHeight = $bbox[1] - $bbox[5];
        $x = ($width - $textWidth) / 2;
        $y = ($height + $textHeight) / 2;
        
        // Add text shadow
        imagettftext($image, $fontSize, 0, $x + 2, $y + 2, $textColor, $fontFile, $title);
        // Add main text
        imagettftext($image, $fontSize, 0, $x, $y, $primaryColor, $fontFile, $title);
    } else {
        // Fallback to basic text
        $x = ($width - strlen($title) * 10) / 2;
        $y = $height / 2;
        imagestring($image, 5, $x, $y, $title, $primaryColor);
    }
    
    // Add decorative elements
    $centerX = $width / 2;
    $centerY = $height / 2;
    
    // Add circle
    imageellipse($image, $centerX, $centerY - 30, 60, 60, $accentColor);
    imagefill($image, $centerX, $centerY - 30, $accentColor);
    
    // Add lines
    imageline($image, $centerX - 40, $centerY + 20, $centerX + 40, $centerY + 20, $primaryColor);
    imageline($image, $centerX - 30, $centerY + 30, $centerX + 30, $centerY + 30, $primaryColor);
    
    // Save image
    imagejpeg($image, $filepath, 90);
    imagedestroy($image);
    
    echo "Generated: $filepath\n";
}
?>
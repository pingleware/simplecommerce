<?php
require 'settings.php';


// Fetch product URLs
$categories = $db->query("SELECT id FROM categories");

$baseUrl = $settings['siteurl'];
$sitemapPath = __DIR__ . "/sitemap.xml";
$robotsPath = __DIR__ . "/robots.txt";

$jobsearch_url = $settings['jobsearch_url'];
$support_url = $settings['support_url'];

// Generate sitemap content
$sitemapContent = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
$sitemapContent .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
$sitemapContent .= "
    <url>
        <loc>$baseUrl/</loc>
        <lastmod>" . date('Y-m-d') . "</lastmod>
        <priority>1.0</priority>
    </url>
";

while ($row = $categories->fetchArray(SQLITE3_ASSOC)) {
    $sitemapContent .= "
    <url>
        <loc>$baseUrl/product.php?id={$row['id']}</loc>
        <lastmod>" . date('Y-m-d') . "</lastmod>
        <priority>0.7</priority>
    </url>
    ";
}

$sitemapContent .= "
    <!-- Static Pages -->
    <url>
        <loc>$baseUrl/about.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>$jobsearch_url</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>$support_url</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>$baseUrl/stores.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>$baseUrl/shipping.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>$baseUrl/payment.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>$baseUrl/giftcard.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>$baseUrl/return.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>$baseUrl/help.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <priority>0.6</priority>
    </url>
</urlset>
";

// Save sitemap.xml
file_put_contents($sitemapPath, $sitemapContent);

// Generate robots.txt content
$robotsContent = "User-agent: *" . PHP_EOL;
$robotsContent .= "Disallow: /admin.php" . PHP_EOL;
$robotsContent .= "Disallow: /cart.php" . PHP_EOL;
$robotsContent .= "Sitemap: $baseUrl/sitemap.xml" . PHP_EOL;

// Save robots.txt
file_put_contents($robotsPath, $robotsContent);


echo "Sitemap and robots.txt generated successfully!";
?>
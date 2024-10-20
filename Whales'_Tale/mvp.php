<?php
// 设置数据源路径，可以是数据库查询结果，也可以是从文件获取
$dataFile = 'whale_dataset.json';
$data = json_decode(file_get_contents($dataFile), true);

// 获取过滤条件和年份区间
$startYear = isset($_GET['startYear']) ? intval($_GET['startYear']) : 1980;
$endYear = isset($_GET['endYear']) ? intval($_GET['endYear']) : 2020;
$selectedSpecies = isset($_GET['species']) ? $_GET['species'] : [];

// 从数据中提取所有鲸鱼种类
$whaleSpecies = array_unique(array_column($data, 'Southern Right Whale'));

// 过滤数据
$filteredData = array_filter($data, function ($item) use ($startYear, $endYear, $selectedSpecies) {
    $year = intval($item['1987']);
    $commonName = $item['Southern Right Whale'];
    return $year >= $startYear && $year <= $endYear && (empty($selectedSpecies) || in_array($commonName, $selectedSpecies));
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map of Whale Strandings and Sightings</title>
    <link rel="stylesheet" href="DECO1800.css/base.css">
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Merienda&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Moul&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .map-container {
            height: 500px; 
            width: 100%;
            margin: 0 auto;
            max-width: 1200px;
        }
        #map {
            height: 100%;
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li class="edu_page"><a href="edu_page.php">Whales Knowledge Base</a></li>
                <li class="mvp"><a href="map.php">Interactive Map</a></li>
            </ul>
            <h1 class="whalestale-title">WhalesTale</h1>
        </nav>
    </header>

    <main>
        <!-- 表单，用于接收用户选择的过滤条件 -->
        <form method="GET" action="map.php">
            <section class="controls">
                <label for="timeSlider1">Start Year:</label>
                <input type="number" name="startYear" id="timeSlider1" min="1980" max="2020" value="<?php echo $startYear; ?>">
                
                <label for="timeSlider2">End Year:</label>
                <input type="number" name="endYear" id="timeSlider2" min="1980" max="2020" value="<?php echo $endYear; ?>">
            </section>

            <section class="filter-section">
                <h2>Filter by Species</h2>
                <div class="filter-options">
                    <div id="filter-checkboxes" class="filter-checkboxes">
                        <?php
                        // 使用PHP动态生成种类过滤选项
                        foreach ($whaleSpecies as $species) {
                            // 检查该种类是否被选中
                            $checked = in_array($species, $selectedSpecies) ? 'checked' : '';
                            echo '<label>';
                            echo '<input type="checkbox" name="species[]" value="' . htmlspecialchars($species) . '" ' . $checked . '>';
                            echo htmlspecialchars($species);
                            echo '</label>';
                        }
                        ?>
                    </div>
                </div>
                <button type="submit">Apply Filters</button>
            </section>
        </form>

        <section class="map-section">
            <h1>Map of Whale Strandings and Sightings</h1>
            <p>Use the map below to explore whale strandings and sightings in Australia.</p>
            <div class="map-container">
                <div id="map"></div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-links">
            <a href="#">Support</a>
            <a href="#">Contact Us</a>
            <a href="#">Follow Us</a>
        </div>
    </footer>

    <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoibHlsNTI1IiwiYSI6ImNtMHg3dDl5dzA5bDcycnB5ZzluZzRueXUifQ.P5n5ikx90MtIu4-NzSEmgQ';

        var map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11', 
            center: [133.7751, -25.2744], 
            zoom: 4 
        });

        var markers = <?php echo json_encode($filteredData); ?>;

        // 遍历PHP生成的标记数据，并在地图上添加标记
        markers.forEach(item => {
            var commonName = item['Southern Right Whale'];
            var latitude = parseFloat(item['-32.2']);
            var longitude = parseFloat(item['115.7']);
            var year = parseInt(item['1987']);

            if (latitude && longitude) {
                var marker = new mapboxgl.Marker()
                    .setLngLat([longitude, latitude])
                    .setPopup(new mapboxgl.Popup().setHTML(`<h3>${commonName}</h3><p>Year: ${year}</p>`))
                    .addTo(map);
            }
        });
    </script>
</body>
</html>

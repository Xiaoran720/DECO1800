<?php
// Set the data source path, which can be a database query result or obtained from a file
$dataFile = 'whale_dataset.json';
$data = json_decode(file_get_contents($dataFile), true);

// Get filter conditions and year ranges
$startYear = isset($_GET['startYear']) ? intval($_GET['startYear']) : 1980;
$endYear = isset($_GET['endYear']) ? intval($_GET['endYear']) : 2020;
$selectedSpecies = isset($_GET['species']) ? $_GET['species'] : [];

// Extract all whale species from the data
$whaleSpecies = array_unique(array_column($data, 'Southern Right Whale'));

// Filtering Data
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
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        header{
            background-image: linear-gradient(rgba(107, 171, 245, 0.8), rgba(81, 129, 185, 0.8), rgba(62, 100, 143, 0.8));
            display: flex;
            align-items: center;
            padding:  0;
            height: 90px;
        }
        #web_name{
            color: white;
            font-family: 'Merienda', cursive; 
            font-size: 40px;
            font-weight: 700;
            text-align: left;
            margin-left: 20px;
            line-height: normal;
            flex: 1;
        }
        header nav li.mvp {
            color: black;
            background-color: #f1f1f1;
            padding: 50px 0px;
            margin: 0;
        }
        header nav li.mvp a {
            color: black;
        }
        header nav {
            display: flex;
            justify-content: center;
            text-align: center;
            margin-left: 90px;
        }
        header nav ul {
            display: flex; 
        }
        header nav li {
            display: flex;
            font-size: 20px;
            align-items: center;
        }
        header nav li a {
            padding: 0 10px;
        }
        header nav li a:hover {
            color: black;
            text-decoration: underline;
        }
        
        header .login {
            color: #fff;
            text-decoration: none;
            margin-right: 20px;
            flex: 1;
            text-align: right;
        }
        main {
            padding: 20px;
        }
        .map-section {
            margin-top: 30px;
            text-align: center;
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
        .filter-section {
            margin-top: 30px;
        }
        .filter-options {
            display: flex;
            justify-content: space-around;
        }
        footer{
            background-image: linear-gradient(rgba(107, 171, 245, 0.8), rgba(81, 129, 185, 0.8), rgba(62, 100, 143, 0.8));
            display: flex;
            align-items: center;
            padding:  10px;
            margin: 0;
        }
        .footer-links {
            justify-content: center;
        }
        .footer-links a {
            margin-right: 20px;
        }
        .footer-links a:last-child {
            margin-right: 0;
        }
        footer div a:hover {
            color: black;
            text-decoration: underline;
        }
        .controls {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .filter-checkboxes label {
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li class="edu_page"><a href="edu_page.html">Whales Knowledge Base</a></li>
                <li class="mvp"><a href="mvp.html">Interactive Map</a></li>
                <li class="game"><a href="game.html">Game To Know</a></li>
            </ul>
            <h1 class="whalestale-title">WhalesTale</h1>
        </nav>
    </header>

    <main>
        <!-- 表单，用于接收用户选择的过滤条件 --> 
<form method="GET" action="map.php">
    <!-- 时间选择部分 -->
    <section class="controls">
        <label for="timeSlider1">Start Year:</label>
        <input type="number" name="startYear" id="timeSlider1" min="1980" max="2020" value="<?php echo $startYear; ?>">

        <label for="timeSlider2">End Year:</label>
        <input type="number" name="endYear" id="timeSlider2" min="1980" max="2020" value="<?php echo $endYear; ?>">
    </section>

    <!-- 物种过滤部分 -->
    <section class="filter-section">
        <h2>Filter by Species</h2>
        <div class="filter-options">
            <div id="filter-checkboxes" class="filter-checkboxes">
                <?php
                // Dynamically generate species filter options using PHP
                foreach ($whaleSpecies as $species) {
                    // Check if the species is selected
                    $checked = in_array($species, $selectedSpecies) ? 'checked' : '';
                    echo '<label>';
                    echo '<input type="checkbox" name="species[]" value="' . htmlspecialchars($species) . '" ' . $checked . '>';
                    echo htmlspecialchars($species);
                    echo '</label>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- 时间轴控制部分 -->
    <section class="controls">
        <label for="timeSlider1">Start Year:</label>
        <input type="range" id="timeSlider1" name="startYear" min="1980" max="2020" value="<?php echo $startYear; ?>">
        <span id="startYear"><?php echo $startYear; ?></span>

        <label for="timeSlider2">End Year:</label>
        <input type="range" id="timeSlider2" name="endYear" min="1980" max="2020" value="<?php echo $endYear; ?>">
        <span id="endYear"><?php echo $endYear; ?></span>
    </section>

    <!-- 提交按钮 -->
    <section class="submit-section">
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

        // 初始化 Mapbox 地图
        var map = new mapboxgl.Map({
            container: 'map', // 绑定地图到id为map的HTML元素
            style: 'mapbox://styles/mapbox/streets-v11', // 地图样式
            center: [133.7751, -25.2744], // 澳大利亚的中心点
            zoom: 4 // 设置缩放级别
        });

        var markers = {}; // 用于存储标记信息
        var whaleSpecies = new Set(); // 用于存储独特的鲸鱼常见名称

        // 当前时间范围
        var startYear = 1980;
        var endYear = 2020;

        // 加载数据集并解析
        fetch('Whale_dataset.json')
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const commonName = item['Southern Right Whale']; // 提取常见名称
                    const uuid = item['6b63056a-825d-4c7c-a9fc-efd128cd2e73']; // 提取 UUID
                    const latitude = parseFloat(item['-32.2']); // 纬度
                    const longitude = parseFloat(item['115.7']); // 经度
                    const year = parseInt(item['1987']); // 提取年份

                    // 确保数据完整
                    if (uuid && commonName && latitude && longitude && year) {
                        whaleSpecies.add(commonName); // 将常见名称添加到 Set 中

                        // 创建地图标记
                        var marker = new mapboxgl.Marker()
                            .setLngLat([longitude, latitude])
                            .setPopup(new mapboxgl.Popup().setHTML(`<h3>${commonName}</h3><p>Year: ${year}</p>`)) // 弹出窗口显示常见名称
                            .addTo(map);

                        markers[uuid] = {
                            marker: marker,
                            commonName: commonName,
                            year: year
                        };
                    }
                });

                // 动态生成过滤器复选框
                generateFilterCheckboxes();
                updateMarkers(); // 初始更新标记
            });

        // 动态生成过滤器复选框
        function generateFilterCheckboxes() {
            const filterContainer = document.getElementById('filter-checkboxes');
            whaleSpecies.forEach(species => {
                const label = document.createElement('label');
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = true; // 默认所有复选框都选中
                checkbox.value = species;
                checkbox.addEventListener('change', updateMarkers); // 当用户更改复选框状态时更新标记

                label.appendChild(checkbox);
                label.appendChild(document.createTextNode(species));
                filterContainer.appendChild(label);
            });
        }

        // 更新标记显示，同时应用时间和物种过滤器
        function updateMarkers() {
            const selectedSpecies = new Set(
                Array.from(document.querySelectorAll('#filter-checkboxes input:checked')).map(cb => cb.value)
            );

            Object.keys(markers).forEach(uuid => {
                const markerObj = markers[uuid];
                const markerSpecies = markerObj.commonName;
                const markerYear = markerObj.year;

                // 只显示同时满足物种筛选和时间筛选的标记
                if (selectedSpecies.has(markerSpecies) && markerYear >= startYear && markerYear <= endYear) {
                    markerObj.marker.addTo(map);
                } else {
                    markerObj.marker.remove();
                }
            });
        }

        // 获取时间滑块元素并添加事件监听器
        const timeSlider1 = document.getElementById('timeSlider1');
        const timeSlider2 = document.getElementById('timeSlider2');
        const startYearDisplay = document.getElementById('startYear');
        const endYearDisplay = document.getElementById('endYear');

        // 监听时间滑块的变化
        timeSlider1.addEventListener('input', function () {
            startYear = parseInt(this.value);
            startYearDisplay.textContent = startYear;
            updateMarkers(); // 更新标记显示
        });

        timeSlider2.addEventListener('input', function () {
            endYear = parseInt(this.value);
            endYearDisplay.textContent = endYear;
            updateMarkers(); // 更新标记显示
        });

    </script>
</body>
</html>

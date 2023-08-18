<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        #map {
            height: 500px;
        }
    </style>
</head>

<body>
    <div id="map"></div>

    <script>
        var map = L.map('map').setView([3.5970225213458646, 98.67360662526694], 13);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // const dataFileGeo = JSON.parse('<?= $jsonFileName ?>');
        // console.log(dataFileGeo);

        // async function getDataGeoJson(url) {
        //     const response = await fetch(url)
        //     const result = await response.json()
        //     console.log(result)
        //     L.geoJSON(result).addTo(map);
        // }

        // dataFileGeo.forEach(url => {
        //     getDataGeoJson("/geojson/" + url)
        // });
    </script>
</body>

</html>
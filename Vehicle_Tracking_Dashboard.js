// =====================
// Vehicle Tracking Dashboard JS
// =====================

// 1️⃣ Initialize map
var map = L.map('map').setView([22.5726, 88.3639], 13);

// 2️⃣ Load map tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

// 3️⃣ Car Icon with 🚗 emoji
var carIcon = L.divIcon({
    className: "car-icon",
    html: `
    <svg width="50" height="50" viewBox="0 0 64 64">
        <circle cx="32" cy="32" r="20" fill="#e74c3c" />
        <text x="32" y="36" font-size="36" text-anchor="middle" alignment-baseline="middle">🚗</text>
    </svg>
    `,
    iconSize: [50, 50],
    iconAnchor: [25, 25]
});

// 4️⃣ Marker for the car
var marker = L.marker([22.5726, 88.3639], { icon: carIcon }).addTo(map);

// 5️⃣ Blue route line
var routeLine = L.polyline([], { color: 'blue', weight: 5 }).addTo(map);
var routePoints = [];

// 6️⃣ Last GPS position
var lastLat = null;
var lastLng = null;

// 7️⃣ Calculate bearing (rotation)
function getBearing(lat1, lon1, lat2, lon2) {
    var dLon = (lon2 - lon1) * Math.PI / 180;
    var y = Math.sin(dLon) * Math.cos(lat2 * Math.PI / 180);
    var x = Math.cos(lat1 * Math.PI / 180) * Math.sin(lat2 * Math.PI / 180) -
            Math.sin(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.cos(dLon);
    return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
}

// 8️⃣ Smooth movement animation & map follow
function animateCar(start, end, duration = 1000) {
    var startTime = performance.now();

    function animate(time) {
        var progress = (time - startTime) / duration;
        if (progress > 1) progress = 1;

        var lat = start[0] + (end[0] - start[0]) * progress;
        var lng = start[1] + (end[1] - start[1]) * progress;

        // Move marker
        marker.setLatLng([lat, lng]);

        // Map follows marker
        map.setView([lat, lng]);

        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    }

    requestAnimationFrame(animate);
}

// 9️⃣ Update dashboard info
function updateDashboard(data) {
    if (!data) return;

    var lat = parseFloat(data.lat);
    var lng = parseFloat(data.lng);

    // Animate car & rotate
    if (lastLat !== null && lastLng !== null) {
        animateCar([lastLat, lastLng], [lat, lng], 1200);
        var angle = getBearing(lastLat, lastLng, lat, lng);
        marker._icon.style.transform = "rotate(" + angle + "deg)";
    } else {
        marker.setLatLng([lat, lng]);
    }

    lastLat = lat;
    lastLng = lng;

    // Update route line
    routePoints.push([lat, lng]);
    routeLine.setLatLngs(routePoints);

    // ===== Update vehicle info =====
    document.getElementById("vehicle_id").innerText = data.vehicle_id;
    document.getElementById("location").innerText = lat.toFixed(5) + " , " + lng.toFixed(5);
    document.getElementById("speed_info").innerText = data.speed;
    document.getElementById("speed").innerText = data.speed + " km/h";
    document.getElementById("fuel").innerText = data.fuel_level;
    document.getElementById("battery").innerText = data.battery_voltage;
    document.getElementById("ignition").innerText = data.ignition == 1 ? "ON" : "OFF";
    document.getElementById("engine").innerText = data.engine_status == 1 ? "ON" : "OFF";
    document.getElementById("seatbelt").innerText = data.seatbelt == 1 ? "ON" : "OFF";
    document.getElementById("timestamp").innerText = data.timestamp;

    document.getElementById("odometer").innerText = data.odometer;
    document.getElementById("segment_distance").innerText = data.segment_distance;
    document.getElementById("history").innerText = data.timestamp + " - Speed " + data.speed + " km/h";

    // Alerts
    var alertsDiv = document.getElementById("alerts");
    alertsDiv.innerHTML = "<h4>Alerts</h4>";
    if (data.speed > 90) alertsDiv.innerHTML += `<p class="alert red">Overspeed Alert</p>`;
    if (data.battery_voltage < 12) alertsDiv.innerHTML += `<p class="alert yellow">Low Battery</p>`;
    if (data.ignition == 0) alertsDiv.innerHTML += `<p class="alert green">Ignition OFF</p>`;
}

// 10️⃣ Fetch GPS from PHP
function fetchData() {
    fetch('Vehicle_Tracking_Dashboard_data.php?car_id=' + car_db_id)
        .then(response => response.json())
        .then(data => updateDashboard(data))
        .catch(err => console.error(err));
}


// Initial load & repeat
fetchData();
setInterval(fetchData, 5000);


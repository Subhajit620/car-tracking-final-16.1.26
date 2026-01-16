// Initialize Leaflet map
var map = L.map('map').setView([22.5726, 88.3639], 13); // default coords
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

// Marker for vehicle
var marker = L.marker([22.5726, 88.3639]).addTo(map).bindPopup("Vehicle");

// Function to update dashboard
function updateDashboard(data) {
    if (!data || Object.keys(data).length === 0) return;

    // Update map marker
    var lat = parseFloat(data.lat);
    var lng = parseFloat(data.lng);
    marker.setLatLng([lat, lng]).update();
    map.setView([lat, lng], 13);

    // Update vehicle info
    document.getElementById("vehicle_id").innerText = data.vehicle_id;
    document.getElementById("location").innerText = data.lat + " , " + data.lng;
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

    // Update alerts
    var alertsDiv = document.getElementById("alerts");
    alertsDiv.innerHTML = "<h4>Alerts</h4>"; // clear previous alerts
    if (data.speed > 90) alertsDiv.innerHTML += `<p class="alert red">Overspeed Alert</p>`;
    if (data.battery_voltage < 12) alertsDiv.innerHTML += `<p class="alert yellow">Low Battery</p>`;
    if (data.ignition == 0) alertsDiv.innerHTML += `<p class="alert green">Ignition OFF</p>`;
}

// Fetch data every 5 seconds
function fetchData() {
    fetch('Vehicle_Tracking_Dashboard_data.php')
        .then(response => response.json())
        .then(data => updateDashboard(data))
        .catch(err => console.error(err));
}

// Initial load
fetchData();
// Update every 5 seconds
setInterval(fetchData, 5000);


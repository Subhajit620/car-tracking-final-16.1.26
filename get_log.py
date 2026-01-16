import mysql.connector
import random
import time
from datetime import datetime
from math import radians, sin, cos, sqrt, atan2

# ----------------- DATABASE CONFIG -----------------
db = mysql.connector.connect(
    host="localhost",
    user="gpsuser",
    password="Saha@2003",
    database="car"
)

cursor = db.cursor(dictionary=True)

# ----------------- VEHICLE CONFIG -----------------
CAR_ID = 1           # Must exist in 'cars' table
VEHICLE_ID = "CAR001"

# ----------------- HELPER FUNCTIONS -----------------
def random_lat():
    return round(22.50 + random.random() * 0.1, 6)

def random_lng():
    return round(88.36 + random.random() * 0.1, 6)

def random_speed():
    return round(random.uniform(10, 90), 2)

def random_seatbelt():
    return random.choice([0, 1])

def random_fuel():
    return random.randint(10, 100)

def random_engine():
    return random.choice([0, 1])

def random_ignition():
    return random.choice([0, 1])

def random_battery():
    return round(random.uniform(11.0, 13.5), 2)

def calculate_distance(lat1, lon1, lat2, lon2):
    """Calculate distance in km using Haversine formula."""
    R = 6371
    dlat = radians(lat2 - lat1)
    dlon = radians(lon2 - lon1)
    a = sin(dlat/2)**2 + cos(radians(lat1)) * cos(radians(lat2)) * sin(dlon/2)**2
    c = 2 * atan2(sqrt(a), sqrt(1-a))
    return R * c

# ----------------- INITIAL VALUES -----------------
last_lat = None
last_lng = None
odometer = 0.0

print("🚗 Live GPS Logger Started... Press Ctrl+C to stop")

# ----------------- MAIN LOOP -----------------
try:
    while True:
        lat = random_lat()
        lng = random_lng()
        speed = random_speed()
        seatbelt = random_seatbelt()
        fuel = random_fuel()
        engine = random_engine()
        ignition = random_ignition()
        battery = random_battery()
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

        # Calculate segment distance
        if last_lat is not None and last_lng is not None:
            segment_distance = calculate_distance(last_lat, last_lng, lat, lng)
        else:
            segment_distance = 0.0

        odometer += segment_distance
        last_lat, last_lng = lat, lng

        # Insert into gps_logs table
        sql = """
        INSERT INTO gps_logs 
        (car_id, vehicle_id, lat, lng, speed, seatbelt, fuel_level, engine_status, ignition,
         battery_voltage, segment_distance, odometer, timestamp)
        VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
        """
        cursor.execute(sql, (
            CAR_ID, VEHICLE_ID, lat, lng, speed, seatbelt, fuel, engine, ignition,
            battery, segment_distance, odometer, timestamp
        ))
        db.commit()

        # Print log for debugging
        print(f"[{timestamp}] Vehicle {VEHICLE_ID} | Lat: {lat} | Lng: {lng} | Speed: {speed} km/h | "
              f"Seatbelt: {'ON' if seatbelt else 'OFF'} | Fuel: {fuel}% | Engine: {'ON' if engine else 'OFF'} | "
              f"Ignition: {'ON' if ignition else 'OFF'} | Battery: {battery} V | "
              f"Segment: {round(segment_distance,3)} km | Odometer: {round(odometer,3)} km")

        time.sleep(5)  # insert every 15 seconds

except KeyboardInterrupt:
    print("\n🛑 Logger stopped by user")

finally:
    cursor.close()
    db.close()


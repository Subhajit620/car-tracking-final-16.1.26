<?php
// ================= DATABASE CONNECTION =================
$conn = new mysqli("localhost", "root", "", "car");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ================= FILTERS =================
$where = "1";
$params = [];
$types = "";

if (!empty($_GET['search'])) {
    $where .= " AND vehicle_id LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
    $types .= "s";
}

if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $where .= " AND timestamp BETWEEN ? AND ?";
    $params[] = $_GET['from'] . " 00:00:00";
    $params[] = $_GET['to'] . " 23:59:59";
    $types .= "ss";
}

$sql = "SELECT * FROM gps_logs WHERE $where ORDER BY timestamp DESC LIMIT 100";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vehicle Log Data</title>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-[#0b1120] text-[#f1f5f9] min-h-screen p-8">

<!-- ================= BACK + LOGOUT BUTTONS ================= -->
<div class="flex justify-between mb-6">
  <!-- BACK BUTTON -->
  <a href="Vehicle_Tracking_Dashboard.html"
     class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 rounded-lg hover:bg-blue-500">
    <i data-lucide="arrow-left"></i>
    Back
  </a>

  <!-- LOGOUT BUTTON -->
  <a href="logout.php"
     class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 rounded-lg hover:bg-red-500">
    <i data-lucide="log-out"></i>
    Logout
  </a>
</div>

<h1 class="text-2xl font-bold mb-6">Vehicle Log Data</h1>

<!-- ================= SEARCH FORM ================= -->
<form method="GET" class="flex flex-wrap gap-4 items-end mb-6">
  <input type="text" name="search" placeholder="Search Vehicle"
    class="px-4 py-2 w-72 rounded-lg bg-slate-800 border border-slate-700 text-white"
    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

  <div>
    <label class="block text-sm text-slate-400 mb-1">From Date</label>
    <input type="date" name="from"
      class="px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white"
      value="<?= $_GET['from'] ?? '' ?>">
  </div>

  <div>
    <label class="block text-sm text-slate-400 mb-1">To Date</label>
    <input type="date" name="to"
      class="px-4 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white"
      value="<?= $_GET['to'] ?? '' ?>">
  </div>

  <button type="submit" class="px-5 py-2 bg-green-600 rounded-lg hover:bg-green-500">
    Search
  </button>

  <a href="logs_data.php" class="px-5 py-2 bg-slate-700 rounded-lg hover:bg-slate-600">
    Reset
  </a>
</form>

<!-- ================= TABLE ================= -->
<div class="bg-slate-800/20 border border-slate-800 rounded-xl overflow-x-auto">
  <table class="w-full text-left">
    <thead class="bg-slate-800/50">
      <tr>
        <th class="p-4">Date</th>
        <th class="p-4">Vehicle</th>
        <th class="p-4">Speed</th>
        <th class="p-4">Fuel</th>
        <th class="p-4">Odometer</th>
        <th class="p-4">Seatbelt</th>
        <th class="p-4">Engine</th>
        <th class="p-4">Ignition</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr class="border-b border-slate-700 hover:bg-slate-700/20">
            <td class="p-4"><?= $row['timestamp'] ?></td>
            <td class="p-4"><?= htmlspecialchars($row['vehicle_id']) ?></td>
            <td class="p-4"><?= $row['speed'] ?? '-' ?></td>
            <td class="p-4"><?= $row['fuel_level'] ?? '-' ?></td>
            <td class="p-4"><?= $row['odometer'] ?? '-' ?></td>
            <td class="p-4"><?= $row['seatbelt'] ? 'On' : 'Off' ?></td>
            <td class="p-4"><?= $row['engine_status'] ? 'On' : 'Off' ?></td>
            <td class="p-4"><?= $row['ignition'] ? 'On' : 'Off' ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" class="text-center p-4 text-slate-400">No records found</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
  lucide.createIcons();
</script>
</body>
</html>


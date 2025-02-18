
<?php
require_once __DIR__ . '/backend/config/database.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get device ID from URL
$deviceId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$deviceId) {
    header('Location: index.php');
    exit;
}

// Fetch device details
function getDevice($db, $id) {
    try {
        $sql = "SELECT 
                d.*,
                p.param_name,
                p.param_value,
                p.param_type 
                FROM devices d 
                LEFT JOIN parameters p ON d.id = p.device_id 
                WHERE d.id = :id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $device = null;
        $parameters = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!$device) {
                $device = [
                    'id' => $row['id'],
                    'serialNumber' => $row['serial_number'],
                    'manufacturer' => $row['manufacturer'],
                    'model' => $row['model_name'],
                    'status' => $row['status'],
                    'lastContact' => $row['last_contact'],
                    'ipAddress' => $row['ip_address'],
                    'softwareVersion' => $row['software_version'],
                    'hardwareVersion' => $row['hardware_version']
                ];
            }
            
            if ($row['param_name']) {
                $parameters[] = [
                    'name' => $row['param_name'],
                    'value' => $row['param_value'],
                    'type' => $row['param_type']
                ];
            }
        }
        
        if ($device) {
            $device['parameters'] = $parameters;
        }
        
        return $device;
    } catch (PDOException $e) {
        error_log("Database error in getDevice: " . $e->getMessage());
        return null;
    }
}

$device = getDevice($db, $deviceId);

if (!$device) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Details - ACS Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Auto-refresh the page every 5 seconds
        setTimeout(function() {
            window.location.reload();
        }, 5000);
    </script>
</head>
<body class="min-h-screen bg-gray-50">
    <div class="p-6">
        <div class="max-w-7xl mx-auto space-y-8">
            <div class="flex items-center justify-between">
                <div>
                    <a href="index.php" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
                    <h1 class="text-3xl font-semibold tracking-tight mt-2">
                        Device Details
                    </h1>
                </div>
                <span class="px-3 py-1 text-sm rounded-full <?php echo $device['status'] === 'online' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo ucfirst($device['status']); ?>
                </span>
            </div>

            <!-- Device Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Device Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Manufacturer:</span>
                            <?php echo htmlspecialchars($device['manufacturer']); ?>
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Model:</span>
                            <?php echo htmlspecialchars($device['model']); ?>
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Serial Number:</span>
                            <?php echo htmlspecialchars($device['serialNumber']); ?>
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">IP Address:</span>
                            <?php echo htmlspecialchars($device['ipAddress']); ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Software Version:</span>
                            <?php echo htmlspecialchars($device['softwareVersion'] ?? 'N/A'); ?>
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Hardware Version:</span>
                            <?php echo htmlspecialchars($device['hardwareVersion'] ?? 'N/A'); ?>
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Last Contact:</span>
                            <?php echo date('Y-m-d H:i:s', strtotime($device['lastContact'])); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Device Parameters -->
            <?php if (!empty($device['parameters'])): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Device Parameters</h2>
                <div class="grid grid-cols-1 gap-2">
                    <?php foreach ($device['parameters'] as $param): ?>
                        <div class="p-3 bg-gray-50 rounded">
                            <p class="text-sm font-medium"><?php echo htmlspecialchars($param['name']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($param['value']); ?></p>
                            <p class="text-xs text-gray-500">Type: <?php echo htmlspecialchars($param['type']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

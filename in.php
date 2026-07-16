<?php
$servername = "sql313.infinityfree.com";
$username = "if0_42403737";
$password = "baZvay38LAp5LMF";
$dbname = "if0_42403737_user";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$submitted_name = "";
$submitted_age = "";

$ajax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

if (isset($_GET['toggle_id'])) {
    $toggle_id = intval($_GET['toggle_id']);
    if ($toggle_id > 0) {
        $toggle = $conn->prepare("UPDATE user SET status = 1 - status WHERE id = ?");
        $toggle->bind_param("i", $toggle_id);
        if ($toggle->execute()) {
            $message = "Status updated successfully.";
        } else {
            $message = "Error updating status: " . $toggle->error;
        }
        $toggle->close();
    }
}

if (isset($_GET['name']) && isset($_GET['age'])) {
    $submitted_name = trim($_GET['name']);
    $submitted_age = trim($_GET['age']);

    if ($submitted_name !== '' && $submitted_age !== '') {
        $submitted_name = ucwords(strtolower($submitted_name));
        $submitted_age = intval($submitted_age);

        $check = $conn->prepare("SELECT id FROM user WHERE name = ? AND age = ?");
        $check->bind_param("si", $submitted_name, $submitted_age);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "This user already exists.";
        } else {
            $insert = $conn->prepare("INSERT INTO user (name, age, status) VALUES (?, ?, 0)");
            $insert->bind_param("si", $submitted_name, $submitted_age);

            if ($insert->execute()) {
                $message = "New record created successfully.";
            } else {
                $message = "Error: " . $insert->error;
            }

            $insert->close();
        }

        $check->close();
        $submitted_name = "";
        $submitted_age = "";
    } else {
        $message = "Please enter both name and age.";
        $submitted_name = "";
        $submitted_age = "";
    }
}

$result = $conn->query("SELECT id, name, age, status FROM user ORDER BY id ASC");

if ($ajax) {
    $rows = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'age' => $row['age'],
                'status' => $row['status'],
            ];
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['message' => $message, 'rows' => $rows]);
    $conn->close();
    exit;
}

$result = $conn->query("SELECT id, name, age, status FROM user ORDER BY id ASC");
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>User Database</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; max-width: 640px; }
    th, td { border: 1px solid #999; padding: 8px; text-align: left; }
    th { background: #f0f0f0; }
    #statusMessage { margin: 12px 0; padding: 10px; background: #eef; border: 1px solid #99c; }
    .status-active { color: green; font-weight: bold; }
    .status-inactive { color: red; font-weight: bold; }
    .action-form { margin: 0; }
  </style>
</head>
<body>

<h2>User database</h2>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
  <label for="name">Name:</label>
  <input type="text" id="name" name="name" placeholder="Name" value="<?php echo htmlspecialchars($submitted_name); ?>">
  <label for="age">Age:</label>
  <input type="text" id="age" name="age" placeholder="Age" value="<?php echo htmlspecialchars($submitted_age); ?>">
  <input type="submit" value="Submit">
</form>

<?php if ($message !== "") { ?>
  <p id="statusMessage"><?php echo htmlspecialchars($message); ?></p>
<?php } ?>

<h2>Users</h2>

<table>
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Age</th>
    <th>Status</th>
    <th>Action</th>
  </tr>

  <?php if ($result && $result->num_rows > 0) { ?>
    <?php while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?php echo htmlspecialchars($row['id']); ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['age']); ?></td>
        <td><?php echo htmlspecialchars($row['status']); ?></td>
        <td>
          <form class="action-form" method="get">
            <input type="hidden" name="toggle_id" value="<?php echo htmlspecialchars($row['id']); ?>">
            <button type="submit">Toggle</button>
          </form>
        </td>
      </tr>
    <?php } ?>
  <?php } else { ?>
    <tr>
      <td colspan="5">No records found.</td>
    </tr>
  <?php } ?>
</table>

<script>
  const statusMessage = document.getElementById('statusMessage');
  if (statusMessage) {
    setTimeout(() => {
      statusMessage.style.transition = 'opacity 0.5s ease';
      statusMessage.style.opacity = '0';
      setTimeout(() => {
        statusMessage.style.display = 'none';
      }, 500);
    }, 3000);
  }
</script>

</body>
</html>

<?php
$conn->close();
?>
<?php
session_start();
include "config.php";

$message = "";
$message_type = "";

// 1. Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != "doctor") {
    die("Access Denied.");
}

$user_id = $_SESSION['user_id'];
$doctor_id = "";

// Get doctor_id securely
$doc_stmt = $conn->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
if ($doc_stmt) {
    $doc_stmt->bind_param("i", $user_id);
    $doc_stmt->execute();
    $doc_result = $doc_stmt->get_result();
    if ($doc_result->num_rows > 0) {
        $doctor = $doc_result->fetch_assoc();
        $doctor_id = $doctor['doctor_id'];
    } else {
        die("Doctor profile not found.");
    }
    $doc_stmt->close();
}

// 2. Secure Delete Schedule Handler
if (isset($_GET['delete_schedule'])) {
    $schedule_id = $_GET['delete_schedule'];

    $delete_stmt = $conn->prepare("DELETE FROM schedules WHERE schedule_id = ? AND doctor_id = ?");
    if ($delete_stmt) {
        $delete_stmt->bind_param("ii", $schedule_id, $doctor_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        $message = "Schedule slot deleted successfully.";
        $message_type = "success";
    }
    header("Location: doctor_schedule.php");
    exit();
}

// 3. Secure Add Schedule Handler
if (isset($_POST['add_schedule'])) {
    $date = $_POST['available_date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    // Check for timeline conflicts securely
    $check_stmt = $conn->prepare("
        SELECT schedule_id 
        FROM schedules 
        WHERE doctor_id = ? 
          AND available_date = ? 
          AND (? < end_time AND ? > start_time)
    ");
    
    if ($check_stmt) {
        $check_stmt->bind_param("isss", $doctor_id, $date, $start, $end);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message = "Schedule slot conflict detected. Slot overlaps with an existing time.";
            $message_type = "error";
        } else {
            // Insert slot securely
            $insert_stmt = $conn->prepare("
                INSERT INTO schedules (doctor_id, available_date, start_time, end_time) 
                VALUES (?, ?, ?, ?)
            ");
            if ($insert_stmt) {
                $insert_stmt->bind_param("isss", $doctor_id, $date, $start, $end);
                if ($insert_stmt->execute()) {
                    $message = "Schedule slot added successfully!";
                    $message_type = "success";
                } else {
                    $message = "Failed to add schedule slot.";
                    $message_type = "error";
                }
                $insert_stmt->close();
            }
        }
        $check_stmt->close();
    }
}

// Fetch doctor's schedules securely using prepared statement
$schedules_result = null;
$list_stmt = $conn->prepare("
    SELECT schedule_id, available_date, start_time, end_time 
    FROM schedules 
    WHERE doctor_id = ? 
    ORDER BY available_date ASC, start_time ASC
");
if ($list_stmt) {
    $list_stmt->bind_param("i", $doctor_id);
    $list_stmt->execute();
    $schedules_result = $list_stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Schedule - Bird Safety Appointment System</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="page-bg">
  <div class="page-glow page-glow-a"></div>
  <div class="page-glow page-glow-b"></div>

  <header class="site-header content-header">
    <div class="wrap header-inner">
      <a class="brand" href="index.php">
        <span class="brand-mark">BS</span>
        <span>
          Bird Safety
          <small>Appointment System</small>
        </span>
      </a>

      <nav class="nav">
        <a href="index.php">Home</a>
        <a href="doctor_dashboard.php">Dashboard</a>
        <a class="active" href="doctor_schedule.php">Manage Schedule</a>
        <a href="doctors.php">Doctors</a>
        <a href="logout.php" style="color: var(--danger);">Logout</a>
      </nav>
    </div>
  </header>

  <main class="wrap page-main">
    <div style="margin-bottom: 2rem;">
      <h1 style="color: var(--accent); margin: 0; font-weight: 700;">Manage My Schedules</h1>
      <p style="color: var(--muted); margin-top: 0.25rem;">Create and remove available slots for patient bookings.</p>
    </div>

    <?php if ($message != "") { ?>
      <div style="padding: 12px 16px; border-radius: 8px; margin-bottom: 2rem; font-size: 0.95rem; font-weight: 500;
        <?php echo $message_type == 'success' ? 'color: #065f46; background: #d1fae5; border: 1px solid #a7f3d0;' : 'color: #b91c1c; background: #fee2e2; border: 1px solid #fca5a5;'; ?>">
        <?php echo $message_type == 'success' ? '✅' : '⚠️'; ?> <?php echo htmlspecialchars($message); ?>
      </div>
    <?php } ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
      <!-- Add Available Time Form Redesign -->
      <section class="card glass-card">
        <h3 style="color: var(--accent); margin-bottom: 1.25rem; font-weight: 600;">Add Available Slot</h3>
        
        <form method="POST">
          <label class="field">
            Available Date
            <input
              type="date"
              name="available_date"
              required
            />
          </label>

          <label class="field">
            Start Time
            <input
              type="time"
              name="start_time"
              required
            />
          </label>

          <label class="field">
            End Time
            <input
              type="time"
              name="end_time"
              required
            />
          </label>

          <button
            class="btn primary"
            type="submit"
            name="add_schedule"
            style="width: 100%; border-radius: 12px; margin-top: 0.5rem;"
          >
            Add to Calendar
          </button>
        </form>
      </section>

      <!-- Schedules List Table Redesign -->
      <section class="card glass-card" style="overflow-x: auto;">
        <h3 style="color: var(--accent-2); margin-bottom: 0.5rem; font-weight: 600;">Active Time Calendar</h3>
        <p style="color: var(--muted); margin-bottom: 1.25rem; font-size: 0.92rem;">Review your available listed blocks.</p>

        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Start Time</th>
              <th>End Time</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($schedules_result && $schedules_result->num_rows > 0) { ?>
              <?php while ($row = $schedules_result->fetch_assoc()) { ?>
                <tr>
                  <td style="font-weight: 600;"><?php echo date("d M Y", strtotime($row['available_date'])); ?></td>
                  <td><?php echo date("h:i A", strtotime($row['start_time'])); ?></td>
                  <td><?php echo date("h:i A", strtotime($row['end_time'])); ?></td>
                  <td>
                    <a
                      href="doctor_schedule.php?delete_schedule=<?php echo $row['schedule_id']; ?>"
                      class="btn secondary"
                      style="min-height: 2rem; padding: 0.2rem 0.65rem; font-size: 0.8rem; border-radius: 6px; color: var(--danger); border-color: rgba(239, 68, 68, 0.2);"
                      onclick="return confirm('Delete this schedule slot?');"
                    >
                      Delete
                    </a>
                  </td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr>
                <!-- Corrected Colspan Bug from colspan="3" to colspan="4" -->
                <td colspan="4" style="text-align: center; color: var(--muted); padding: 2.5rem;">No schedules listed in calendar.</td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </section>
    </div>
  </main>

  <footer class="footer" style="margin-top: 5rem;">
    <div class="wrap">
      <p>&copy; <?php echo date('Y'); ?> MedLink Appointment System. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
<?php
if (isset($list_stmt)) {
    $list_stmt->close();
}
?>
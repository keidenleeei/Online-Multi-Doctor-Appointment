<?php
session_start();
include "config.php";

$message = "";
$message_type = "";

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$selected_doctor = "";

if (isset($_GET['doctor_id'])) {
    $selected_doctor = $_GET['doctor_id'];
}
if (isset($_POST['doctor_id'])) {
    $selected_doctor = $_POST['doctor_id'];
}

$selected_schedule = "";
if (isset($_GET['schedule_id'])) {
    $selected_schedule = $_GET['schedule_id'];
}

// Submit booking
if (isset($_POST['submit_booking'])) {
    $doctor_id = $_POST['doctor_id'];
    $schedule_id = $_POST['schedule_id'];

    if (!empty($doctor_id) && !empty($schedule_id)) {
        // Secure prepared statement to fetch available date from schedule
        $schedule_stmt = $conn->prepare("SELECT available_date FROM schedules WHERE schedule_id = ?");
        if ($schedule_stmt) {
            $schedule_stmt->bind_param("i", $schedule_id);
            $schedule_stmt->execute();
            $schedule_result = $schedule_stmt->get_result();
            
            if ($schedule_result->num_rows > 0) {
                $schedule_data = $schedule_result->fetch_assoc();
                $date = $schedule_data['available_date'];

                // Secure prepared statement to insert booking
                $insert_stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, schedule_id, appointment_date, status) VALUES (?, ?, ?, ?, 'Pending')");
                if ($insert_stmt) {
                    $insert_stmt->bind_param("iiis", $patient_id, $doctor_id, $schedule_id, $date);
                    if ($insert_stmt->execute()) {
                        $message = "Appointment booked successfully!";
                        $message_type = "success";
                        $selected_schedule = ""; // reset selected schedule on success
                    } else {
                        $message = "Booking failed. Please try again.";
                        $message_type = "error";
                    }
                    $insert_stmt->close();
                } else {
                    $message = "System error during booking creation.";
                    $message_type = "error";
                }
            } else {
                $message = "Invalid schedule slot selected.";
                $message_type = "error";
            }
            $schedule_stmt->close();
        } else {
            $message = "System error during slot verification.";
            $message_type = "error";
        }
    } else {
        $message = "Please select both a doctor and a schedule slot.";
        $message_type = "error";
    }
}

// Fetch doctors
$doctor_sql = "
    SELECT doctors.doctor_id, users.full_name, doctors.specialization 
    FROM doctors 
    INNER JOIN users ON doctors.user_id = users.user_id 
    WHERE users.role = 'doctor'
";
$doctors = mysqli_query($conn, $doctor_sql);

// Fetch schedules using prepared statement
$schedules_result = null;
if (!empty($selected_doctor)) {
    $schedules_stmt = $conn->prepare("
        SELECT schedules.schedule_id, schedules.doctor_id, schedules.available_date, schedules.start_time, schedules.end_time 
        FROM schedules 
        LEFT JOIN appointments ON schedules.schedule_id = appointments.schedule_id 
        WHERE schedules.doctor_id = ? 
          AND (appointments.schedule_id IS NULL OR appointments.status = 'Cancelled')
        ORDER BY available_date ASC, start_time ASC
    ");
    if ($schedules_stmt) {
        $schedules_stmt->bind_param("i", $selected_doctor);
        $schedules_stmt->execute();
        $schedules_result = $schedules_stmt->get_result();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Booking - Bird Safety Appointment System</title>
  <link rel="stylesheet" href="styles.css" />
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
        <a href="about.php">About</a>
        <a href="features.php">Features</a>
        <a href="doctors.php">Doctors</a>
        <a class="active" href="booking.php">Booking</a>
        <?php if(isset($_SESSION['user_id'])){ ?>
          <a href="dashboard.php">Dashboard</a>
        <?php } ?>
      </nav>
    </div>
  </header>

  <main class="wrap page-main">
    <div style="margin-bottom: 2rem;">
      <h1 style="color: var(--accent); margin: 0; font-weight: 700;">Book a Consultation</h1>
      <p style="color: var(--muted); margin-top: 0.25rem;">Select your veterinarian and an available calendar slot.</p>
    </div>

    <section class="grid-2 page-grid">
      <article class="form-card glass-card">
        <h3 style="color: var(--accent); margin-bottom: 1.5rem; font-weight: 600;">Booking Form</h3>

        <?php if ($message != "") { ?>
          <div style="padding: 10px 14px; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.92rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;
            <?php echo $message_type == 'success' ? 'color: #065f46; background: #d1fae5; border: 1px solid #a7f3d0;' : 'color: #b91c1c; background: #fee2e2; border: 1px solid #fca5a5;'; ?>">
            <?php echo $message_type == 'success' ? '✅' : '⚠️'; ?> <?php echo htmlspecialchars($message); ?>
          </div>
        <?php } ?>

        <form method="POST" action="booking.php">
          <label class="field">
            Patient ID (Logged In User)
            <input 
              type="text" 
              value="<?php echo htmlspecialchars($_SESSION['name']) . ' (ID: ' . htmlspecialchars($_SESSION['user_id']) . ')'; ?>" 
              disabled
              style="background: var(--panel-2); color: var(--muted);"
            />
          </label>

          <label class="field">
            Avian Doctor / Specialist
            <select name="doctor_id" required onchange="this.form.submit()">
              <option value="">Select Doctor</option>
              <?php while ($doctor = mysqli_fetch_assoc($doctors)) { ?>
                <option 
                  value="<?php echo $doctor['doctor_id']; ?>"
                  <?php if ($selected_doctor == $doctor['doctor_id']) echo "selected"; ?>
                >
                  Dr. <?php echo htmlspecialchars($doctor['full_name']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?>
                </option>
              <?php } ?>
            </select>
          </label>

          <label class="field">
            Available Schedule Slots
            <select name="schedule_id" required>
              <option value="">
                <?php 
                  if (empty($selected_doctor)) {
                    echo "Please select a doctor first";
                  } else {
                    echo "Select Schedule Slot";
                  }
                ?>
              </option>
              <?php 
                if ($schedules_result && $schedules_result->num_rows > 0) { 
                  while ($schedule = $schedules_result->fetch_assoc()) { 
                    $formatted_date = date("d M Y", strtotime($schedule['available_date']));
                    $formatted_start = date("h:i A", strtotime($schedule['start_time']));
                    $formatted_end = date("h:i A", strtotime($schedule['end_time']));
              ?>
                <option
                  value="<?php echo $schedule['schedule_id']; ?>"
                  <?php if ($selected_schedule == $schedule['schedule_id']) echo "selected"; ?>
                >
                  <?php echo "$formatted_date | $formatted_start - $formatted_end"; ?>
                </option>
              <?php 
                  } 
                } elseif (!empty($selected_doctor)) {
              ?>
                <option value="" disabled>No available schedule slots for this doctor</option>
              <?php } ?>
            </select>
          </label>

          <div class="actions">
            <button class="btn primary" type="submit" name="submit_booking">Confirm Booking</button>
            <a href="index.php" class="btn secondary">Cancel</a>
          </div>
        </form>
      </article>

      <article class="card glass-card" style="display: flex; flex-direction: column; justify-content: center;">
        <h3 style="color: var(--accent-2); margin-bottom: 1rem; font-weight: 600;">Booking Guidelines</h3>
        <p style="margin-bottom: 1.25rem; font-size: 0.95rem; line-height: 1.6;">Follow these quick steps to secure an appointment slot with a specialist doctor:</p>
        <ol class="list" style="display: grid; gap: 0.75rem;">
          <li>Select a doctor from the drop-down menu to automatically load their calendar slots.</li>
          <li>Choose an available date and time slot that fits your schedule.</li>
          <li>Click <strong>Confirm Booking</strong> to instantly save the record to our workspace.</li>
          <li>View approval updates under your <strong>Dashboard</strong> tab.</li>
        </ol>
      </article>
    </section>
  </main>

  <footer class="footer">
    <div class="wrap">
      <p>&copy; <?php echo date('Y'); ?> MedLink Appointment System. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
<?php
if (isset($schedules_stmt)) {
    $schedules_stmt->close();
}
?>
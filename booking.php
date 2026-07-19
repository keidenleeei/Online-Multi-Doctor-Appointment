<?php
session_start();
include "config.php";

$message = "";
$message_type = "";
$editing_appointment_id = 0;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (($_SESSION['role'] ?? '') !== 'patient') {
    header("Location: dashboard.php");
    exit();
}

$patient_id = (int)$_SESSION['user_id'];
$selected_doctor = 0;
$selected_schedule = 0;

if (isset($_GET['doctor_id'])) {
    $selected_doctor = (int)$_GET['doctor_id'];
}
if (isset($_POST['doctor_id'])) {
    $selected_doctor = (int)$_POST['doctor_id'];
}
if (isset($_GET['schedule_id'])) {
    $selected_schedule = (int)$_GET['schedule_id'];
}
if (isset($_POST['schedule_id'])) {
    $selected_schedule = (int)$_POST['schedule_id'];
}

if (isset($_GET['appointment_id'])) {
    $editing_appointment_id = (int)$_GET['appointment_id'];
    $edit_stmt = $conn->prepare("SELECT appointment_id, doctor_id, schedule_id FROM appointments WHERE appointment_id = ? AND patient_id = ? AND status = 'Pending' LIMIT 1");
    if ($edit_stmt) {
        $edit_stmt->bind_param('ii', $editing_appointment_id, $patient_id);
        $edit_stmt->execute();
        $edit_result = $edit_stmt->get_result();
        if ($edit_result->num_rows > 0) {
            $editing_appointment = $edit_result->fetch_assoc();
            $selected_doctor = (int)$editing_appointment['doctor_id'];
            $selected_schedule = (int)$editing_appointment['schedule_id'];
        } else {
            $editing_appointment_id = 0;
        }
        $edit_stmt->close();
    }
}

if (isset($_POST['submit_booking'])) {
    $doctor_id = (int)$_POST['doctor_id'];
    $schedule_id = (int)$_POST['schedule_id'];
    $posted_appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;

    if ($posted_appointment_id > 0) {
        $editing_appointment_id = $posted_appointment_id;
    }

    if ($doctor_id > 0 && $schedule_id > 0) {
        $schedule_stmt = $conn->prepare("SELECT available_date FROM schedules WHERE schedule_id = ? AND doctor_id = ? LIMIT 1");
        if ($schedule_stmt) {
            $schedule_stmt->bind_param('ii', $schedule_id, $doctor_id);
            $schedule_stmt->execute();
            $schedule_result = $schedule_stmt->get_result();

            if ($schedule_result->num_rows > 0) {
                $schedule_data = $schedule_result->fetch_assoc();
                $date = $schedule_data['available_date'];

                $booking_check_stmt = $conn->prepare("SELECT appointment_id FROM appointments WHERE schedule_id = ? AND status <> 'Cancelled' AND appointment_id <> ? LIMIT 1");
                if ($booking_check_stmt) {
                    $booking_check_stmt->bind_param('ii', $schedule_id, $editing_appointment_id);
                    $booking_check_stmt->execute();
                    $booking_check_result = $booking_check_stmt->get_result();

                    if ($booking_check_result->num_rows > 0) {
                        $message = 'This slot has already been booked.';
                        $message_type = 'error';
                    } else {
                        if ($editing_appointment_id > 0) {
                            $update_stmt = $conn->prepare("UPDATE appointments SET doctor_id = ?, schedule_id = ?, appointment_date = ?, status = 'Pending' WHERE appointment_id = ? AND patient_id = ? AND status = 'Pending'");
                            if ($update_stmt) {
                                $update_stmt->bind_param('iisii', $doctor_id, $schedule_id, $date, $editing_appointment_id, $patient_id);
                                if ($update_stmt->execute()) {
                                    $message = 'Appointment updated successfully!';
                                    $message_type = 'success';
                                    $selected_schedule = 0;
                                } else {
                                    $message = 'Update failed. Please try again.';
                                    $message_type = 'error';
                                }
                                $update_stmt->close();
                            } else {
                                $message = 'System error during booking update.';
                                $message_type = 'error';
                            }
                        } else {
                            $insert_stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, schedule_id, appointment_date, status) VALUES (?, ?, ?, ?, 'Pending')");
                            if ($insert_stmt) {
                                $insert_stmt->bind_param('iiis', $patient_id, $doctor_id, $schedule_id, $date);
                                if ($insert_stmt->execute()) {
                                    $message = 'Appointment booked successfully!';
                                    $message_type = 'success';
                                    $selected_schedule = 0;
                                } else {
                                    $message = 'Booking failed. Please try again.';
                                    $message_type = 'error';
                                }
                                $insert_stmt->close();
                            } else {
                                $message = 'System error during booking creation.';
                                $message_type = 'error';
                            }
                        }
                    }
                    $booking_check_stmt->close();
                }
            } else {
                $message = 'Invalid schedule slot selected.';
                $message_type = 'error';
            }
            $schedule_stmt->close();
        } else {
            $message = 'System error during slot verification.';
            $message_type = 'error';
        }
    } else {
        $message = 'Please select both a doctor and a schedule slot.';
        $message_type = 'error';
    }
}

$doctors = mysqli_query(
    $conn,
    "
    SELECT doctors.doctor_id, users.full_name, doctors.specialization
    FROM doctors
    INNER JOIN users ON doctors.user_id = users.user_id
    WHERE users.role = 'doctor'
    ORDER BY users.full_name ASC
    "
);

$schedules_result = null;
if ($selected_doctor > 0) {
    $schedules_stmt = $conn->prepare("SELECT s.schedule_id, s.available_date, s.start_time, s.end_time FROM schedules s LEFT JOIN appointments a ON s.schedule_id = a.schedule_id AND a.status <> 'Cancelled' AND a.appointment_id <> ? WHERE s.doctor_id = ? AND a.schedule_id IS NULL ORDER BY s.available_date ASC, s.start_time ASC");
    if ($schedules_stmt) {
        $schedules_stmt->bind_param('ii', $editing_appointment_id, $selected_doctor);
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
  <title><?php echo $editing_appointment_id > 0 ? 'Reschedule Appointment' : 'Booking'; ?> - MedLink Appointment System</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="page-bg">
  <div class="page-glow page-glow-a"></div>
  <div class="page-glow page-glow-b"></div>

  <header class="site-header content-header">
    <div class="wrap header-inner">
      <a class="brand" href="index.php">
        <span class="brand-mark">ML</span>
        <span>
          MedLink
          <small>Appointment System</small>
        </span>
      </a>

      <nav class="nav">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="features.php">Features</a>
        <a href="doctors.php">Doctors</a>
        <a class="active" href="booking.php">Booking</a>
        <a href="dashboard.php">Dashboard</a>
      </nav>
    </div>
  </header>

  <main class="wrap page-main">
    <div style="margin-bottom: 2rem;">
      <h1 style="color: var(--accent); margin: 0; font-weight: 700;"><?php echo $editing_appointment_id > 0 ? 'Reschedule Consultation' : 'Book a Consultation'; ?></h1>
      <p style="color: var(--muted); margin-top: 0.25rem;"><?php echo $editing_appointment_id > 0 ? 'Update the doctor or time slot for your pending appointment.' : 'Select a doctor and an available calendar slot.'; ?></p>
    </div>

    <section class="grid-2 page-grid">
      <article class="form-card glass-card">
        <h3 style="color: var(--accent); margin-bottom: 1.5rem; font-weight: 600;"><?php echo $editing_appointment_id > 0 ? 'Reschedule Form' : 'Booking Form'; ?></h3>

        <?php if ($message !== '') { ?>
          <div style="padding: 10px 14px; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.92rem; font-weight: 500; display: flex; align-items: center; gap: 0.5rem; <?php echo $message_type === 'success' ? 'color: #065f46; background: #d1fae5; border: 1px solid #a7f3d0;' : 'color: #b91c1c; background: #fee2e2; border: 1px solid #fca5a5;'; ?>">
            <?php echo $message_type === 'success' ? '+' : '!'; ?> <?php echo htmlspecialchars($message); ?>
          </div>
        <?php } ?>

        <form method="POST" action="booking.php<?php echo $editing_appointment_id > 0 ? '?appointment_id=' . (int)$editing_appointment_id : ''; ?>">
          <?php if ($editing_appointment_id > 0) { ?>
            <input type="hidden" name="appointment_id" value="<?php echo (int)$editing_appointment_id; ?>" />
          <?php } ?>
          <label class="field">
            Patient account
            <input type="text" value="<?php echo htmlspecialchars($_SESSION['name']) . ' (ID: ' . htmlspecialchars($_SESSION['user_id']) . ')'; ?>" disabled style="background: var(--panel-2); color: var(--muted);" />
          </label>

          <label class="field">
            Doctor / Specialist
            <select name="doctor_id" required onchange="this.form.submit()">
              <option value="">Select Doctor</option>
              <?php while ($doctor = mysqli_fetch_assoc($doctors)) { ?>
                <option value="<?php echo (int)$doctor['doctor_id']; ?>" <?php if ($selected_doctor === (int)$doctor['doctor_id']) echo 'selected'; ?>>
                  Dr. <?php echo htmlspecialchars($doctor['full_name']); ?> - <?php echo htmlspecialchars($doctor['specialization']); ?>
                </option>
              <?php } ?>
            </select>
          </label>

          <label class="field">
            Available Schedule Slots
            <select name="schedule_id" required>
              <option value=""><?php echo $selected_doctor > 0 ? 'Select Schedule Slot' : 'Please select a doctor first'; ?></option>
              <?php if ($schedules_result && $schedules_result->num_rows > 0) { ?>
                <?php while ($schedule = $schedules_result->fetch_assoc()) { ?>
                  <?php
                    $formatted_date = date('d M Y', strtotime($schedule['available_date']));
                    $formatted_start = date('h:i A', strtotime($schedule['start_time']));
                    $formatted_end = date('h:i A', strtotime($schedule['end_time']));
                  ?>
                  <option value="<?php echo (int)$schedule['schedule_id']; ?>" <?php if ($selected_schedule === (int)$schedule['schedule_id']) echo 'selected'; ?>>
                    <?php echo $formatted_date . ' | ' . $formatted_start . ' - ' . $formatted_end; ?>
                  </option>
                <?php } ?>
              <?php } elseif ($selected_doctor > 0) { ?>
                <option value="" disabled>No available schedule slots for this doctor</option>
              <?php } ?>
            </select>
          </label>

          <div class="actions">
            <button class="btn primary" type="submit" name="submit_booking"><?php echo $editing_appointment_id > 0 ? 'Save Changes' : 'Confirm Booking'; ?></button>
            <a href="index.php" class="btn secondary">Cancel</a>
          </div>
        </form>
      </article>

      <article class="card glass-card" style="display: flex; flex-direction: column; justify-content: center;">
        <h3 style="color: var(--accent-2); margin-bottom: 1rem; font-weight: 600;">Booking Guidelines</h3>
        <p style="margin-bottom: 1.25rem; font-size: 0.95rem; line-height: 1.6;">Follow these quick steps to secure an appointment slot with a specialist doctor:</p>
        <ol class="list" style="display: grid; gap: 0.75rem;">
          <li>Select a doctor from the drop-down menu to load their available slots.</li>
          <li>Choose an available date and time slot that fits your schedule.</li>
          <li>Click <strong><?php echo $editing_appointment_id > 0 ? 'Save Changes' : 'Confirm Booking'; ?></strong> to save the record.</li>
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

  <?php
  if (isset($schedules_stmt)) {
      $schedules_stmt->close();
  }
  ?>
</body>
</html>

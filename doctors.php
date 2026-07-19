<?php
session_start();
include "config.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$specialty = isset($_GET['specialty']) ? trim($_GET['specialty']) : '';

$specializations = [];
$specs_query = mysqli_query($conn, "SELECT DISTINCT specialization FROM doctors ORDER BY specialization ASC");
if ($specs_query) {
    while ($spec = mysqli_fetch_assoc($specs_query)) {
        $specializations[] = $spec['specialization'];
    }
}

$like_search = '%' . $search . '%';

if ($specialty !== '') {
    $stmt = $conn->prepare("SELECT doctors.doctor_id, users.full_name, doctors.specialization, doctors.experience, doctors.consultation_fee FROM doctors INNER JOIN users ON doctors.user_id = users.user_id WHERE users.role = 'doctor' AND (users.full_name LIKE ? OR doctors.specialization LIKE ?) AND doctors.specialization = ? ORDER BY users.full_name ASC");
    if ($stmt) {
        $stmt->bind_param('sss', $like_search, $like_search, $specialty);
    }
} else {
    $stmt = $conn->prepare("SELECT doctors.doctor_id, users.full_name, doctors.specialization, doctors.experience, doctors.consultation_fee FROM doctors INNER JOIN users ON doctors.user_id = users.user_id WHERE users.role = 'doctor' AND (users.full_name LIKE ? OR doctors.specialization LIKE ?) ORDER BY users.full_name ASC");
    if ($stmt) {
        $stmt->bind_param('ss', $like_search, $like_search);
    }
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die('Database query error.');
}

$total_results = $result ? $result->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Doctors - MedLink Appointment System</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="page-bg">
  <div class="page-glow page-glow-a"></div>
  <div class="page-glow page-glow-b"></div>

  <header class="site-header content-header">
    <div class="wrap header-inner">
      <a class="brand" href="index.php">
        <span class="brand-mark">ML</span>
        <span>MedLink<small>Appointment System</small></span>
      </a>

      <nav class="nav">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="features.php">Features</a>
        <a class="active" href="doctors.php">Doctors</a>
        <a href="booking.php">Booking</a>
        <?php if (isset($_SESSION['user_id'])) { ?>
          <a href="dashboard.php">Dashboard</a>
        <?php } ?>
      </nav>
    </div>
  </header>

  <main class="wrap page-main">
    <div style="margin-bottom: 2rem;">
      <h1 style="color: var(--accent); margin: 0; font-weight: 700;">Find Medical Specialists</h1>
      <p style="color: var(--muted); margin-top: 0.25rem;">Search by name, then narrow results by specialization.</p>
    </div>

    <form method="GET" action="doctors.php" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1rem; background: var(--panel); padding: 1.25rem; border-radius: var(--radius); border: 1px solid var(--line); box-shadow: var(--shadow);">
      <input
        type="text"
        name="search"
        placeholder="Search doctor or specialization (e.g. Surgery, Cardiology)..."
        value="<?php echo htmlspecialchars($search); ?>"
        style="flex: 1; min-width: 250px; border-radius: 999px;"
      />
      <select name="specialty" style="min-width: 220px; border-radius: 999px;">
        <option value="">All Specializations</option>
        <?php foreach ($specializations as $spec) { ?>
          <option value="<?php echo htmlspecialchars($spec); ?>" <?php if ($specialty === $spec) echo 'selected'; ?>><?php echo htmlspecialchars($spec); ?></option>
        <?php } ?>
      </select>
      <div style="display: flex; gap: 0.5rem;">
        <button class="btn primary" type="submit" style="min-height: 2.8rem; padding: 0.5rem 1.5rem;">Search</button>
        <?php if ($search !== '' || $specialty !== '') { ?>
          <a href="doctors.php" class="btn secondary" style="min-height: 2.8rem; display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1.25rem;">Reset</a>
        <?php } ?>
      </div>
    </form>

    <div style="margin-bottom: 1.25rem; color: var(--muted); font-size: 0.92rem;">Showing <?php echo (int)$total_results; ?> doctor(s).</div>

    <section class="grid-3 page-grid">
      <?php if ($result && $result->num_rows > 0) { ?>
        <?php while ($doctor = $result->fetch_assoc()) { ?>
          <article class="card glass-card doctor">
            <div class="doctor-top">
              <div>
                <h3>Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h3>
                <span class="pill good" style="margin-top: 0.5rem;"><?php echo htmlspecialchars($doctor['specialization']); ?></span>
              </div>
            </div>

            <div style="margin: 0.5rem 0; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); padding: 0.75rem 0; display: grid; gap: 0.35rem; font-size: 0.92rem; color: #475569;">
              <div style="display: flex; justify-content: space-between;">
                <span>Experience:</span>
                <strong><?php echo htmlspecialchars($doctor['experience']); ?> Years</strong>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span>Consultation Fee:</span>
                <strong style="color: var(--accent-2);">RM <?php echo number_format($doctor['consultation_fee'], 2); ?></strong>
              </div>
            </div>

            <a href="booking.php?doctor_id=<?php echo (int)$doctor['doctor_id']; ?>" class="btn primary" style="width: 100%; border-radius: 12px; min-height: 2.8rem; margin-top: 0.5rem;">Book Appointment</a>
          </article>
        <?php } ?>
      <?php } else { ?>
        <article class="card glass-card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
          <div style="font-size: 3rem; margin-bottom: 1rem;">Search</div>
          <h3 style="margin: 0; color: var(--accent);">No Doctors Found</h3>
          <p style="color: var(--muted); margin-top: 0.5rem;">We couldn't find any doctors matching your filters. Try another specialization or reset the search.</p>
          <a href="doctors.php" class="btn primary" style="margin-top: 1.5rem; display: inline-flex;">Show All Doctors</a>
        </article>
      <?php } ?>
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
if (isset($stmt)) {
    $stmt->close();
}
?>

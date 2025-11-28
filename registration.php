<?php
session_start();
require 'db.php'; // $conn = new mysqli(...)

$message = "";
$myRegistrations = [];

//reg detail fetch from db
// --- Handle AJAX request for checking registrations ---
if (isset($_GET['check_userid'])) {
    $userid = strtoupper(trim($_GET['check_userid']));
    $stmt = $conn->prepare("SELECT e.event_name, r.status 
                            FROM registrations r
                            JOIN events e ON r.event_id = e.id
                            WHERE r.userid = ?");
    $stmt->bind_param("s", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    $registrations = [];
    while ($row = $result->fetch_assoc()) {
        $registrations[] = $row;
    }
    $stmt->close();
    echo json_encode($registrations);
    exit; // imp to stop rest of page rendering for AJAX
}


// --- SOLO REGISTRATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'solo') {
    $userid     = strtoupper(trim($_POST['idnum']));
    $fullname   = trim($_POST['fullname']);
    $mobile     = trim($_POST['mobilenum']);
    $class      = $_POST['class'];
    $department = $_POST['department'];
    $events     = $_POST['events'] ?? [];

    foreach ($events as $event_id) {
 
   
    // 1. Prevent duplicate registration
    $check = $conn->prepare("SELECT id FROM registrations WHERE userid=? AND event_id=?");
    $check->bind_param("si", $userid, $event_id);
    $check->execute();
    $check->store_result();

    // fetch event_name
    $ename_stmt = $conn->prepare("SELECT event_name FROM events WHERE id=?");
    $ename_stmt->bind_param("i", $event_id);
    $ename_stmt->execute();
    $ename_stmt->bind_result($event_name);
    $ename_stmt->fetch();
    $ename_stmt->close();

    if ($check->num_rows > 0) {
        $message .= "❌ Already registered for <b>$event_name</b>.<br>";
        continue;
    } 



        // 2. Check capacity
        $capacityCheck = $conn->prepare("SELECT capacity FROM events WHERE id=?");
        $capacityCheck->bind_param("i", $event_id);
        $capacityCheck->execute();
        $capacityCheck->bind_result($capacity);
        $capacityCheck->fetch();
        $capacityCheck->close();

        $countRes = $conn->prepare("SELECT COUNT(*) FROM registrations WHERE event_id=? AND status IN ('Pending','Approved')");
        $countRes->bind_param("i", $event_id);
        $countRes->execute();
        $countRes->bind_result($currentCount);
        $countRes->fetch();
        $countRes->close();

        if ($currentCount >= $capacity) {
            $message .= "⚠️ Event  $event_name is full.<br>";
            continue;
        }

        // 3. Insert registration
        $stmt = $conn->prepare("INSERT INTO registrations (userid, fullname, mobile, class, department, event_id) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("sssssi", $userid, $fullname, $mobile, $class, $department, $event_id);
        $stmt->execute();
        $stmt->close();

        $message .= "✅ Registered successfully for Event  $event_name.<br>";
    }
}

// --- TEAM REGISTRATION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'team') {
    $userid     = strtoupper(trim($_POST['idnum2']));
    $fullname   = trim($_POST['fullname2']);
    $mobile     = trim($_POST['mobilenum2']);
    $class      = $_POST['class2'];
    $department = $_POST['department2'];
    $membersId  = $_POST['memberId'] ?? [];
    $membersName= $_POST['memberName'] ?? [];

    // Hardcoding Treasure Hunt event id (fetch from DB instead of manual)
    $treasureEventId = null;
    $q = $conn->query("SELECT id FROM events WHERE event_name='Treasure Hunt' LIMIT 1");
    if ($row = $q->fetch_assoc()) {
        $treasureEventId = $row['id'];
    }

    if ($treasureEventId) {
        // 1. Prevent duplicate registration
        $check = $conn->prepare("SELECT id FROM registrations WHERE userid=? AND event_id=?");
        $check->bind_param("si", $userid, $treasureEventId);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $message .= "❌ Leader already registered for Treasure Hunt.<br>";
        } else {
            // 2. Check capacity
            $capacityCheck = $conn->prepare("SELECT capacity FROM events WHERE id=?");
            $capacityCheck->bind_param("i", $treasureEventId);
            $capacityCheck->execute();
            $capacityCheck->bind_result($capacity);
            $capacityCheck->fetch();
            $capacityCheck->close();

            $countRes = $conn->prepare("SELECT COUNT(*) FROM registrations WHERE event_id=? AND status IN ('Pending','Approved')");
            $countRes->bind_param("i", $treasureEventId);
            $countRes->execute();
            $countRes->bind_result($currentCount);
            $countRes->fetch();
            $countRes->close();

            if ($currentCount >= $capacity) {
                $message .= "⚠️ Treasure Hunt is full.<br>";
            } else {
                // 3. Insert leader registration
                $stmt = $conn->prepare("INSERT INTO registrations (userid, fullname, mobile, class, department, event_id) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param("sssssi", $userid, $fullname, $mobile, $class, $department, $treasureEventId);
                $stmt->execute();
                $stmt->close();

                // 4. Insert team members (with suffix in fullname for clarity)
                foreach ($membersId as $idx => $mid) {
                    $mid = strtoupper(trim($mid));
                    $mname = trim($membersName[$idx]);
                    if ($mid && $mname) {
                        $stmt = $conn->prepare("INSERT INTO registrations (userid, fullname, mobile, class, department, event_id) VALUES (?,?,?,?,?,?)");
                        $dummyMobile = "NA";
                        $dummyClass  = $class;
                        $dummyDept   = $department;
                        $stmt->bind_param("sssssi", $mid, $mname, $dummyMobile, $dummyClass, $dummyDept, $treasureEventId);
                        $stmt->execute();
                        $stmt->close();
                    }
                }

                $message .= "✅ Team registered successfully for Treasure Hunt.<br>";
            }
        }
    }
}

// --- Fetch user's registrations (by ID from either form) ---
$userIdToCheck = "";
if (!empty($_POST['idnum'])) $userIdToCheck = strtoupper(trim($_POST['idnum']));
if (!empty($_POST['idnum2'])) $userIdToCheck = strtoupper(trim($_POST['idnum2']));

if ($userIdToCheck) {
    $result = $conn->prepare("SELECT e.event_name, r.status 
                              FROM registrations r 
                              JOIN events e ON r.event_id = e.id 
                              WHERE r.userid=?");
    $result->bind_param("s", $userIdToCheck);
    $result->execute();
    $res = $result->get_result();
    while ($row = $res->fetch_assoc()) {
        $myRegistrations[] = $row;
    }
    $result->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="index.css" rel="stylesheet">
  <link href="registration.css" rel="stylesheet">
</head>
<body>
<header class="topbar">
  <div id="logo">
    <img src="logo.png" alt="logo">
    <div id="afterlogo"><h1>Inventrix</h1></div>
  </div>
  <nav id="navbar">
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="about.html">About</a></li>
      <li><a href="events.php">Events</a></li>
      <li><a href="contact.html">Contact</a></li>
      <li><a href="registration.php">Registration</a></li>
      <li><a href="gallery.html">Gallery</a></li>
    </ul>
  </nav>
  <div class="right-controls">
    <button id="login"><a href="login.php">Log in</a></button>
    <button class="hamburger" id="hamburger">&#9776;</button>
  </div>
</header>

<main>
  <div class="pagename"><h2> registration </h2></div>
  <!-- for alert msg -->
<?php if (!empty($message)): ?>
  <div id="message" class="message-box">
    <?php echo $message; ?>
  </div>
<?php endif; ?>

  <!-- Solo -->
  <div id="registration-area-solo" class="registration-area">
    <h2> Registration Form for <b>Solo Events</b></h2>
    <p>Welcome to the registration section. Please fill out the form below:</p>
    <!-- bkh<kjhu?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?> -->
    <div class="form-container">
      <form method="post">
        <input type="hidden" name="form_type" value="solo">
        <label for="idnum">ID Number</label>
        <input type="text" id="idnum" name="idnum" required pattern="[A-Z0-9]{7}">
        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname" required>
        <label for="mobilenum">Mobile Number</label>
        <input type="tel" id="mobilenum" name="mobilenum" required pattern="[0-9]{10}">
        <label for="class">Class</label>
        <select id="class" name="class" required>
          <option value="">--Select Class--</option>
          <option value="FY">FY</option>
          <option value="SY">SY</option>
          <option value="TY">TY</option>
        </select>
        <label for="department">Department</label>
        <select id="department" name="department" required>
          <option value="">--Select Department--</option>
          <option>Junior College</option>
          <option>B.Com</option>
          <option>BA</option>
          <option>BSc</option>
          <option>BAF</option>
          <option>BMS</option>
          <option>BBA</option>
          <option>BSc.IT</option>
          <option>BSc.Agro</option>
        </select>
        <label>Select Events (You can choose multiple)</label>
        <div class="events-group">
          <?php
          $events = $conn->query("SELECT id, event_name FROM events WHERE event_name!='Treasure Hunt'");
          while ($row = $events->fetch_assoc()) {
              echo "<label><input type='checkbox' name='events[]' value='{$row['id']}'> {$row['event_name']}</label><br>";
          }
          ?>
        </div>
        <button type="submit" class="submitBtn">Submit</button>
      </form>
    </div>
  </div>

  <!-- Team -->
  <div id="registration-area-team" class="registration-area">
    <h2> Registration Form for <br><b>Team Event: Treasure Hunt</b></h2>
    <p>Please register your team. Group leader’s details are required along with member names.</p>
    <div class="form-container">
      <form method="post">
        <input type="hidden" name="form_type" value="team">
        <label for="idnum2">Leader ID Number</label>
        <input type="text" id="idnum2" name="idnum2" required pattern="[A-Z0-9]{7}">
        <label for="fullname2">Leader Full Name</label>
        <input type="text" id="fullname2" name="fullname2" required>
        <label for="mobilenum2">Leader Mobile Number</label>
        <input type="tel" id="mobilenum2" name="mobilenum2" required pattern="[0-9]{10}">
        <label for="class2">Class</label>
        <select id="class2" name="class2" required>
          <option value="">--Select Class--</option>
          <option value="FY">FY</option>
          <option value="SY">SY</option>
          <option value="TY">TY</option>
        </select>
        <label for="department2">Department</label>
        <select id="department2" name="department2" required>
          <option value="">--Select Department--</option>
          <option>Junior College</option>
          <option>B.Com</option>
          <option>BA</option>
          <option>BSc</option>
          <option>BAF</option>
          <option>BMS</option>
          <option>BBA</option>
          <option>BSc.IT</option>
          <option>BSc.Agro</option>
        </select>
        <label>Team Members (excluding leader)</label>
        <div id="teamMembers">
          <div class="member">
            <input type="text" name="memberId[]" placeholder="Member ID">
            <input type="text" name="memberName[]" placeholder="Enter Member Name">
          </div>
        </div>
        <button type="button" id="addMemberBtn">+ Add Member</button>
        <button type="submit" class="submitBtn">Submit</button>
      </form>
    </div>
  </div>

 <div id="my-registrations-section" class="registration-area">
  <h3>Check Your Registrations</h3>
  <p>Enter your User ID to see registration details:</p>
  
  <div class="form-container">
    <input type="text" id="checkUserId" placeholder="Enter Your ID" pattern="[A-Z0-9]{7}">
    <button type="button" id="checkBtn" class="submitBtn">Check Registrations</button>
  </div>

  <div id="registrationsList" style="display:none; margin-top:10px;">
    <!-- Registration results will appear here -->
  </div>
</div>

</main>

<script>
document.getElementById("addMemberBtn").addEventListener("click", function() {
  const div = document.createElement("div");
  div.classList.add("member");
  div.innerHTML = `
    <input type="text" name="memberId[]" placeholder="Member ID">
    <input type="text" name="memberName[]" placeholder="Member Name">
  `;
  document.getElementById("teamMembers").appendChild(div);
});


  setTimeout(() => {
    let msg = document.getElementById("message");
    if (msg) {
      msg.style.display = "none";
    }
  }, 10000); // disappears after 10 seconds

//details n status

document.getElementById("checkBtn").addEventListener("click", function() {
  const userid = document.getElementById("checkUserId").value.trim().toUpperCase();
  const listDiv = document.getElementById("registrationsList");
  
  if (!userid) {
    alert("Please enter your User ID.");
    return;
  }

  // AJAX request to same page with GET parameter
  fetch(`<?php echo $_SERVER['PHP_SELF']; ?>?check_userid=${encodeURIComponent(userid)}`)
    .then(response => response.json())
    .then(data => {
      if (data.length === 0) {
        listDiv.innerHTML = "<p>No registrations found.</p>";
      } else {
        let html = "<ul>";
        data.forEach(reg => {
          html += `<li>${reg.event_name} - Status: ${reg.status}</li>`;
        });
        html += "</ul>";
        listDiv.innerHTML = html;
      }
      listDiv.style.display = "block"; // show results
    })
    .catch(err => {
      console.error(err);
      listDiv.innerHTML = "<p>Error fetching registrations.</p>";
      listDiv.style.display = "block";
    });
});


</script>
 <script src="index.js"></script>
</body>
</html>

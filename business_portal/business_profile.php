<?php
// start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// make sure user is logged in and is a business
if (!isset($_SESSION['logged_in']) || $_SESSION['userType'] !== 'business') {
    header("Location: ../login/login_signup.php");
    exit();
}

include '../sql/db.php';

if (!$mysql) {
  die("Database connection failed.");
}


$businessId = $_SESSION['userId'];

// fetch business info
$stmt = $mysql->prepare("SELECT Name, Email FROM Business WHERE BusinessID = :businessId");
$stmt->bindParam(':businessId', $businessId, PDO::PARAM_INT);
$stmt->execute();
$business = $stmt->fetch(PDO::FETCH_ASSOC);

if ($business) {
    $businessName = htmlspecialchars($business['Name']);
    $businessEmail = htmlspecialchars($business['Email']);
} else {
    $businessName = "Unknown Business";
    $businessEmail = "email@example.com";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Business Profile</title>
    <link rel="stylesheet" href="business_profile.css?v=<?php echo time(); ?>"> <!--handles cache issues-->
    <link rel="stylesheet" href="../footer.css" />
    <link rel="stylesheet" href="../navbar.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
</head>

<body>
    <?php include '../navbar.php'; ?>

    <main class="section">
        <h2>Business Profile</h2>

        <!-- summary -->
        <section class="card summary">
            <div class="logo-wrap">
                <img src="bottle.jpg" alt="Company Logo" />
                <button class="btn small" id="change-logo">Change Logo</button>
            </div>
            <div class="info">
                 <h3 id="company-name"><?php echo $businessName; ?></h3>
                <p id="company-tagline">Smart hydration, sustainable future.</p>
                <p id="company-email"><?php echo $businessEmail; ?></p>
                <p class="muted">Joined: <span id="joined">14 Mar 2024</span></p>
            </div>
        </section>

        <!-- company details -->
        <section class="card">
            <h3>Business Details</h3>
            <div class="grid">
                <div class="field">
                    <label>Legal Name</label>
                    <input id="legal-name" type="text" value="EcoBottle Ltd" />
                </div>
                <div class="field">
                    <label>Registration No.</label>
                    <input id="reg-no" type="text" value="SC123456" />
                </div>
                <div class="field">
                    <label>Sector</label>
                    <select id="sector">
                        <option>Consumer Goods</option>
                        <option selected>Greentech</option>
                        <option>Food & Beverage</option>
                        <option>Fintech</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="field">
                    <label>Website</label>
                    <input id="website" type="url" value="https://ecobottle.co.uk" />
                </div>
                <div class="field wide">
                    <label>Registered Address</label>
                    <input id="address" type="text" value="12 Green Lane, Edinburgh, EH1 2AB" />
                </div>
                <div class="field wide">
                    <label>Company Description</label>
                    <textarea id="description"
                        rows="3">We design connected, reusable bottles that help people stay hydrated while reducing single-use plastics.</textarea>
                </div>
            </div>
            <div class="actions">
                <button class="btn primary" id="save-details">Save Details</button>
            </div>
        </section>

        <!-- Team members -->
        <section class="card">
            <h3>Team Members</h3>
            <ul class="team-list" id="team-list">
                <li><strong>Alex Johnson</strong> — CEO</li>
                <li><strong>Sara Patel</strong> — COO</li>
                <li><strong>Jamie Lee</strong> — Head of Product</li>
            </ul>
            <div class="inline">
                <input id="team-name" type="text" placeholder="Full name" />
                <input id="team-role" type="text" placeholder="Role (e.g CEO)" />
                <button class="btn" id="add-team">Add Member</button>
            </div>
        </section>

        <!-- Banking -->
        <section class="card">
            <h3>Banking (Mock)</h3>
            <div class="grid">
                <div class="field">
                    <label>Account Name</label>
                    <input id="acct-name" type="text" value="EcoBottle Ltd" />
                </div>
                <div class="field">
                    <label>Sort Code</label>
                    <input id="sort-code" type="text" placeholder="12-34-56" />
                </div>
                <div class="field">
                    <label>Account Number</label>
                    <input id="acct-number" type="text" placeholder="12345678" />
                </div>
                <div class="field">
                    <label>Payout Preference</label>
                    <select id="payout-pref">
                        <option selected>Manual Payout</option>
                        <option>Auto-Deposit on Profit Distribution</option>
                    </select>
                </div>
            </div>
            <p class="hint">Note: This is a simulated setup for demos; no real payments are processed.</p>
            <div class="actions">
                <button class="btn" id="save-banking">Save Banking</button>
            </div>
        </section>

        <!-- verification -->
        <section class="card">
            <h3>Verification</h3>
            <p>Status: <span class="badge verified" id="kyc-status">Verified</span></p>
            <ul class="doc-list" id="doc-list">
                <li><a href="#" target="_blank">Certificate of Incorporation.pdf</a></li>
                <li><a href="#" target="_blank">Proof of Address.pdf</a></li>
            </ul>
            <button class="btn small" id="upload-doc">Upload Document</button>
        </section>

        <!-- account actions -->
        <section class="card danger-zone">
            <h3>Danger Zone</h3>
            <p>Closing your business account removes access to all pitches and funds. This cannot be undone.</p>
            <button class="btn danger outline" id="close-account">Close Business Account</button>
        </section>
    </main>

    <!-- Footer -->
     <?php include '../footer.php'; ?>

    <script src="business_profile.js"></script>
</body>

</html>
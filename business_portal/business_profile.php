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
        <h2>My Account</h2>
        <section class="card summary">
            <div class="avatar placeholder-avatar">
                <!-- No image, just grey block -->
            </div>
            <div class="info">
                <h3 id="company-name"><?php echo htmlspecialchars($businessName ?? 'N/A'); ?></h3>
                <p id="company-email"><?php echo htmlspecialchars($businessEmail ?? 'N/A'); ?></p>
                <button class="btn small" id="change-logo" disabled>Change Logo</button>
            </div>


        </section>

        <!-- company details -->
        <section class="card">
            <h3>Business Details</h3>
            <p class="placeholder-note">This is a placeholder.</p>
            <div class="grid">
                <div class="field">
                    <label>Legal Name</label>
                    <input id="legal-name" type="text" value="My Company Ltd" disabled />
                </div>
                <div class="field">
                    <label>Registration No.</label>
                    <input id="reg-no" type="text" value="SC123456" disabled />
                </div>
                <div class="field">
                    <label>Sector</label>
                    <select id="sector" disabled>
                        <option>Consumer Goods</option>
                        <option selected>Greentech</option>
                        <option>Food & Beverage</option>
                        <option>Fintech</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="field">
                    <label>Website</label>
                    <input id="website" type="url" value="https://mycompany.co.uk" disabled />
                </div>
                <div class="field wide">
                    <label>Registered Address</label>
                    <input id="address" type="text" value="12 Green Lane, Edinburgh, EH1 2AB" disabled />
                </div>
                <div class="field wide">
                    <label>Company Description</label>
                    <textarea id="description" rows="3" disabled>Enter a short description...</textarea>
                </div>
            </div>
            <div class="actions">
                <button class="btn primary" id="save-details" disabled>Save Details</button>
            </div>
        </section>

        <!-- Banking -->
        <section class="card">
            <h3>Banking (Mock)</h3>
            <p class="placeholder-note">This is a placeholder.</p>
            <div class="grid">
                <div class="field">
                    <label>Account Name</label>
                    <input id="acct-name" type="text" value="My Company Ltd" disabled />
                </div>
                <div class="field">
                    <label>Sort Code</label>
                    <input id="sort-code" type="text" placeholder="12-34-56" disabled />
                </div>
                <div class="field">
                    <label>Account Number</label>
                    <input id="acct-number" type="text" placeholder="12345678" disabled />
                </div>
                <div class="field">
                    <label>Payout Preference</label>
                    <select id="payout-pref" disabled>
                        <option selected>Manual Payout</option>
                        <option>Auto-Deposit on Profit Distribution</option>
                    </select>
                </div>
            </div>
            <p class="hint">Note: This is a simulated setup for demos; no real payments are processed.</p>
            <div class="actions">
                <button class="btn" id="save-banking" disabled>Save Banking</button>
            </div>
        </section>

        <!-- verification -->
        <section class="card readonly">
            <h3>Verification</h3>
            <p class="placeholder-note">This is a placeholder. Verification coming soon...</p>
            <p>Status: <span class="badge verified" id="kyc-status">Verified</span></p>
            <ul class="doc-list" id="doc-list">
                <li><a href="#" target="_blank" class="disabled-link">Certificate of Incorporation.pdf</a></li>
                <li><a href="#" target="_blank" class="disabled-link">Proof of Address.pdf</a></li>
            </ul>
            <button class="btn small" id="upload-doc" disabled>Upload Document</button>
        </section>


        <!-- Danger Zone Section -->
        <section class="card danger-zone">
            <h3>Danger Zone</h3>
            <p>Closing your business account removes access to all pitches and funds. This cannot be undone.</p>
            <button class="btn danger outline" id="close-account">Close Business Account</button>
        </section>

        <!-- Modal -->
        <div id="contactModal" class="modal">
            <div class="modal-content">
                <h3>Contact Support</h3>
                <p>To delete your account, please contact support at <strong>fundify@support.com</strong>.</p>
                <div class="modal-actions">
                    <button class="btn primary" id="modal-ok">OK</button>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <?php include '../footer.php'; ?>
    <script src="business_profile.js?v=<?php echo time(); ?>"></script>
</body>

</html>
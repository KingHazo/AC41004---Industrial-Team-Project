<?php 
// start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security check
if (!isset($_SESSION['logged_in']) || $_SESSION['userType'] !== 'investor') {
    header("Location: ../login/login_signup.php");
    exit();
}

// include database connection
include '../sql/db.php'; 

$investorID = $_SESSION['userId'];
$investorData = [];

try {
    // get all profile data
    $sql = "SELECT Name, Email, InvestorBalance, Address, DateOfBirth, Nationality, PreferredCurrency 
            FROM Investor 
            WHERE InvestorID = :investorID";
    $stmt = $mysql->prepare($sql);
    $stmt->bindParam(':investorID', $investorID);
    $stmt->execute();
    $investorData = $stmt->fetch(PDO::FETCH_ASSOC);

    // if data not found, redirect
    if (!$investorData) {
        session_destroy();
        header("Location: ../login/login_signup.php");
        exit();
    }
    
    // Format DateOfBirth for HTML input (YYYY-MM-DD)
    $formattedDOB = $investorData['DateOfBirth'] ? date('Y-m-d', strtotime($investorData['DateOfBirth'])) : '';
    // Format DateOfBirth for display (e.g., 05 May 1992)
    $displayDOB = $investorData['DateOfBirth'] ? date('d M Y', strtotime($investorData['DateOfBirth'])) : 'Not provided';
    
} catch (PDOException $e) {
    error_log("Database Fetch Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Investor Profile</title>
    <link rel="stylesheet" href="investor_profile.css" />
    <link rel="stylesheet" href="../footer.css" />
    <link rel="stylesheet" href="../navbar.css" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
</head>

<body>
    <!-- Navbar -->
      <?php include '../navbar.php'; ?>

     <main class="section">
        <h2>My Profile</h2>
        <section class="card summary">
            <div class="avatar">
                <img src="corporate-headshot.jpg" alt="Profile Photo">
            </div>
            
            <div class="info">
                <h3 id="full-name"><?php echo htmlspecialchars($investorData['Name'] ?? 'N/A'); ?></h3>
                <p id="email"><?php echo htmlspecialchars($investorData['Email'] ?? 'N/A'); ?></p>
                <button class="btn small" id="change-photo">Change Photo</button>
            </div>
        </section>
        <section class="card wallet">
            <h3>Account Wallet</h3>
            <div class="balance-info">
                <p class="kpi-label">Current Balance:</p>
                <p class="kpi-value" id="investor-balance">
                    <?php echo htmlspecialchars($investorData['PreferredCurrency'] === 'USD' ? '$' : '£'); ?>
                    <?php echo number_format($investorData['InvestorBalance'] ?? 0.00, 2); ?>
                </p>
            </div>
            <button class="btn primary small" id="add-funds-btn">Add Funds</button>
        </section>

        <section class="card stats">
            <h3>Investment Stats</h3>
            <div class="kpi-grid">
                <div class="kpi-card">
                    <p class="kpi-label">Total Invested</p>
                    <p class="kpi-value" id="stat-invested">£9,500</p>
                </div>
                <div class="kpi-card">
                    <p class="kpi-label">Total Returns</p>
                    <p class="kpi-value good" id="stat-returns">£1,320</p>
                </div>
                <div class="kpi-card">
                    <p class="kpi-label">Active Pitches</p>
                    <p class="kpi-value" id="stat-active">3</p>
                </div>
                <div class="kpi-card">
                    <p class="kpi-label">Closed Pitches</p>
                    <p class="kpi-value" id="stat-closed">5</p>
                </div>
            </div>
        </section>

        <section class="card details">
            <h3>Personal Information</h3>
            
            <div id="display-details">
                <div class="detail-row"><span class="label">Address:</span> <span id="display-address"><?php echo htmlspecialchars($investorData['Address'] ?? 'Not provided'); ?></span></div>
                <div class="detail-row"><span class="label">Date of Birth:</span> <span id="display-dob"><?php echo htmlspecialchars($displayDOB); ?></span></div>
                <div class="detail-row"><span class="label">Nationality:</span> <span id="display-nationality"><?php echo htmlspecialchars($investorData['Nationality'] ?? 'Not provided'); ?></span></div>
                <div class="detail-row"><span class="label">Preferred Currency:</span> <span id="display-currency"><?php echo htmlspecialchars($investorData['PreferredCurrency'] ?? 'GBP'); ?> (<?php echo htmlspecialchars($investorData['PreferredCurrency'] === 'USD' ? '$' : '£'); ?>)</span></div>
                
                <button class="btn small primary" id="edit-details-btn">Edit Details</button>
            </div>

            <form id="inline-edit-form" style="display:none;">
                
                <div class="form-group detail-row">
                    <label class="label" for="edit-address-inline">Address</label>
                    <input type="text" id="edit-address-inline" name="address" value="<?php echo htmlspecialchars($investorData['Address'] ?? ''); ?>">
                </div>

                <div class="form-group detail-row">
                    <label class="label" for="edit-dob-inline">Date of Birth</label>
                    <input type="date" id="edit-dob-inline" name="dob" value="<?php echo htmlspecialchars($formattedDOB); ?>"> 
                </div>

                <div class="form-group detail-row">
                    <label class="label" for="edit-nationality-inline">Nationality</label>
                    <input type="text" id="edit-nationality-inline" name="nationality" value="<?php echo htmlspecialchars($investorData['Nationality'] ?? ''); ?>">
                </div>

                <div class="form-group detail-row">
                    <label class="label" for="edit-currency-inline">Preferred Currency</label>
                    <select id="edit-currency-inline" name="currency">
                        <option value="GBP" <?php if (($investorData['PreferredCurrency'] ?? 'GBP') === 'GBP') echo 'selected'; ?>>GBP (£)</option>
                        <option value="USD" <?php if (($investorData['PreferredCurrency'] ?? '') === 'USD') echo 'selected'; ?>>USD ($)</option>
                        <option value="EUR" <?php if (($investorData['PreferredCurrency'] ?? '') === 'EUR') echo 'selected'; ?>>EUR (€)</option>
                    </select>
                </div>

                <div class="form-actions" style="margin-top: 15px;">
                    <button type="submit" class="btn primary small">Save Changes</button>
                    <button type="button" class="btn outline small" id="cancel-edit-btn">Cancel</button>
                </div>
            </form>
        </section>

        <section class="card documents">
            <h3>Documents</h3>
            <ul class="doc-list">
                <li><a href="#" target="_blank">Proof of ID.pdf</a></li>
                <li><a href="#" target="_blank">Address Verification.pdf</a></li>
            </ul>
            <button class="btn small" id="upload-docs">Upload New Document</button>
        </section>

        <section class="card actions-card">
            <h3>Account Actions</h3>
            <div class="actions">
                <!-- <button class="btn primary" id="edit-profile">Edit Profile</button> -->
                <button class="btn danger outline" id="deactivate-account">Deactivate Account</button>
            </div>
        </section>
    </main>
    
    <div id="edit-profile-modal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h4>Edit Personal Information</h4>
            <form id="edit-profile-form">   
                <div class="form-group">
                    <label for="edit-name">Full Name</label>
                    <input type="text" id="edit-name" name="name" 
                    value="<?php echo htmlspecialchars($investorData['Name'] ?? ''); ?>" required>
                </div>
                <hr>
                <div class="form-group">
                    <label for="edit-address">Address</label>
                    <input type="text" id="edit-address" name="address" 
                    value="<?php echo htmlspecialchars($investorData['Address'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="edit-dob">Date of Birth</label>
                    <input type="date" id="edit-dob" name="dob" 
                    value="<?php echo htmlspecialchars($formattedDOB); ?>"> 
                </div>

                <div class="form-group">
                    <label for="edit-nationality">Nationality</label>
                    <input type="text" id="edit-nationality" name="nationality" 
                        value="<?php echo htmlspecialchars($investorData['Nationality'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="edit-currency">Preferred Currency</label>
                    <select id="edit-currency" name="currency">
                        <option value="GBP" <?php if (($investorData['PreferredCurrency'] ?? 'GBP') === 'GBP') echo 'selected'; ?>>GBP (£) - British Pound</option>
                        <option value="USD" <?php if (($investorData['PreferredCurrency'] ?? '') === 'USD') echo 'selected'; ?>>USD ($) - US Dollar</option>
                        <option value="EUR" <?php if (($investorData['PreferredCurrency'] ?? '') === 'EUR') echo 'selected'; ?>>EUR (€) - Euro</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn primary">Save Changes</button>
                    <button type="button" class="btn outline close-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deposit-modal" class="modal-overlay">
        <div class="modal-content card">
            <h3>Deposit Funds</h3>
            
            <div class="form-grid">
                
                <div class="field wide">
                    <label for="bank-account-number">Account Number</label>
                    <input type="text" id="bank-account-number" placeholder="12345678" required>
                </div>
                
                <div class="field wide">
                    <label for="bank-holder-name">Holder Name</label>
                    <input type="text" id="bank-holder-name" placeholder="Name" required>
                </div>
                
                <div class="field wide">
                    <label for="deposit-amount">Deposit Amount (£)</span></label>
                    <input type="number" id="deposit-amount" min="100" placeholder="e.g., 500.00" required>
                </div>
            </div>
            
            <div class="actions">
                <button class="btn" id="cancel-deposit-btn">Cancel</button>
                <button class="btn primary" id="confirm-deposit-btn">Deposit</button>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
     <?php include '../footer.php'; ?>
    <script src="investor_profile.js"></script>
</body>

</html>
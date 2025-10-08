<?php session_start();

// start the session to get current business
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// make sure user is logged in and is a business
if (!isset($_SESSION['logged_in']) || $_SESSION['userType'] !== 'investor') {
    header("Location: ../login/login_signup.php");
    exit();
}

// include database connection
include '../sql/db.php';

if (!$mysql) {
    die("Database connection failed.");
}


$selected_tag_ids_raw = filter_input(INPUT_GET, 'tag_id', FILTER_DEFAULT) ?? '0';

$selected_tag_ids_array = array_filter(
    array_map('intval', explode(',', $selected_tag_ids_raw)),
    fn($id) => $id >= 0
);

if (count($selected_tag_ids_array) > 1 && in_array(0, $selected_tag_ids_array)) {
    $selected_tag_ids_array = array_filter($selected_tag_ids_array, fn($id) => $id > 0);
}

if (empty($selected_tag_ids_array)) {
    $selected_tag_ids_array = [0];
}

$selected_tag_ids = $selected_tag_ids_array;

$search_term = filter_input(INPUT_GET, 'search_term', FILTER_SANITIZE_STRING) ?? '';

$all_tags = [];
$all_tags[] = ['TagID' => 0, 'Name' => 'All'];

try {
    $tag_sql = "SELECT TagID, Name FROM Tag ORDER BY Name";
    $tag_stmt = $mysql->query($tag_sql);
    $db_tags = $tag_stmt->fetchAll(PDO::FETCH_ASSOC);
    $all_tags = array_merge($all_tags, $db_tags);
} catch (PDOException $e) {
    error_log("Tag Load Query Error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investor Portal Home</title>
    <link rel="stylesheet" href="investor_portal_home.css">
    <link rel="stylesheet" href="../footer.css">
    <script src="https://kit.fontawesome.com/004961d7c9.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .filter-tag.selected {
            background-color: #0b3d91;
            /* blue fill color when selected */
            color: white;
            border-color: #0b3d91;
        }
    </style>
</head>

<body>

    <?php include '../navbar.php'; ?>

    <!-- discover new pitches section-->
    <section id="discover" class="section">
        <h2>Discover New Pitches</h2>
        <div class="search-filter">
            <input type="text" placeholder="Search pitches..." value="<?php echo htmlspecialchars($search_term); ?>" id="searchInput">
            <button class="filter-btn" id="filterButton" aria-label="Filter">
                <i class="fa-solid fa-filter"></i>
            </button>
            </button>
        </div>

        <div class="tag-filters-container" id="tagFiltersContainer" style="display: none;">
            <div class="active-filters" id="pitch-tags">
                <?php foreach ($all_tags as $tag):
                    if (!is_array($tag) || !isset($tag['TagID']) || !isset($tag['Name'])) {
                        error_log("Skipping malformed tag data: " . var_export($tag, true));
                        continue;
                    }

                    $is_selected = in_array($tag['TagID'], $selected_tag_ids) ? ' selected' : '';
                ?>
                    <button class="filter-tag<?php echo $is_selected; ?>" data-tag="<?php echo htmlspecialchars($tag['TagID']); ?>">
                        <?php echo htmlspecialchars($tag['Name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="pitches">
            <?php
            try {
                $sql = "SELECT 
            p.PitchID, 
            p.Title, 
            p.ElevatorPitch, 
            p.CurrentAmount, 
            p.TargetAmount, 
            p.ProfitSharePercentage 
            FROM Pitch p";

                $where_conditions = ["p.Status NOT IN ('draft', 'closed')"]; // always exclude drafts and closed pitches
                $bind_values = [];

                $tag_filter_needed = !in_array(0, $selected_tag_ids);
                $text_search_needed = !empty($search_term);

                if ($tag_filter_needed) {
                    $placeholders = implode(',', array_fill(0, count($selected_tag_ids), '?'));
                    $where_conditions[] = "EXISTS (
                SELECT 1 
                FROM PitchTag pt 
                WHERE pt.PitchID = p.PitchID 
                AND pt.TagID IN ($placeholders)
            )";
                    $bind_values = array_merge($bind_values, $selected_tag_ids);
                }

                if ($text_search_needed) {
                    $where_conditions[] = "(p.Title LIKE ? OR p.ElevatorPitch LIKE ?)";
                    $search_param = '%' . $search_term . '%';
                    $bind_values[] = $search_param;
                    $bind_values[] = $search_param;
                }

                // combine everything into one WHERE clause
                $sql .= " WHERE " . implode(' AND ', $where_conditions);
                $sql .= " ORDER BY p.PitchID DESC";


                $stmt = $mysql->prepare($sql);

                // bind all values collected from both tags and search
                if (!empty($bind_values)) {
                    foreach ($bind_values as $index => $value) {
                        $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                        $stmt->bindValue($index + 1, $value, $param_type);
                    }
                }

                $stmt->execute();

                // make a card for each pitch
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    $pitch_id = $row['PitchID'];

                    if (empty($pitch_id)) {
                        error_log("Skipping pitch card due to missing PitchID in database record.");
                        continue;
                    }

                    // stop division by zero error
                    $currentAmount = $row['CurrentAmount'] ?? 0;
                    $targetAmount = $row['TargetAmount'] ?? 1;

                    $progress_percentage = ($currentAmount / $targetAmount) * 100;
                    $progress_percentage = min($progress_percentage, 100); // Cap at 100%

            ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($row['Title'] ?? 'N/A'); ?></h3>
                        <p><?php echo htmlspecialchars($row['ElevatorPitch'] ?? 'N/A'); ?></p>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?php echo $progress_percentage; ?>%;">
                                £<?php echo number_format($currentAmount); ?> / £<?php echo number_format($targetAmount); ?>
                            </div>
                        </div>
                        <div class="profit-share">
                            Investor Profit Share: <strong><?php echo htmlspecialchars($row['ProfitSharePercentage'] ?? '0'); ?>%</strong>
                        </div>
                        <div class="card-buttons">
                            <?php if ($currentAmount < $targetAmount): ?>
                                <button class="invest-btn">Invest</button>
                            <?php endif; ?>

                            <button class="more-btn" data-pitch-id="<?php echo htmlspecialchars($pitch_id); ?>">Find Out More</button>
                        </div>


                    </div>
            <?php
                }
            } catch (PDOException $e) {
                // if the query fails
                echo "<p style='color: red; text-align: center; margin-top: 20px;'>Error loading pitches: " . htmlspecialchars($e->getMessage()) . "</p>";
                error_log("Pitch Load Query Error: " . $e->getMessage());
            }
            ?>
        </div>
    </section>

    <?php include '../footer.php'; ?>
    <script src="load_investor_navbar.js"></script>
    <script src="../load-footer.js"></script>
    <script src="investor_portal_home.js"></script>
</body>

</html>
<html>
<head>
</head>
<body>
<?php
echo "AC41004 Industrial Team Project <br>";
$name = "Drew Webster";
echo "My name is ".$name."<br>";

include 'db.php';

try {
    // test connection
    $stmt = $mysql->query("SELECT * FROM Pitch");

    echo "My name is ".$name."<br>";
    echo "Displaying data from the 'pitch' table:<br>";

    // error check just to make sure its connecting
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo " - " . htmlspecialchars($row['Title']) . "<br>";

    }
} catch (PDOException $e) {
    echo "Error querying the database: " . $e->getMessage();
}
?>
<br>
<a href="login.html">Go to the Login Page</a>
<a href="investor-portal-home.php">Go to the Login Page</a>
</body>
</html>
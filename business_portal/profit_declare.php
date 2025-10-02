<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profit Declaration</title>
  <link rel="stylesheet" href="profit_declare.css">
  <link rel="stylesheet" href="../footer.css">
  <link rel="stylesheet" href="../navbar.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <!-- Header -->
     <?php include '../navbar.php'; ?>

  <!-- Profit declaration section -->
  <main class="section">
    <div class="profit-card">
      <h2>Declare Profits – EcoBottle Pitch</h2>
      <p><strong>Status:</strong> Funded</p>
      <p><strong>Raised:</strong> £10,000 | <strong>Investor Share:</strong> 20%</p>

      <form class="profit-form">
        <!-- profit input -->
        <label for="profit">Total Profit (£)</label>
        <input type="number" id="profit" placeholder="Enter profit amount" required>


        <label for="distributable">Distributable Profit (£)</label>
        <input type="text" id="distributable" readonly value="£0">

        <div class="form-buttons">
          <button type="submit" class="submit-btn">Distribute Profits</button>
        </div>
      </form>
    </div>
  </main>
   <?php include '../footer.php'; ?>
  <script src="profit_declare.js"></script>
</body>

</html>
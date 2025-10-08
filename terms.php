<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions – Fundify</title>

    <!-- Global Styles -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">

    <!-- Inline Page-Specific Styles -->
    <style>
        /* === Terms & Conditions Page === */

        .terms-wrapper {
            background: #f8f9fc;
        }

        .terms-header {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 2rem auto;
        }

        .terms-header h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.75rem;
        }

        .terms-header p {
            font-size: 1rem;
            color: #555;
        }

        .terms-content {
            max-width: 900px;
            background: #fff;
            padding: 2rem;
            margin: 0 auto 3rem auto;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            line-height: 1.65;
        }

        .terms-content h2 {
            color: #0b3d91;
            font-weight: 600;
            font-size: 1.25rem;
            margin-top: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .terms-content p {
            color: #444;
            margin-bottom: 1rem;
            font-size: 0.98rem;
        }

        .terms-content a {
            color: #0b3d91;
            text-decoration: none;
            font-weight: 500;
        }

        .terms-content a:hover {
            text-decoration: underline;
        }

        /* Small-screen adjustments */
        @media (max-width: 600px) {
            .terms-content {
                padding: 1.25rem;
            }

            .terms-header h1 {
                font-size: 2rem;
            }
        }
    </style>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <?php include 'navbar.php'; ?>

    <main class="terms-wrapper">
        <header class="terms-header section">
            <h1>Terms & Conditions</h1>
            <p>Welcome to Fundify. Please read the following terms carefully before using our platform.</p>
        </header>

        <section class="terms-content section">
            <h2>1. Introduction</h2>
            <p>
                By accessing and using Fundify’s services, you agree to comply with and be bound by these Terms & Conditions.
                If you do not agree, you may not use our platform.
            </p>

            <h2>2. Eligibility</h2>
            <p>
                Users must be at least 18 years old and capable of entering into legally binding agreements to use our services.
            </p>

            <h2>3. User Accounts</h2>
            <p>
                You are responsible for maintaining the confidentiality of your account credentials and for all activities
                under your account.
            </p>

            <h2>4. Investment Risks</h2>
            <p>
                Investments made through Fundify carry inherent financial risks. Fundify does not guarantee returns or
                the success of any pitch. Please invest responsibly.
            </p>

            <h2>5. Profit Sharing</h2>
            <p>
                Profit distribution is determined by each pitch’s agreed terms and tiers. Fundify is not responsible
                for disputes between investors and businesses regarding profit allocation.
            </p>

            <h2>6. AI-Assisted Feedback</h2>
            <p>
                Our AI pitch analysis is provided as guidance only and does not replace professional advice. Fundify
                makes no guarantees on the accuracy of AI recommendations.
            </p>

            <h2>7. Termination</h2>
            <p>
                Fundify reserves the right to suspend or terminate accounts found violating these terms or engaging in
                fraudulent activity.
            </p>

            <h2>8. Changes to Terms</h2>
            <p>
                We may update these Terms & Conditions periodically. Continued use of the platform indicates acceptance
                of the revised terms.
            </p>

            <h2>9. Contact Us</h2>
            <p>
                If you have any questions about these Terms & Conditions, please contact us at
                <a href="mailto:support@fundify.com">support@fundify.com</a>.
            </p>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script src="load_navbar.js"></script>
    <script src="load_footer.js"></script>

</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Us – Fundify</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">

    <style>
        body {
            background: #f8f9fc;
            font-family: 'Montserrat', sans-serif;
        }

        .contact-wrapper {
            padding: 3rem 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .contact-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .contact-header h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .contact-header p {
            color: #555;
            font-size: 1rem;
        }

        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: start;
        }

        .contact-info {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            line-height: 1.6;
            border: 1px solid rgba(11, 61, 145, 0.06);
        }

        .contact-info h2 {
            font-size: 1.4rem;
            color: #0b3d91;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .contact-info p {
            margin-bottom: 1rem;
            font-size: 0.95rem;
            color: #444;
        }

        .contact-info a {
            color: #0b3d91;
            font-weight: 600;
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        .contact-form {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(11, 61, 145, 0.06);
        }

        .contact-form h2 {
            font-size: 1.4rem;
            color: #0b3d91;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .contact-form form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .contact-form label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: #0b3d91;
            box-shadow: 0 0 6px rgba(11, 61, 145, 0.25);
        }

        .contact-form textarea {
            resize: vertical;
            min-height: 120px;
        }

        .contact-form button {
            align-self: flex-start;
            background: #0b3d91;
            color: #fff;
            border: none;
            padding: 0.8rem 1.6rem;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s;
        }

        .contact-form button:hover {
            background: #072a61;
        }

        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <main class="contact-wrapper">
        <div class="contact-header">
            <h1>Contact Us</h1>
            <p>We’d love to hear from you. Whether you’re a business, an investor, or just curious about Fundify, we’re here to help.</p>
        </div>

        <div class="contact-container">
            <div class="contact-info">
                <h2>Our Office</h2>
                <p>Fundify<br>67 Dundee Road, Edinburgh, UK</p>

                <h2>Email</h2>
                <p>General: <a href="mailto:info@fundify.com">info@fundify.com</a><br>
                    Support: <a href="mailto:support@fundify.com">support@fundify.com</a></p>

                <h2>Phone</h2>
                <p>+44 073456789<br>(Mon – Fri, 9am – 5pm)</p>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <h2>Send us a message</h2>
                <form action="#" method="post">
                    <div>
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your name" required>
                    </div>

                    <div>
                        <label for="email">Your Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div>
                        <label for="message">Message</label>
                        <textarea id="message" name="message" placeholder="Type your message..." required></textarea>
                    </div>

                    <button type="submit">Send Message</button>
                </form>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="load_navbar.js"></script>
    <script src="load_footer.js"></script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Fundify</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="footer.css">

    <link rel="stylesheet" href="about.css">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <?php include 'navbar.php'; ?>

    <main>

        <header class="about-hero section reveal">
            <h1>About Fundify</h1>
            <p>
                Fundify connects bold local businesses with community investors. We focus on clarity and fairness:
                real targets, transparent progress, and simple profit-sharing that everyone understands.
            </p>
        </header>

        <section class="section reveal">
            <h2>Our Values</h2>
            <div class="info-cards stagger">
                <article class="card">
                    <h3>Transparency</h3>
                    <p>Clear funding targets, visible progress, and straightforward profit-sharing.</p>
                </article>
                <article class="card">
                    <h3>Fairness</h3>
                    <p>Balanced incentives for both founders and investors with tiered participation.</p>
                </article>
                <article class="card">
                    <h3>Practical Innovation</h3>
                    <p>AI assists the pitch process without replacing sound business thinking.</p>
                </article>
            </div>
        </section>

        <section class="section reveal">
            <h2>How It Works</h2>
            <div class="info-cards stagger">
                <article class="card">
                    <h3>1) Create a Pitch</h3>
                    <p>Founders describe their product, targets, timeline, and proposed profit-share.</p>
                </article>
                <article class="card">
                    <h3>2) Get Funded</h3>
                    <p>Investors back pitches they believe in; progress is tracked transparently.</p>
                </article>
                <article class="card">
                    <h3>3) Share Profits</h3>
                    <p>Once revenue arrives, profits are distributed according to tiered shares.</p>
                </article>
            </div>
        </section>

        <section class="section reveal">
            <h2>Meet the Team</h2>
            <div class="info-cards stagger">
                <article class="card team-card">
                    <h3>Alex Johnson – CEO</h3>
                    <p>Product & partnerships. Loves simple products that solve real problems.</p>
                </article>
                <article class="card team-card">
                    <h3>Sara Patel – COO</h3>
                    <p>Operations & risk. Ensures workflows are efficient and compliant.</p>
                </article>
                <article class="card team-card">
                    <h3>Jamie Lee – Head of Product</h3>
                    <p>Design & UX. Focused on clarity, accessibility, and user trust.</p>
                </article>
            </div>
        </section>

        <section class="section reveal">
            <h2>FAQ</h2>
            <div class="faq-card">
                <details>
                    <summary><strong>Is AI feedback required for every pitch?</strong></summary>
                    <p>AI feedback is encouraged to improve clarity, but it’s not mandatory for submission.</p>
                </details>
                <hr>
                <details>
                    <summary><strong>Can investors cancel before a pitch succeeds?</strong></summary>
                    <p>Yes, until the funding window closes or the target is reached.</p>
                </details>
                <hr>
                <details>
                    <summary><strong>Which devices are supported?</strong></summary>
                    <p>Fundify is designed for both desktop and mobile use</p>
                </details>
            </div>
        </section>

        <section class="section cta reveal">
            <div class="cta-box">
                <h3>Have an idea worth funding?</h3>
                <p>Join Fundify and share your pitch with our community of investors.</p>
                <button onclick="window.location.href='login/login_signup.php?type=business'">Get Started</button>
            </div>
        </section>

    </main>

    <?php include 'footer.php'; ?>

    <script src="load_navbar.js"></script>
    <script src="load_footer.js"></script>

    <script>
        (function() {
            const blocks = document.querySelectorAll('.reveal');
            const obs = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        e.target.classList.add('in');
                        obs.unobserve(e.target);
                    }
                });
            }, {
                threshold: 0.12
            });
            blocks.forEach(b => obs.observe(b));
        })();
    </script>
</body>

</html>
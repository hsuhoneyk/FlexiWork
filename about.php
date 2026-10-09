<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About — Flexiwork</title>

    <!-- Fonts: Newsreader (classic serif) + Inter (modern sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="stylesheet" href="style.css">
</head>

<body class="about-page">

    <header>
        <nav class="wrap" aria-label="Main navigation">

            <a href="index.php" class="logo">Flexi<span class="chip">Work</span></a>

            <div class="nav-links">

                <?php if (!isset($_SESSION['user_id'])): ?>

                    <a href="index.php">Home</a>
                    <a href="categories.php" >Find Jobs</a>
                    <a href="about.php" class="active">About</a>
                    <a href="contact.php">Contact Us</a>

                <?php elseif ($_SESSION['role'] === 'jobseeker'): ?>

                    <a href="index.php">Home</a>
                    <a href="categories.php">Find Jobs</a>
                    <a href="applications.php">My Applications</a>
                    <a href="about.php" class="active">About</a>
                    <a href="contact.php">Contact Us</a>
                    <a href="jobseeker_profile.php">Profile</a>

                <?php elseif ($_SESSION['role'] === 'employer'): ?>

                    <a href="employer_jobs.php">Dashboard</a>
                    <a href="employer_applications.php">Applicants</a>
                    <a href="Post_job.php">Post</a>
                    <a href="about.php" class="active">About</a>
                    <a href="contact.php">Contact Us</a>
                    <a href="employer_profile.php">Profile</a>

                <?php elseif ($_SESSION['role'] === 'admin'): ?>

                    <a href="admin_dashboard.php">Dashboard</a>
                    <a href="admin_categories.php">Categories</a>
                    <a href="admin_users.php">Users</a>
                    <a href="admin_settings.php">Settings</a>

                <?php endif; ?>

            </div>

            <div class="nav-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="user-pill">
                        Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>
                    </span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="signin">Log in</a>
                    <a href="register.php" class="btn btn-primary">Get started</a>
                <?php endif; ?>
            </div>

            <button class="mobile-toggle" aria-label="Open menu" type="button">
                <span></span>
                <span></span>
                <span></span>
            </button>

        </nav>
    </header>

    <main>

        <!-- =====================================================
             HERO
        ===================================================== -->
        <section class="about-hero">
            <div class="about-wrap about-hero-grid">

                <div class="about-hero-copy">
                    <h1>Work should fit your life.</h1>

                    <p>
                        FlexiWork connects flexible workers with businesses
                        that need reliable people, making part-time work
                        simpler, faster, and more accessible.
                    </p>

                    <div class="about-hero-actions">
                        <a href="index.php" class="about-btn about-btn-gold">Find shifts</a>
                        <a href="register.php" class="about-btn about-btn-line">Post a shift</a>
                    </div>
                </div>

                <!-- Decorative week: booked shifts and open time -->
                <div class="week" aria-hidden="true">
                    <div class="week-grid">
                        <div class="week-day">
                            <span>Mon</span>
                            <div class="week-track"><i style="--top:8%; --h:30%"></i></div>
                        </div>
                        <div class="week-day">
                            <span>Tue</span>
                            <div class="week-track"><i class="open" style="--top:38%; --h:26%"></i></div>
                        </div>
                        <div class="week-day">
                            <span>Wed</span>
                            <div class="week-track"><i style="--top:44%; --h:32%"></i></div>
                        </div>
                        <div class="week-day">
                            <span>Thu</span>
                            <div class="week-track">
                                <i style="--top:8%; --h:22%"></i>
                                <i style="--top:58%; --h:28%"></i>
                            </div>
                        </div>
                        <div class="week-day">
                            <span>Fri</span>
                            <div class="week-track"><i class="open" style="--top:16%; --h:30%"></i></div>
                        </div>
                        <div class="week-day">
                            <span>Sat</span>
                            <div class="week-track"><i style="--top:20%; --h:52%"></i></div>
                        </div>
                        <div class="week-day">
                            <span>Sun</span>
                            <div class="week-track"><i class="open" style="--top:30%; --h:34%"></i></div>
                        </div>
                    </div>

                    <div class="week-legend">
                        <span><b></b>Shift booked</span>
                        <span><b class="open"></b>Open time</span>
                    </div>
                </div>

            </div>
        </section>

        <!-- =====================================================
             OUR STORY
        ===================================================== -->
        <section class="about-story">
            <div class="about-wrap about-story-layout">

                <div class="about-story-intro">
                    <h2 class="about-title">A simpler way to find flexible work.</h2>
                    <p class="about-lead">
                        We believe finding part-time work should be as easy
                        as finding something that fits your schedule.
                    </p>
                </div>

                <div class="story-list">

                    <div class="story-block">
                        <h3>Why we started</h3>
                        <p>
                            FlexiWork began with a simple idea: finding
                            part-time, flexible work should not be difficult,
                            and hiring for it should not be slow.
                        </p>
                        <p>
                            We built a platform where job seekers can search
                            and apply in minutes, while employers can post
                            opportunities and start receiving applicants
                            quickly.
                        </p>
                    </div>

                    <div class="story-block">
                        <h3>What we offer</h3>
                        <p>
                            Whether you are a student looking for weekend
                            shifts, someone building a second income, or a
                            business owner who needs reliable part-time help,
                            FlexiWork keeps the process simple, transparent,
                            and fast.
                        </p>
                    </div>

                    <div class="story-block">
                        <h3>Our values</h3>
                        <p>
                            We believe flexible work should still mean fair
                            pay, clear expectations, and respectful employers.
                            Every listing on FlexiWork is reviewed to help
                            keep the platform trustworthy for everyone.
                        </p>
                    </div>

                </div>

            </div>
        </section>

        <!-- =====================================================
             FLEXIWORK IN NUMBERS
        ===================================================== -->
        <section class="about-stats">
            <div class="about-wrap">

                <h2 class="about-title">Growing a better way to work.</h2>

                <div class="about-stat-grid">

                    <div class="about-stat">
                        <strong>1,200+</strong>
                        <span>Job seekers</span>
                    </div>

                    <div class="about-stat">
                        <strong>150+</strong>
                        <span>Employers</span>
                    </div>

                    <div class="about-stat">
                        <strong>80+</strong>
                        <span>Jobs posted</span>
                    </div>

                </div>

            </div>
        </section>

        <!-- =====================================================
             WHAT MATTERS TO US
        ===================================================== -->
        <section class="about-values">
            <div class="about-wrap">

                <h2 class="about-title">Flexible work, done properly.</h2>

                <div class="values-grid">

                    <div class="value">
                        <div class="value-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3v18M6 21h12M5 7h14" />
                                <path d="M5 7l-3 7a3 3 0 0 0 6 0L5 7zM19 7l-3 7a3 3 0 0 0 6 0l-3-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3>Fairness</h3>
                            <p>
                                Clear pay, clear expectations, and opportunities
                                that respect the people doing the work.
                            </p>
                        </div>
                    </div>

                    <div class="value">
                        <div class="value-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M8 12.5l3 3 5-6" />
                            </svg>
                        </div>
                        <div>
                            <h3>Simplicity</h3>
                            <p>
                                Finding a shift or hiring someone should not require
                                complicated processes or endless paperwork.
                            </p>
                        </div>
                    </div>

                    <div class="value">
                        <div class="value-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3l8 3v6c0 4.5-3.2 8-8 9-4.8-1-8-4.5-8-9V6l8-3z" />
                                <path d="M9 12l2 2 4-4" />
                            </svg>
                        </div>
                        <div>
                            <h3>Trust</h3>
                            <p>
                                We work to create a platform where workers and
                                employers can connect with confidence.
                            </p>
                        </div>
                    </div>

                    <div class="value">
                        <div class="value-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 7v5l3 2" />
                            </svg>
                        </div>
                        <div>
                            <h3>Flexibility</h3>
                            <p>
                                Work should fit around your life, your studies,
                                your business, and your priorities.
                            </p>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- =====================================================
             CLOSING CALL TO ACTION
        ===================================================== -->
        <section class="about-cta">
            <div class="about-wrap">
                <h2 class="about-title">Ready to get started?</h2>
                <p>Find a shift that fits your week, or post one and start hearing from applicants.</p>
                <div class="about-cta-actions">
                    <a href="index.php" class="about-btn about-btn-brand">Find shifts</a>
                    <a href="register.php" class="about-btn about-btn-outline">Create an account</a>
                </div>
            </div>
        </section>

    </main>

    <footer>
        <div class="wrap">
            <div class="footer-grid">

                <div class="footer-brand">
                    <a href="index.php" class="footer-logo">Flexi<span class="chip">Work</span></a>
                    <p>
                        A part-time job marketplace connecting flexible workers with businesses that need reliable people.
                    </p>

                    <div class="footer-social">
                        <a href="https://www.facebook.com" aria-label="Facebook">f</a>
                        <a href="https://x.com" aria-label="X">𝕏</a>
                        <a href="https://www.instagram.com" aria-label="Instagram">◎</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>For workers</h4>
                    <ul>
                        <li><a href="index.php">Find shifts</a></li>
                        <li><a href="categories.php">Categories</a></li>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'jobseeker'): ?>
                            <li><a href="applications.php">My applications</a></li>
                        <?php else: ?>
                            <li><a href="register.php">Create account</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>For employers</h4>
                    <ul>
                        <li>
                            <a href="<?= isset($_SESSION['user_id']) && $_SESSION['role'] === 'employer' ? 'Post_job.php' : 'register.php' ?>">
                                Post a shift
                            </a>
                        </li>
                        <li><a href="<?= isset($_SESSION['user_id']) && $_SESSION['role'] === 'employer' ? 'employer_jobs.php' : 'register.php' ?>">
                                My Jobs
                            </a></li>
                        <li><a href="<?= isset($_SESSION['user_id']) && $_SESSION['role'] === 'employer' ? 'employer_applications.php' : 'register.php' ?>">
                                Applicants
                            </a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        
                    </ul>
                </div>

            </div>

            <div class="footer-bottom">
                <span>© 2026 Flexiwork</span>
                <span><a href="privacy.php">Privacy</a> · Terms</span>
            </div>
        </div>
    </footer>

</body>

</html>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MotoHub</title>
  <link rel="icon" href="motorcycle.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      background: #fdfdfd;
      color: #1a1a1a;
    }

    /* Navbar */
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 50px;
      background: #fff;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    header .logo {
      display: flex;
      align-items: center;
      font-size: 1.2rem;
      font-weight: 600;
      color: #004aad;
    }

    header .logo img {
      width: 40px;
      margin-right: 10px;
    }

    header nav a {
      margin-left: 20px;
      padding: 8px 16px;
      text-decoration: none;
      font-weight: 500;
      color: #004aad;
      border-radius: 8px;
      transition: 0.3s;
    }

    header nav a.login {
      border: 1px solid #004aad;
    }
    header nav a.login:hover {
      background: #004aad;
      color: #fff;
    }

    header nav a.get-started {
      background: #004aad;
      color: #fff;
    }
    header nav a.get-started:hover {
      background: #002d6d;
    }

    /* Old Hero Section */
    .hero {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 80px 20px;
      background: #1f293a;
      min-height: calc(100vh - 80px);
    }

    .hero h1 {
      font-size: 2.8rem;
      max-width: 800px;
      color: #fff;
      margin-bottom: 20px;
      line-height: 1.3;
    }

    .hero h1 span {
      color: #010306ff;
    }

    .hero p {
      font-size: 1.1rem;
      max-width: 600px;
      color: #fff;
      margin-bottom: 30px;
    }

    .hero .buttons a {
      display: inline-block;
      margin: 10px;
      padding: 14px 30px;
      font-size: 1rem;
      font-weight: 500;
      border-radius: 10px;
      text-decoration: none;
      transition: 0.3s;
    }

    .hero .buttons a.primary {
      background: #004aad;
      color: #fff;
    }
    .hero .buttons a.primary:hover {
      background: #002d6d;
    }

    .hero .buttons a.secondary {
      background: #fff;
      border: 1px solid #004aad;
      color: #004aad;
    }
    .hero .buttons a.secondary:hover {
      background: #004aad;
      color: #fff;
    }

    /* New Hero Section */
    .new-hero {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 100px 20px;
      background: #002d6d; /* Dark motorcycle blue */
      color: #fff;
      min-height: 70vh;
    }

    .new-hero h1 {
      font-size: 2.8rem;
      max-width: 900px;
      margin-bottom: 20px;
      font-weight: 700;
    }

    .new-hero p {
      font-size: 1.2rem;
      max-width: 700px;
      margin-bottom: 30px;
      color: #e0e0e0;
    }

    .new-hero button {
      padding: 15px 35px;
      font-size: 1.1rem;
      border: none;
      border-radius: 10px;
      background: #ff3b30; /* Red accent */
      color: #fff;
      cursor: pointer;
      transition: 0.3s;
    }

    .new-hero button:hover {
      background: #cc2a23;
    }

    /* Features Section */
    .features {
      padding: 80px 20px;
      text-align: center;
    }

    .features h2 {
      font-size: 2.2rem;
      margin-bottom: 15px;
      color: #111;
    }

    .features p {
      font-size: 1.1rem;
      color: #555;
      margin-bottom: 50px;
    }

    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      max-width: 1100px;
      margin: 0 auto;
    }

    .feature-card {
      background: #fff;
      padding: 40px 20px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      transition: transform 0.3s ease;
    }

    .feature-card:hover {
      transform: translateY(-5px);
    }

    .feature-card img {
      width: 50px;
      margin-bottom: 15px;
    }

    .feature-card h3 {
      font-size: 1.3rem;
      margin-bottom: 10px;
      color: #002d6d;
    }

    .feature-card p {
      font-size: 1rem;
      color: #555;
    }

    /* Footer */
    footer {
      background: #f8f8f8;
      text-align: center;
      padding: 20px;
      font-size: 0.9rem;
      color: #666;
      margin-top: 50px;
    }
     

    /* Button style */
    .top-contact-btn {
      display: inline-block;
      padding: 12px 28px;
      font-size: 1rem;
      font-weight: 500;
      border-radius: 8px;
      text-decoration: none;
      background: #fff;
      color: #004aad;
      border: 2px solid #004aad;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    /* Hover effect */
    .top-contact-btn:hover {
      background: #002d6d;
      color: #fff;
      border-color: #002d6d;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }


  </style>
</head>
<body>
  <!-- Navbar -->
  <header>
    <div class="logo">
      <img src="photos/Motorcycle.jpg" alt="Logo">
      MotoHub
    </div>

      <!-- Top Contact Button -->
  <div class="top-contact">
    <a href="contact.php" class="top-contact-btn">Contact Us</a>
  </div>

    <nav>
      <a href="user_login.php" class="login">Sign In</a>
      <a href="user_signup.php" class="get-started">Get Started</a>
    </nav>
  </header>

  <!-- Old Hero Section -->
  <section class="hero">
    <h1>Modern <span>Motorcycle Products</span> for Riders & Enthusiasts</h1>
    <p>Discover the latest motorcycles, gear, and accessories with our streamlined product showcase and booking system.</p>
    <div class="buttons">
      <a href="user_login.php" class="primary">Shop Now</a>
      <a href="about.php" class="secondary">Learn More</a>
    </div>
  </section>



  <!-- Features Section -->
<section class="features">
  <h2>Everything You Need for Motorcycle Enthusiasts</h2>
  <p>Built specifically for riders with features designed to enhance your motorcycle journey.</p>

  <div class="feature-grid">
    <div class="feature-card">
      <i class="fa-solid fa-motorcycle fa-3x" style="color:#2563eb;"></i>
      <h3>Motorcycle Showcase</h3>
      <p>Explore the latest motorcycles, from cruisers to sport bikes, all in one place.</p>
    </div>

    <div class="feature-card">
      <i class="fa-solid fa-helmet-safety fa-3x" style="color:#16a34a;"></i>
      <h3>Gear & Accessories</h3>
      <p>Browse helmets, jackets, and riding gear for safety and style.</p>
    </div>

    <div class="feature-card">
      <i class="fa-solid fa-calendar-check fa-3x" style="color:#f59e0b;"></i>
      <h3>Easy Booking</h3>
      <p>Book motorcycle test rides and maintenance schedules with real-time availability.</p>
    </div>

    <div class="feature-card">
      <i class="fa-solid fa-chart-line fa-3x" style="color:#dc2626;"></i>
      <h3>Analytics & Reviews</h3>
      <p>Get detailed insights, reviews, and ratings to make informed choices.</p>
    </div>
  </div>
</section>

   <!-- New Hero Section (Like Green Site) -->
  <section class="new-hero">
    <h1>Ready to Upgrade Your Motorcycle Experience?</h1>
    <p>Join MotoHub in embracing modern motorcycle products, gear, and accessories tailored for riders and enthusiasts.</p>
    <button onclick="window.location.href='user_signup.php'">
  Get Started Today →
</button>

  </section>

  

  <!-- Footer -->
  <footer>
    © 2025 MotoHub. All rights reserved.
  </footer>
</body>
</html>

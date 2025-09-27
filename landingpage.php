<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Motorcycle Dashboard</title>
  <link rel="icon" href="photos/Motorcycle.jpg" type="image/jpeg">
  <style>
    /* Global styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      background: #f1f5f9;
      color: #333;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    button, .btn {
      cursor: pointer;
      border: none;
      outline: none;
      font-weight: 500;
      transition: 0.3s;
    }

    /* Header */
    header {
      background: #fff;
      padding: 20px 50px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: #004aad;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    header h1 {
      font-size: 1.8rem;
    }

   nav a {
  margin-left: 20px;
  font-weight: 500;
  color: #fff; /* White text for better contrast */
  padding: 10px 20px; /* Slightly bigger clickable area */
  border-radius: 12px; /* Rounded corners */
  background: linear-gradient(135deg, #2563eb, #3b82f6); /* Gradient background */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); /* Subtle shadow */
  transition: all 0.3s ease; /* Smooth transition */
  text-decoration: none;
}

nav a:hover {
  background: linear-gradient(135deg, #3b82f6, #2563eb); /* Gradient flip on hover */
  transform: translateY(-3px); /* Lift effect */
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3); /* Stronger shadow */
  color: #fff;
}

/* Active link style */
nav a.active {
  background: #1e40af;
  box-shadow: 0 4px 12px rgba(30, 64, 175, 0.6);
}


    /* Hero Section */
    .hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 100px 50px 50px 50px;
      background: #1f293a;
      color: #fff;
    }

    .hero-text {
      max-width: 600px;
    }

    .hero-text h2 {
      font-size: 3rem;
      margin-bottom: 20px;
    }

    .hero-text p {
      font-size: 1.2rem;
      margin-bottom: 30px;
    }

    .hero-text .btn {
      background: #fff;
      color: #3b82f6;
      padding: 12px 25px;
      border-radius: 8px;
      font-weight: 500;
      transition: 0.3s;
    }

    .hero-text .btn:hover {
      background: #e5e7eb;
    }

    .hero-image img {
      width: 500px;
      max-width: 100%;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }

    /* Features Section */
    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      padding: 50px;
      background: #fff;
    }

    .feature-box {
      background: #f9fafb;
      padding: 25px;
      border-radius: 15px;
      text-align: center;
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
      transition: transform 0.3s;
    }

    .feature-box:hover {
      transform: translateY(-5px);
    }

    .feature-box h3 {
      margin-bottom: 15px;
      color: #2563eb;
    }

    .feature-box p {
      font-size: 0.95rem;
      color: #555;
    }

    /* Footer */
    footer {
      text-align: center;
      padding: 30px 20px;
      background: #3b82f6;
      color: #fff;
      margin-top: 50px;
    }

    /* Responsive */
    @media(max-width: 900px) {
      .hero {
        flex-direction: column;
        text-align: center;
      }

      .hero-text h2 {
        font-size: 2.5rem;
      }

      .hero-text p {
        font-size: 1rem;
      }

      .hero-image img {
        margin-top: 30px;
      }
    }
  </style>
</head>
<body>

<header>
  <h1>Admin Motorcycle</h1>
  <nav>
    <a href="#features">Features</a>
    <a href="login.php">Login</a>
    <a href="signup.php">Signup</a>
  </nav>
</header>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-text">
    <h2>Manage Your Motorcycle Business Easily</h2>
    <p>Admin dashboard for tracking products, orders, top customers, and reports. Everything you need to run your motorcycle shop efficiently.</p>
    <a href="login.php" class="btn">Get Started</a>
  </div>
  <div class="hero-image">
    <img src="photos/Motorcycle.jpg" alt="Motorcycle Dashboard">
  </div>
</section>

<!-- Features Section -->
<section class="features" id="features">
  <div class="feature-box">
    <h3>Products Management</h3>
    <p>Add, edit, and track all your motorcycles and parts in a single dashboard.</p>
  </div>
  <div class="feature-box">
    <h3>Orders & Sales</h3>
    <p>Track orders, sales, and inventory to keep your business running smoothly.</p>
  </div>
  <div class="feature-box">
    <h3>Customer Insights</h3>
    <p>View your top customers and their purchase history to boost sales.</p>
  </div>
  <div class="feature-box">
    <h3>Reports & Analytics</h3>
    <p>Generate detailed reports to analyze your motorcycle shop performance.</p>
  </div>
</section>

<footer>
  &copy; 2025 Admin Motorcycle Dashboard. All Rights Reserved.
</footer>

</body>
</html>

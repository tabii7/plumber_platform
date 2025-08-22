<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Professional plumbing services - 24/7 emergency repairs, installations, and maintenance">
    <meta name="keywords" content="plumbing, plumber, emergency plumbing, pipe repair, toilet repair, drain cleaning">
    <title>Professional Plumbing Services - 24/7 Emergency Repairs</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #10b981 !important;
        }
        
        .nav-link {
            font-weight: 500;
            color: #374151 !important;
            margin: 0 1rem;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: #10b981 !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #e2e8f0;
            color: #e2e8f0;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline:hover {
            background: #e2e8f0;
            color: #1e293b;
            border-color: #e2e8f0;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
            color: white;
            padding: 120px 0 80px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.4;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            color: #cbd5e1;
        }
        
        .hero-features {
            list-style: none;
            margin: 2rem 0;
        }
        
        .hero-features li {
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .hero-features li i {
            color: #10b981;
            margin-right: 0.75rem;
            font-size: 1.1rem;
            background: rgba(16, 185, 129, 0.1);
            padding: 0.5rem;
            border-radius: 50%;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .section {
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        
        .section-title p {
            font-size: 1.1rem;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .service-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid #f3f4f6;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .service-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .service-icon i {
            color: white;
            font-size: 1.5rem;
        }
        
        .service-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        
        .service-card p {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }
        
        .about-section {
            background: #f8fafc;
        }
        
        .about-content h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
        }
        
        .about-content p {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 2rem;
        }
        
        .feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            background: #10b981;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .feature-icon i {
            color: white;
            font-size: 1rem;
        }
        
        .feature-text h4 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }
        
        .feature-text p {
            color: #6b7280;
            margin: 0;
        }
        
        .contact-section {
            background: #1f2937;
            color: white;
        }
        
        .contact-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .contact-icon {
            width: 60px;
            height: 60px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .contact-icon i {
            color: white;
            font-size: 1.5rem;
        }
        
        .contact-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .contact-card p {
            opacity: 0.8;
            margin-bottom: 1rem;
        }
        
        .contact-card a {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 500;
        }
        
        .contact-card a:hover {
            color: #93c5fd;
        }
        
        .footer {
            background: #111827;
            color: white;
            padding: 2rem 0;
            text-align: center;
        }
        
        .footer p {
            margin: 0;
            opacity: 0.8;
        }
        
        .hero-stats {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 2rem;
            margin-top: 3rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #10b981;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        /* Pricing — theme matched to hero (slate bg + green accents) */
.pricing-section {
  background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
  padding: 80px 0;
}
.pricing-section .section-title h2 { color:#fff; }
.pricing-section .section-title p { color: rgba(255,255,255,.85); }

.pricing-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 32px 24px;
  text-align: center;
  height: 100%;
  transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
.pricing-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 50px rgba(2,6,23,.14);
  border-color: rgba(16,185,129,.35);
}

.pricing-card .pricing-icon {
  width: 64px; height: 64px; border-radius: 12px;
  margin: 0 auto 1rem;
  display:flex; align-items:center; justify-content:center;
  background: linear-gradient(135deg, #10b981, #059669);
  color:#fff; font-size:1.7rem;
}

.pricing-card h3 { color:#1f2937; font-weight:700; margin-bottom: 12px; }

.pricing-price .current-price { color:#10b981; font-size:2.2rem; font-weight:800; line-height:1; }
.pricing-price .period { color:#6b7280; }
.pricing-price .original-price { color:#9ca3af; text-decoration:line-through; }

.discount-badge {
  display:inline-block; margin-top:10px; font-weight:700; font-size:.9rem;
  color:#065f46; background:rgba(16,185,129,.12);
  border:1px solid rgba(16,185,129,.35); border-radius:20px;
  padding:6px 12px;
}

.pricing-features li { color:#374151; }
.pricing-features i { color:#10b981; }

.pricing-card .btn {
  border-radius: 10px;
  padding: 12px 16px;
}
.pricing-card .btn.btn-primary {
  /* Uses your existing .btn-primary gradient (green) */
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  box-shadow: 0 8px 24px rgba(16,185,129,.28);
  border: none;
}
.pricing-card .btn.btn-primary:hover {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

.pricing-card.featured {
  border: 2px solid #10b981;
  box-shadow: 0 24px 60px rgba(16,185,129,.18);
  position: relative;
}
.featured-badge {
  position:absolute; top:-14px; left:50%; transform:translateX(-50%);
  background:#10b981; color:#0b1f17; /* dark teal text for contrast */
  padding:8px 14px; border-radius:999px; font-weight:800; font-size:.82rem;
  border: 1px solid rgba(2,6,23,.08);
}
.yearly-price {
  background:#f8fafc; border:1px solid #e5e7eb; border-radius:10px;
  padding:10px; margin-top:10px;
}
.yearly-price div:first-child { font-weight:700; color:#1f2937; }
.yearly-price div:last-child { color:#059669; font-size:.92rem; }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .about-content h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-wrench me-2"></i>ProPlumber
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">Home</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('profile.edit') }}">Profile</a>
                        </li>
                    @endauth
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/register') }}">Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/login') }}">Login</a>
                        </li>
                        <li class="nav-item ms-2">
                            <a href="{{ url('/register') }}" class="btn btn-primary">Get Started</a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>{{ Auth::user()->full_name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="hero-content">
                        <h1 class="hero-title">Professional Plumbing Services</h1>
                        <p class="hero-subtitle">Reliable, licensed plumbers available 24/7 for all your plumbing needs. Emergency repairs, installations, and maintenance with guaranteed quality.</p>
                        
                        <div class="d-flex flex-wrap gap-3 mb-4">
                            @guest
                                <a href="{{ url('/register') }}" class="btn btn-outline btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Register Now
                                </a>
                                <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                                </a>
                                <a href="#pricing" class="btn btn-outline btn-lg">
                                    <i class="fas fa-crown me-2"></i>View Plans
                                </a>
                            @endguest
                        </div>
                        
                        <ul class="hero-features">
                            <li><i class="fas fa-clock"></i> 24/7 Emergency Services</li>
                            <li><i class="fas fa-shield-alt"></i> Licensed & Insured Professionals</li>
                            <li><i class="fas fa-calendar-check"></i> Same Day Service Available</li>
                            <li><i class="fas fa-medal"></i> Free Estimates & Guaranteed Work</li>
                        </ul>
                        
                        <div class="hero-stats">
                            <div class="row">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <span class="stat-number">500+</span>
                                        <span class="stat-label">Happy Clients</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <span class="stat-number">15+</span>
                                        <span class="stat-label">Years Experience</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <span class="stat-number">24/7</span>
                                        <span class="stat-label">Emergency Service</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="text-center">
                        <div style="position: relative;">
                            <div style="width: 300px; height: 300px; background: rgba(16, 185, 129, 0.1); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-wrench" style="font-size: 8rem; color: #10b981; opacity: 0.8;"></i>
                            </div>
                            <div style="position: absolute; top: -20px; right: 50px; width: 80px; height: 80px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-tools" style="font-size: 2rem; color: #e2e8f0;"></i>
                            </div>
                            <div style="position: absolute; bottom: -20px; left: 50px; width: 60px; height: 60px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-cog" style="font-size: 1.5rem; color: #e2e8f0;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-content">
                        <h2>Trusted Plumbing Experts</h2>
                        <p>With years of experience and a commitment to quality, we provide reliable plumbing solutions for residential and commercial properties. Our team of licensed professionals is dedicated to delivering exceptional service and lasting results.</p>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="feature-text">
                                <h4>24/7 Emergency Service</h4>
                                <p>Available round the clock for urgent plumbing emergencies</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Licensed & Insured</h4>
                                <p>Fully licensed professionals with comprehensive insurance coverage</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-medal"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Quality Guarantee</h4>
                                <p>All work is guaranteed with our satisfaction promise</p>
                            </div>
                        </div>
                        
                        <a href="{{ url('/register') }}" class="btn btn-primary mt-3">
                            Join Our Platform
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Professional Plumber" class="img-fluid rounded" style="max-width: 100%; height: auto;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>Our Professional Services</h2>
                <p>Comprehensive plumbing solutions for all your needs</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3>Emergency Plumbing</h3>
                        <p>24/7 emergency services for burst pipes, clogged drains, water heater failures, and other urgent plumbing issues.</p>
                        <a href="{{ url('/register') }}" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-wrench"></i>
                        </div>
                        <h3>Pipe Repair & Installation</h3>
                        <p>Professional pipe repair, replacement, and installation services for all types of plumbing systems.</p>
                        <a href="{{ url('/register') }}" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-water"></i>
                        </div>
                        <h3>Drain Cleaning</h3>
                        <p>Advanced drain cleaning and unclogging services using professional equipment and techniques.</p>
                        <a href="{{ url('/register') }}" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-fire"></i>
                        </div>
                        <h3>Water Heater Services</h3>
                        <p>Installation, repair, and maintenance of water heaters including tankless and traditional systems.</p>
                        <a href="{{ url('/register') }}" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-sink"></i>
                        </div>
                        <h3>Fixture Installation</h3>
                        <p>Professional installation of sinks, toilets, faucets, showers, and other plumbing fixtures.</p>
                        <a href="{{ url('/register') }}" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Leak Detection</h3>
                        <p>Advanced leak detection services using modern technology to find and repair hidden water leaks.</p>
                        <a href="{{ url('/register') }}" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
  <section id="pricing" class="section pricing-section">
  <div class="container">
    <div class="section-title text-center mb-5">
      <h2>Choose Your Plan</h2>
      <p>Select the perfect plan for your plumbing needs</p>
    </div>

    <div class="row justify-content-center g-4">
      <!-- Client Plan -->
      <div class="col-lg-4 col-md-6">
        <div class="pricing-card">
          <div class="pricing-icon"><i class="fas fa-home"></i></div>
          <h3>Client Plan</h3>

          <div class="pricing-price mb-3">
            <div class="current-price">€19.99</div>
            <div class="period">/month</div>
            <div class="original-price">€26.99</div>
            <span class="discount-badge">25% OFF</span>
          </div>

          <div class="pricing-features mb-4" style="text-align:left">
            <ul class="list-unstyled mb-0">
              <li class="mb-2"><i class="fas fa-check me-2"></i>Unlimited plumber requests</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Instant WhatsApp contact</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Save favorite plumbers</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>24/7 support</li>
            </ul>
          </div>

          <a href="{{ route('checkout', ['plan' => 'client_monthly']) }}"
             class="btn btn-primary btn-lg w-100">
            Get Started
          </a>
        </div>
      </div>

      <!-- Company Plan (Featured) -->
      <div class="col-lg-4 col-md-6">
        <div class="pricing-card featured">
          <div class="featured-badge">MOST POPULAR</div>
          <div class="pricing-icon"><i class="fas fa-building"></i></div>
          <h3>Company Plan</h3>

          <div class="pricing-price mb-3">
            <div class="current-price">€29.99</div>
            <div class="period">/month</div>
            <div class="yearly-price">
              <div>€179.88/year</div>
              <div>Save 50% until Sept 30</div>
            </div>
          </div>

          <div class="pricing-features mb-4" style="text-align:left">
            <ul class="list-unstyled mb-0">
              <li class="mb-2"><i class="fas fa-check me-2"></i>Unlimited job postings</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Team member accounts</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Priority support</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Access to verified plumbers</li>
            </ul>
          </div>

          <a href="{{ route('checkout', ['plan' => 'company_monthly']) }}"
             class="btn btn-primary btn-lg w-100">
            Get Started
          </a>
        </div>
      </div>

      <!-- Plumber Plan -->
      <div class="col-lg-4 col-md-6">
        <div class="pricing-card">
          <div class="pricing-icon"><i class="fas fa-tools"></i></div>
          <h3>Plumber Plan</h3>

          <div class="pricing-price mb-3">
            <div class="current-price">FREE</div>
            <div class="period">forever</div>
          </div>

          <div class="pricing-features mb-4" style="text-align:left">
            <ul class="list-unstyled mb-0">
              <li class="mb-2"><i class="fas fa-check me-2"></i>Create free profile</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Set service radius</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Receive unlimited requests</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Direct WhatsApp contact</li>
            </ul>
          </div>

          <a href="{{ route('register', ['role' => 'plumber']) }}"
             class="btn btn-primary btn-lg w-100">
            Join Free
          </a>
        </div>
      </div>
    </div>
  </div>
</section>



    <!-- Contact Section -->
    <section class="section contact-section">
        <div class="container">
            <div class="section-title">
                <h2 style="color: white;">Get In Touch</h2>
                <p style="color: rgba(255,255,255,0.8);">Ready to help with all your plumbing needs</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h3>Emergency Hotline</h3>
                        <p>24/7 Emergency Plumbing Services</p>
                        <a href="tel:+1234567890">+1 (234) 567-890</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Email Us</h3>
                        <p>Get in touch for quotes and inquiries</p>
                        <a href="mailto:info@professionalplumber.com">info@professionalplumber.com</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Service Hours</h3>
                        <p>Available when you need us most</p>
                        <span>Mon-Fri: 8AM-8PM<br>Sat: 9AM-6PM<br>Sun: Emergency Only</span>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="{{ url('/register') }}" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-user-plus me-2"></i>Register Now
                </a>
                <a href="{{ url('/login') }}" class="btn btn-outline btn-lg" style="border-color: white; color: white;">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Professional Plumbing Services. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
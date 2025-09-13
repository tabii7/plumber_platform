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
            transition: background-color 0.3s ease, color 0.3s ease;
            scroll-behavior: smooth;
        }
        
        /* Ensure page starts at top on mobile */
        html, body {
            scroll-behavior: auto !important;
        }
        
        /* Prevent auto-scroll to hidden elements */
        .login-form-container[style*="display: none"] {
            position: absolute !important;
            left: -9999px !important;
            top: -9999px !important;
        }
        
        /* Dark mode styles */
        body.dark {
            background-color: #0f172a;
            color: #e2e8f0;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            transition: background-color 0.3s ease;
        }
        
        body.dark .navbar {
            background: rgba(15, 23, 42, 0.95);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
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
        
        body.dark .nav-link {
            color: #e2e8f0 !important;
        }
        
        body.dark .nav-link:hover {
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

        /* Custom Plumber Button Styling */
        .btn-plumber {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }
        
        .btn-plumber:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
            color: white;
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

        /* Additional outline button styles for navbar */
        .navbar .btn-outline {
            border-color: #10b981;
            color: #10b981;
        }
        
        .navbar .btn-outline:hover {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }

        /* Navbar button sizing and spacing */
        .navbar .btn {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        .navbar .btn-plumber {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
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
        
        body.dark .service-card {
            background: #1e293b;
            border: 1px solid #334155;
            color: #e2e8f0;
        }
        
        body.dark .service-card h3 {
            color: #f1f5f9;
        }
        
        body.dark .service-card p {
            color: #cbd5e1;
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
        
        body.dark .about-section {
            background: #0f172a;
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
        
        body.dark .about-content h2 {
            color: #f1f5f9;
        }
        
        body.dark .about-content p {
            color: #cbd5e1;
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
        
        body.dark .feature-text h4 {
            color: #f1f5f9;
        }
        
        body.dark .feature-text p {
            color: #cbd5e1;
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
        
        .footer-links {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 1rem;
        }
        
        .footer-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .footer-link:hover {
            color: #10b981;
        }
        
        .footer-separator {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.8rem;
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

        /* Change blue outline buttons to green */
        .pricing-card .btn.btn-outline-primary {
          color: #10b981;
          border-color: #10b981;
          background: transparent;
        }
        
        .pricing-card .btn.btn-outline-primary:hover {
          color: white;
          background: #10b981;
          border-color: #10b981;
        }

        .pricing-card.featured {
          border: 2px solid #10b981;
          box-shadow: 0 24px 60px rgba(16,185,129,.18);
          position: relative;
        }

        .featured-badge {
          position: absolute;
          top: -12px;
          left: 50%;
          transform: translateX(-50%);
          background: #10b981;
          color: white;
          padding: 8px 16px;
          border-radius: 20px;
          font-size: 0.8rem;
          font-weight: 700;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          box-shadow: 0 4px 12px rgba(16,185,129,.3);
        }
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


        /* Login Form Styles */
        .login-form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 600px;
            width: 100%;
        }
        
        .login-form {
            background: white;
            border: 2px dashed #60a5fa;
            border-radius: 12px;
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: block;
            visibility: visible;
            opacity: 1;
        }
        
        .login-form:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        /* Ensure login form is always visible for guests */
        .col-lg-5 .login-form-container {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .col-lg-5 .login-form {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        
        .login-brand i {
            color: #10b981;
        }
        
        .login-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .crossed-out {
            text-decoration: line-through;
            color: #ef4444;
        }
        
        .login-subtitle {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            color: #9ca3af;
            z-index: 2;
            font-size: 1.1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 14px 14px 45px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f9fafb;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #6b7280;
            font-size: 0.8rem;
            cursor: pointer;
            z-index: 2;
        }
        
        .password-toggle:hover {
            color: #10b981;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .form-check {
            display: flex;
            align-items: center;
        }
        
        .form-check-input {
            margin-right: 0.5rem;
            accent-color: #10b981;
        }
        
        .form-check-label {
            font-size: 0.9rem;
            color: #374151;
            margin: 0;
        }
        
        .forgot-password {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .forgot-password:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #ef4444;
        }
        
        .is-invalid {
            border-color: #ef4444;
        }
        
        .is-invalid:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        /* Why Plumbers Join Section Styles */
        .plumber-section {
            background: #f8fafc;
            padding: 100px 0;
        }
        
        .plumber-join-content {
            padding-right: 2rem;
        }
        
        .section-title-left {
            font-size: 2.8rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .plumber-join-subtitle {
            font-size: 1.2rem;
            color: #6b7280;
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }
        
        .plumber-benefits {
            margin-bottom: 2.5rem;
        }
        
        .benefit-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .benefit-icon {
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
        
        .benefit-icon i {
            color: white;
            font-size: 1rem;
        }
        
        .benefit-text h4 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }
        
        .benefit-text p {
            color: #6b7280;
            margin: 0;
        }
        
        .plumber-cta {
            margin-top: 2rem;
        }
        
        .cta-text {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
        }
        
        .plumber-image-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            padding-left: 2rem;
        }
        
        .plumber-image-wrapper {
            position: relative;
            width: 100%;
            max-width: 600px;
        }
        
        .plumber-main-image {
            width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        
        .plumber-main-image:hover {
            transform: scale(1.02);
        }
        
        .floating-tool {
            position: absolute;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            animation: float 3s ease-in-out infinite;
        }
        
        .tool-1 {
            top: 8%;
            left: -8%;
            animation-delay: 0s;
        }
        
        .tool-2 {
            top: 15%;
            right: -12%;
            animation-delay: 0.5s;
        }
        
        .tool-3 {
            bottom: 25%;
            left: -6%;
            animation-delay: 1s;
        }
        
        .tool-4 {
            bottom: 10%;
            right: -10%;
            animation-delay: 1.5s;
        }
        
        .tool-5 {
            top: 45%;
            right: -4%;
            animation-delay: 2s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
        }
        
        body.dark .plumber-section {
            background: #0f172a;
        }
        
        body.dark .section-title-left {
            color: #f1f5f9;
        }
        
        body.dark .plumber-join-subtitle {
            color: #cbd5e1;
        }
        
        
        body.dark .benefit-text h4 {
            color: #f1f5f9;
        }
        
        body.dark .benefit-text p {
            color: #cbd5e1;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .section-title h2 {
                font-size: 2.2rem;
            }
        }
        
        @media (max-width: 992px) {
            .hero-section {
                padding: 100px 0 60px;
            }
            
            .hero-title {
                font-size: 2.8rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .section {
                padding: 60px 0;
            }
            
            .plumber-section {
                padding: 80px 0;
            }
            
            .plumber-join-content {
                padding-right: 0;
                margin-bottom: 2rem;
            }
            
            .section-title-left {
                font-size: 2.4rem;
            }
            
            .plumber-image-container {
                padding-left: 0;
                margin-top: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            /* Navbar improvements */
            .navbar {
                padding: 0.75rem 0;
            }
            
            .navbar-brand {
                font-size: 1.3rem;
            }
            
            .navbar .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
                margin: 0.25rem 0;
            }
            
            .navbar .btn-plumber {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
            }
            
            /* Hero section */
            .hero-section {
                padding: 80px 0 50px;
            }
            
            .hero-title {
                font-size: 2.2rem;
                margin-bottom: 1rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .hero-features {
                margin: 1.5rem 0;
            }
            
            .hero-features li {
                font-size: 0.9rem;
                margin: 0.4rem 0;
            }
            
            .hero-features li i {
                width: 1.5rem;
                height: 1.5rem;
                font-size: 0.9rem;
                margin-right: 0.5rem;
            }
            
            .hero-stats {
                margin-top: 2rem;
                padding: 1.5rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
            
            /* Login form - Hide by default on mobile */
            .login-form-container {
                min-height: auto;
                padding: 1rem 0;
                display: none !important;
                visibility: hidden !important;
            }
            
            .login-form {
                margin: 0.5rem;
                padding: 1.5rem;
                max-width: 100%;
                display: none !important;
                visibility: hidden !important;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
            
            .form-control {
                padding: 12px 12px 12px 40px;
                font-size: 0.95rem;
            }
            
            .input-icon {
                left: 12px;
                font-size: 1rem;
            }
            
            .btn-login {
                padding: 12px;
                font-size: 1rem;
            }
            
            /* Sections */
            .section {
                padding: 50px 0;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
            
            .section-title p {
                font-size: 1rem;
            }
            
            .about-content h2 {
                font-size: 1.8rem;
            }
            
            .about-content p {
                font-size: 1rem;
            }
            
            .feature-item {
                margin-bottom: 1.2rem;
            }
            
            .feature-icon {
                width: 35px;
                height: 35px;
            }
            
            .feature-icon i {
                font-size: 0.9rem;
            }
            
            /* Service cards */
            .service-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .service-icon {
                width: 50px;
                height: 50px;
                margin-bottom: 1rem;
            }
            
            .service-icon i {
                font-size: 1.2rem;
            }
            
            .service-card h3 {
                font-size: 1.1rem;
            }
            
            .service-card p {
                font-size: 0.9rem;
            }
            
            /* Plumber section */
            .plumber-section {
                padding: 60px 0;
            }
            
            .section-title-left {
                font-size: 1.8rem;
            }
            
            .plumber-join-subtitle {
                font-size: 1rem;
            }
            
            .benefit-item {
                margin-bottom: 1.2rem;
            }
            
            .benefit-icon {
                width: 35px;
                height: 35px;
            }
            
            .benefit-icon i {
                font-size: 0.9rem;
            }
            
            .plumber-image-wrapper {
                max-width: 100%;
            }
            
            .floating-tool {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            
            .tool-1, .tool-2, .tool-3, .tool-4, .tool-5 {
                display: none;
            }
            
            /* Pricing section */
            .pricing-section {
                padding: 60px 0;
            }
            
            .pricing-card {
                padding: 24px 20px;
                margin-bottom: 1.5rem;
            }
            
            .pricing-card h3 {
                font-size: 1.2rem;
            }
            
            .current-price {
                font-size: 1.8rem;
            }
            
            .pricing-features li {
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
            }
            
            /* Contact section */
            .contact-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .contact-icon {
                width: 50px;
                height: 50px;
                margin-bottom: 1rem;
            }
            
            .contact-icon i {
                font-size: 1.2rem;
            }
            
            .contact-card h3 {
                font-size: 1.1rem;
            }
            
            .contact-card p {
                font-size: 0.9rem;
            }
            
            /* Footer */
            .footer {
                padding: 1.5rem 0;
            }
            
            .footer-links {
                justify-content: center;
                margin-top: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            /* Extra small devices */
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .hero-title {
                font-size: 1.8rem;
            }
            
            .hero-subtitle {
                font-size: 0.95rem;
            }
            
            .section-title h2 {
                font-size: 1.6rem;
            }
            
            .section-title-left {
                font-size: 1.6rem;
            }
            
            .about-content h2 {
                font-size: 1.6rem;
            }
            
            .login-form {
                padding: 1rem;
            }
            
            .login-title {
                font-size: 1.3rem;
            }
            
            .form-control {
                padding: 10px 10px 10px 35px;
                font-size: 0.9rem;
            }
            
            .input-icon {
                left: 10px;
                font-size: 0.9rem;
            }
            
            .service-card {
                padding: 1.2rem;
            }
            
            .pricing-card {
                padding: 20px 16px;
            }
            
            .contact-card {
                padding: 1.2rem;
            }
            
            .hero-stats {
                padding: 1rem;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
            
            .stat-label {
                font-size: 0.75rem;
            }
            
            /* Button improvements */
            .btn {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }
            
            .btn-lg {
                padding: 0.875rem 1.75rem;
                font-size: 1rem;
            }
            
            /* Flex improvements for mobile */
            .d-flex.flex-wrap.gap-3 {
                flex-direction: column;
                gap: 0.75rem !important;
            }
            
            .d-flex.flex-wrap.gap-3 .btn {
                width: 100%;
                margin: 0;
            }
        }
        
        @media (max-width: 480px) {
            /* Very small devices */
            .hero-title {
                font-size: 1.6rem;
            }
            
            .section-title h2 {
                font-size: 1.4rem;
            }
            
            .section-title-left {
                font-size: 1.4rem;
            }
            
            .about-content h2 {
                font-size: 1.4rem;
            }
            
            .login-form {
                padding: 0.8rem;
                display: none !important;
                visibility: hidden !important;
            }
            
            .login-title {
                font-size: 1.2rem;
            }
            
            .service-card {
                padding: 1rem;
            }
            
            .pricing-card {
                padding: 16px 12px;
            }
            
            .contact-card {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-wrench me-2"></i>Loodgieter.app
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
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
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    
                    <li class="nav-item d-flex align-items-center me-3">
                        <x-dark-mode-toggle />
                    </li>

                    @guest
                        <li class="nav-item d-block d-lg-none">
                            <button onclick="toggleMobileLogin()" class="btn btn-outline-secondary w-100 mb-2">Login</button>
                        </li>
                        <li class="nav-item d-block d-lg-none">
                            <a href="/client/register" class="btn btn-primary w-100">Get Started</a>
                        </li>
                        <li class="nav-item d-none d-lg-block">
                            <a href="/client/register" class="btn btn-primary">Get Started</a>
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
                                <a href="#pricing" class="btn btn-primary btn-lg">
                                    <i class="fas fa-crown me-2"></i>100% Free beta access now
                                </a>
                                <a href="/client/register" class="btn btn-outline btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Join to hire a Plumber
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="btn btn-outline btn-lg">
                                    <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
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
                    <!-- Login Form -->
                    <div class="login-form-container" style="display: flex !important; visibility: visible !important; opacity: 1 !important;">
                        <div class="login-form" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                                <div class="login-header">
                                    <div class="login-brand">
                                        <i class="fas fa-building me-2"></i>loodgieter.app
                                    </div>
                                    <h2 class="login-title">
                                        Welcome 
                                    </h2>
                                    <p class="login-subtitle">Sign in to your account</p>
                                </div>
                                
                                <form method="POST" action="{{ route('login') }}" class="login-form-fields">
                                    @csrf
                                    
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <i class="fas fa-envelope input-icon"></i>
                                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                                   name="email"  required autocomplete="email" placeholder="Enter your email address">
                                        </div>
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <i class="fas fa-lock input-icon"></i>
                                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                                   name="password" required autocomplete="current-password" placeholder="Enter your password">
                                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                                <span class="show-text">Show</span>
                                            </button>
                                        </div>
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-options">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="remember">
                                                Remember me
                                            </label>
                                        </div>
                                        @if (Route::has('password.request'))
                                            <a class="forgot-password" href="{{ route('password.request') }}">
                                                Forgot password?
                                            </a>
                                        @endif
                                    </div>
                                    
                                    <button type="submit" class="btn btn-login">
                                        Log in
                                    </button>
                                </form>
                        </div>
                    </div>
                    
                    @auth
                        <!-- Show this for logged in users -->
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
                    @endauth
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
                        
                        <a href="{{ route('register') }}" class="btn btn-primary mt-3">
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
                        <a href="{{ url('/login') }}" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-wrench"></i>
                        </div>
                        <h3>Pipe Repair & Installation</h3>
                        <p>Professional pipe repair, replacement, and installation services for all types of plumbing systems.</p>
                        <a href="{{ url('/login') }}" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-water"></i>
                        </div>
                        <h3>Drain Cleaning</h3>
                        <p>Advanced drain cleaning and unclogging services using professional equipment and techniques.</p>
                        <a href="{{ url('/login') }}" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-fire"></i>
                        </div>
                        <h3>Water Heater Services</h3>
                        <p>Installation, repair, and maintenance of water heaters including tankless and traditional systems.</p>
                        <a href="{{ url('/login') }}" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-sink"></i>
                        </div>
                        <h3>Fixture Installation</h3>
                        <p>Professional installation of sinks, toilets, faucets, showers, and other plumbing fixtures.</p>
                        <a href="{{ url('/login') }}" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Leak Detection</h3>
                        <p>Advanced leak detection services using modern technology to find and repair hidden water leaks.</p>
                        <a href="{{ url('/login') }}" class="btn btn-outline">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Plumbers Join Section -->
    <section class="section plumber-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <div class="plumber-join-content">
                        <h2 class="section-title-left">Why plumbers Join loodgieter.app?</h2>
                        <p class="plumber-join-subtitle">Be part of the fastest-growing platform connecting plumbers directly with real clients in your area. Subscription as a plumber is 100% free and brings you closer to the jobs that matter.</p>
                        
                        <div class="plumber-benefits">
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div class="benefit-text">
                                    <h4>Direct contact with clients</h4>
                                    <p>No middleman. Talk to customers right away and secure new projects without delays.</p>
                                </div>
                            </div>
                            
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-shield-check"></i>
                                </div>
                                <div class="benefit-text">
                                    <h4>Verified clients</h4>
                                    <p>We check every request so you only receive genuine leads.</p>
                                </div>
                            </div>
                            
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="benefit-text">
                                    <h4>More visibility</h4>
                                    <p>Showcase your services to people searching for plumbers in your region, 24/7.</p>
                                </div>
                            </div>
                            
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <div class="benefit-text">
                                    <h4>No hidden costs</h4>
                                    <p>Signing up is free for ever.</p>
                                </div>
                            </div>
                            
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="benefit-text">
                                    <h4>Flexibility</h4>
                                    <p>Set your own working hours, holiday, choose your coverage area, and manage your own schedule.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="plumber-cta">
                            <p class="cta-text">Start today, grow your business, and let new customers find you easily.</p>
                            <a href="/plumber/register" class="btn btn-primary btn-lg">
                                <i class="fas fa-tools me-2"></i>Plumbers Join now – it's free!
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="plumber-image-container">
                        <div class="plumber-image-wrapper">
                            <img src="{{ asset('front/plumber-section.jpeg') }}" 
                                 alt="Professional Plumber with Tools" 
                                 class="plumber-main-image">
                            
                            <!-- Floating tool elements -->
                            <div class="floating-tool tool-1">
                                <i class="fas fa-wrench"></i>
                            </div>
                            <div class="floating-tool tool-2">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="floating-tool tool-3">
                                <i class="fas fa-screwdriver"></i>
                            </div>
                            <div class="floating-tool tool-4">
                                <i class="fas fa-hammer"></i>
                            </div>
                            <div class="floating-tool tool-5">
                                <i class="fas fa-cog"></i>
                            </div>
                        </div>
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
      <!-- One Time Request -->
      <div class="col-lg-4 col-md-6">
        <div class="pricing-card">
          <div class="pricing-icon"><i class="fas fa-bolt"></i></div>
          <h3>One Time Request</h3>

          <div class="pricing-price mb-3">
            <div class="current-price">€25</div>
            <div class="period">one-time</div>
          </div>

          <div class="pricing-features mb-4" style="text-align:left">
            <ul class="list-unstyled mb-0">
              <li class="mb-2"><i class="fas fa-check me-2"></i>Single plumber request</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Instant WhatsApp contact</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>24/7 emergency service</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>No subscription required</li>
            </ul>
          </div>

          <a href="{{ route('checkout', ['plan' => 'one_time']) }}"
             class="btn btn-primary btn-lg w-100">
            Get Service Now
          </a>
        </div>
      </div>

      <!-- Client Plan (Featured) -->
      <div class="col-lg-4 col-md-6">
        <div class="pricing-card featured">
          <div class="featured-badge">MOST POPULAR</div>
          <div class="pricing-icon"><i class="fas fa-home"></i></div>
          <h3>Client Plan</h3>

          <div class="pricing-price mb-3">
            <div class="current-price">€9.99</div>
            <div class="period">/month</div>
            <div class="original-price">€14.99</div>
            <span class="discount-badge">33% OFF</span>
          </div>

          <div class="pricing-features mb-4" style="text-align:left">
            <ul class="list-unstyled mb-0">
              <li class="mb-2"><i class="fas fa-check me-2"></i>Unlimited plumber requests</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Instant WhatsApp contact</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Save favorite plumbers</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>24/7 support</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>€99/year (2 months free)</li>
            </ul>
          </div>

          <div class="d-grid gap-2">
            <a href="{{ route('checkout', ['plan' => 'client_monthly']) }}"
               class="btn btn-primary">
              Monthly - €9.99
            </a>
            <a href="{{ route('checkout', ['plan' => 'client_yearly']) }}"
               class="btn btn-outline-primary">
              Yearly - €99 (Save €20)
            </a>
          </div>
        </div>
      </div>

      <!-- Company Plan -->
      <div class="col-lg-4 col-md-6">
        <div class="pricing-card">
          <div class="pricing-icon"><i class="fas fa-building"></i></div>
          <h3>Company Plan</h3>

          <div class="pricing-price mb-3">
            <div class="current-price">€24.99</div>
            <div class="period">/month</div>
            <div class="yearly-price">
              <div>€299/year</div>
              <div>2 months free</div>
            </div>
          </div>

          <div class="pricing-features mb-4" style="text-align:left">
            <ul class="list-unstyled mb-0">
              <li class="mb-2"><i class="fas fa-check me-2"></i>Unlimited job postings</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Team member accounts</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Priority support</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Access to verified plumbers</li>
              <li class="mb-2"><i class="fas fa-check me-2"></i>Advanced analytics</li>
            </ul>
          </div>

          <div class="d-grid gap-2">
            <a href="{{ route('checkout', ['plan' => 'company_monthly']) }}"
               class="btn btn-primary">
              Monthly - €24.99
            </a>
            <a href="{{ route('checkout', ['plan' => 'company_yearly']) }}"
               class="btn btn-outline-primary">
              Yearly - €299 (Save €60)
            </a>
          </div>
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
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h3>Emergency Hotline</h3>
                        <p>24/7 Emergency Plumbing Services</p>
                        <a href="https://wa.me/32490468009" target="_blank">+32 490 46 80 09</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Email Us</h3>
                        <p>Get in touch for quotes and inquiries</p>
                        <a href="mailto:support@loodgieter.app">support@loodgieter.app</a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Service Hours</h3>
                        <p>Available when you need us most</p>
                        <span style="font-weight: bold; color: #10b981;">ALWAYS</span>
                    </div>
                </div>
            </div>
            

        </div>
    </section>

    <!-- Technical Support Section -->
    <section class="section" style="background: #f8f9fa; padding: 40px 0;">
        <div class="container">
            <div class="text-center">
                <h3 style="color: #6b7280; margin-bottom: 20px;">Technische Ondersteuning</h3>
                <p style="color: #6b7280; margin-bottom: 20px;">Need technical help? Our support team is here to assist you.</p>
                <a href="https://help.diensten.pro" target="_blank" class="btn btn-outline-secondary">
                    <i class="fas fa-headset me-2"></i>Naar Diensten.Pro Help desk
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p>&copy; 2025 diensten.pro - All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="footer-links" style="font-size: 0.8em; opacity: 0.7;">
                        <a href="{{ route('privacy') }}" class="footer-link">Privacy</a>
                        <span class="footer-separator">|</span>
                        <a href="{{ route('terms') }}" class="footer-link">T&C</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Password Toggle Function -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle .show-text');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = 'Show';
            }
        }
        
        // Toggle mobile login form
        function toggleMobileLogin() {
            const loginContainer = document.querySelector('.login-form-container');
            const loginForm = document.querySelector('.login-form');
            
            if (loginContainer && loginForm) {
                const isVisible = loginContainer.style.display === 'flex';
                
                if (isVisible) {
                    // Hide login form
                    loginContainer.style.display = 'none';
                    loginContainer.style.visibility = 'hidden';
                    loginForm.style.display = 'none';
                    loginForm.style.visibility = 'hidden';
                } else {
                    // Show login form
                    loginContainer.style.display = 'flex';
                    loginContainer.style.visibility = 'visible';
                    loginForm.style.display = 'block';
                    loginForm.style.visibility = 'visible';
                    
                    // Focus on email input after a short delay
                    setTimeout(() => {
                        const emailInput = document.getElementById('email');
                        if (emailInput) {
                            emailInput.focus();
                        }
                    }, 100);
                    
                    // Scroll to login form
                    loginContainer.scrollIntoView({ behavior: 'smooth' });
                }
            }
        }
        
        // Initialize dark mode on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure page starts at top on mobile
            if (window.innerWidth <= 768) {
                window.scrollTo(0, 0);
                document.documentElement.scrollTop = 0;
                document.body.scrollTop = 0;
            }
            
            // Initialize dark mode state from localStorage
            const savedDarkMode = localStorage.getItem('darkMode');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const shouldBeDark = savedDarkMode === 'true' || (!savedDarkMode && prefersDark);
            
            if (shouldBeDark) {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark');
            }
            
            console.log('Dark mode initialized on page load:', shouldBeDark);
            
            // Check if Alpine.js is loaded
            if (typeof Alpine === 'undefined') {
                console.log('Alpine.js not loaded, using fallback dark mode toggle');
                
                // Add fallback dark mode functionality
                const toggleButton = document.querySelector('.toggle-switch');
                if (toggleButton) {
                    toggleButton.addEventListener('click', function() {
                        const isDark = document.body.classList.contains('dark');
                        
                        if (isDark) {
                            document.documentElement.classList.remove('dark');
                            document.body.classList.remove('dark');
                            localStorage.setItem('darkMode', 'false');
                        } else {
                            document.documentElement.classList.add('dark');
                            document.body.classList.add('dark');
                            localStorage.setItem('darkMode', 'true');
                        }
                        
                        console.log('Fallback dark mode toggled to:', !isDark ? 'dark' : 'light');
                    });
                }
            }
        });
        
        // Additional check on window load to prevent auto-scroll
        window.addEventListener('load', function() {
            if (window.innerWidth <= 768) {
                // Force scroll to top on mobile
                setTimeout(() => {
                    window.scrollTo(0, 0);
                    document.documentElement.scrollTop = 0;
                    document.body.scrollTop = 0;
                }, 100);
            }
        });
    </script>
</body>
</html>
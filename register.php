<?php
// register.php
session_start();
require_once 'database/conn.php';
require_once 'inc/countries.php';

// Function to detect country from IP
function detectCountryFromIp() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $url = "https://ipapi.co/{$ip}/country_name/";
    $response = @file_get_contents($url);
    if ($response === false) {
        file_put_contents('debug.log', "Failed to fetch country from ipapi.co for IP: {$ip}\n", FILE_APPEND);
        return 'Nigeria';
    }
    $country = trim($response);
    return in_array($country, $GLOBALS['countries']) ? $country : 'Nigeria';
}

$response = ['success' => false, 'error' => ''];

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registerData'])) {
    $data = json_decode($_POST['registerData'], true);

    if (!empty($data['name']) && !empty($data['email']) && !empty($data['gender']) && !empty($data['country']) && !empty($data['password'])) {
        $name = trim($data['name']);
        $email = trim($data['email']);
        $gender = $data['gender'];
        $country = trim($data['country']);
        $password = $data['password'];

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['error'] = "Invalid email format.";
        } 
        elseif (!in_array($country, $countries)) {
            $response['error'] = "Invalid country selected.";
        } 
        // Minimum 1 character password allowed
        elseif (strlen($password) < 1) {
            $response['error'] = "Password must be at least 1 character long.";
        } 
        else {
            try {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    $response['error'] = "Email already registered.";
                } else {
                    // Hash password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // Insert user
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, gender, passcode, country) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $gender, $hashedPassword, $country]);

                    $userId = $pdo->lastInsertId();

                    // Log user in
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['email'] = $email;
                    $_SESSION['passcode'] = $hashedPassword;

                    $response['success'] = true;
                }
            } catch (PDOException $e) {
                $response['error'] = "Database error. Please try again.";
                file_put_contents('debug.log', 'DB Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            }
        }
    } else {
        $response['error'] = "Please fill all fields.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$detected_country = detectCountryFromIp();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Register for Task Tube to start earning money by watching video ads.">
    <title>Task Tube - Register</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* === Your full CSS remains 100% unchanged (same as before) === */
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#f5f7fa; min-height:100vh; display:flex; flex-direction:column; color:#333; padding-top:80px; padding-bottom:100px; }
        .hero-section { background:linear-gradient(135deg,#6e44ff,#b5179e); color:#fff; text-align:center; padding:100px 20px; position:relative; overflow:hidden; }
        .hero-section::before { content:''; position:absolute; top:0; left:0; right:0; bottom:0; background:url('https://source.unsplash.com/random/1920x1080/?technology') center/cover no-repeat; opacity:0.1; }
        .hero-section h1 { font-size:48px; font-weight:700; margin-bottom:20px; position:relative; z-index:1; }
        .hero-section p { font-size:18px; max-width:600px; margin:0 auto 30px; position:relative; z-index:1; }
        .index-container { max-width:1200px; margin:40px auto; padding:0 20px; }
        .section-title { font-size:36px; text-align:center; margin-bottom:40px; color:#333; }
        .register-content { max-width:500px; margin:0 auto; background:#fff; border-radius:15px; padding:30px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center; }
        .register-content h2 { font-size:28px; margin-bottom:10px; }
        .register-content p span { color:#6e44ff; font-weight:500; }
        .input-field, .country-select { width:100%; height:50px; padding:10px 15px; border:2px solid #e0e0e0; border-radius:10px; margin-bottom:20px; font-size:16px; }
        .country-select { appearance:none; background:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12"><path fill="%23333" d="M6 8.5L0 2.5h12z"/></svg>') no-repeat right 15px center; background-size:12px; }
        .input-field:focus, .country-select:focus { border-color:#6e44ff; box-shadow:0 0 5px rgba(110,68,255,0.3); }
        .gender-options { display:flex; justify-content:center; gap:20px; margin-bottom:20px; }
        .gender-options label { display:flex; align-items:center; gap:5px; cursor:pointer; }
        .gender-options input[type="radio"] { width:18px; height:18px; accent-color:#6e44ff; }
        .submit-btn { background:#6e44ff; color:#fff; border:none; padding:15px; width:100%; font-size:18px; font-weight:500; border-radius:25px; cursor:pointer; transition:.3s; }
        .submit-btn:hover { background:#5a00b5; transform:translateY(-2px); }
        .login-link { margin-top:20px; font-size:14px; color:#666; }
        .login-link a { color:#6e44ff; text-decoration:none; font-weight:500; }
        .login-link a:hover { color:#ff69b4; text-decoration:underline; }
        /* Responsive styles unchanged (kept for brevity) */
        @media (max-width: 768px) { .hero-section h1 { font-size:32px; } .register-content { margin:0 20px; } }
        @media (max-width: 480px) { .gender-options { flex-direction:column; } }
    </style>
</head>
<body>
    <?php include 'inc/header.php'; ?>
    <?php include 'inc/navbar.php'; ?>

    <section class="hero-section">
        <h1>Join Task Tube</h1>
        <p>Create your account to start earning money by watching video ads on our crypto-powered platform.</p>
    </section>

    <div class="index-container">
        <h2 class="section-title">Create Your Account</h2>
        <div class="register-content">
            <h2>Register for <span>Task Tube</span></h2>
            <p>Fill in your details to get started</p>

            <form id="register-form" method="POST">
                <input type="text" id="name" name="name" class="input-field" placeholder="Full Name" required>
                <input type="email" id="email" name="email" class="input-field" placeholder="Email Address" required>
                <input type="password" id="password" name="password" class="input-field" placeholder="Password (at least 1 character)" required>
                <select id="country" name="country" class="country-select" required>
                    <option value="" disabled>Select your country</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?= htmlspecialchars($country) ?>" <?= $country === $detected_country ? 'selected' : '' ?>>
                            <?= htmlspecialchars($country) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="gender-options">
                    <label><input type="radio" name="gender" value="male" required> Male</label>
                    <label><input type="radio" name="gender" value="female"> Female</label>
                    <label><input type="radio" name="gender" value="other"> Other</label>
                </div>

                <button type="submit" class="submit-btn btn">Submit</button>
            </form>

            <p class="login-link">Already have an account? <a href="signin.php">Sign In</a></p>
        </div>
    </div>

    <?php include 'inc/footer.php'; ?>

    <script>
        // Form submission with updated password rule (minimum 1 character)
        document.getElementById('register-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const country = document.getElementById('country').value;
            const gender = document.querySelector('input[name="gender"]:checked')?.value;

            if (!name || !email || !password || !country || !gender) {
                Swal.fire('Error', 'Please fill all fields and select gender.', 'error');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.fire('Invalid Email', 'Please enter a valid email address.', 'error');
                return;
            }

            // Only require at least 1 character
            if (password.length < 1) {
                Swal.fire('Weak Password', 'Password must be at least 1 character long.', 'error');
                return;
            }

            const data = { name, email, password, country, gender };

            $.ajax({
                url: './register.php',
                type: 'POST',
                data: { registerData: JSON.stringify(data) },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Success!', text: 'Account created!', timer: 2000, showConfirmButton: false })
                            .then(() => location.href = './users/home.php');
                    } else {
                        Swal.fire('Error', res.error || 'Registration failed.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Server error. Try again later.', 'error');
                }
            });
        });
    </script>
</body>
</html>

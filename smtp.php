<form class="ion-form" id="form__contact" method="POST" action="sendEmail.php">
                            <div class="ion-form-group ion-form-group-text ion-is-required">
                                <label for="ion_form_name">Name *</label>
                                <input id="ion_form_name" type="text" class="ion-form-control" name="name" required placeholder="Name *">
                            </div>
                            <div class="ion-form-group ion-form-group-email ion-is-required">
                                <label for="ion_form_email">Email *</label>
                                <input id="ion_form_email" type="email" class="ion-form-control" name="email" required placeholder="Email *">
                            </div>
                            <div class="ion-form-group ion-form-group-phone">
                                <label for="ion_form_phone">Phone</label>
                                <input id="ion_form_phone" type="tel" class="ion-form-control" name="phone" placeholder="Phone">
                            </div>
                            <div class="ion-form-group ion-form-group-dropdown">
                                <label for="ion_form_inquiry_type">Inquiry Type *</label>
                                <select id="ion_form_inquiry_type" class="ion-form-control" name="subject" required>
                                    <option value="" disabled selected>Inquiry Type *</option>
                                    <option value="General Inquiry">General Inquiry</option>
                                    <option value="Maintenance Request">Maintenance Request</option>
                                    <option value="Property Availability">Property Availability</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="ion-form-group ion-form-group-textarea ion-is-required">
                                <label for="ion_form_comment">Comment *</label>
                                <textarea id="ion_form_comment" class="ion-form-control" name="message" required placeholder="Comment *"></textarea>
                            </div>
                            
                            <!-- Add the reCAPTCHA widget -->
                         
        <div class="cf-turnstile" data-sitekey="0x4AAAAAAAy_EOg2NETIg9iJ"></div> <!-- Cloudflare Turnstile -->
                    
                            <button type="submit" class="ion-btn">Submit</button>
                        </form>
<?php

// Use statements
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Require PHPMailer autoload file
require "vendor/autoload.php";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input fields
    $name = !empty($_POST["name"]) ? $_POST["name"] : null;
    $email = !empty($_POST["email"]) ? $_POST["email"] : null;
    $subject = !empty($_POST["subject"]) ? $_POST["subject"] : null;
    $message = !empty($_POST["message"]) ? $_POST["message"] : null;
    $phone = !empty($_POST["phone"]) ? $_POST["phone"] : null; 
    $turnstileResponse = !empty($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : null;

    if (!$name || !$email || !$subject || !$message || !$turnstileResponse) {
        echo "All fields are required.";
        exit;
    }

    // Your Cloudflare secret key
    $secretKey = '0x4AAAAAAAy_EOY_Nz0BzmXrAV4dtDVstGg';

    // Verify Turnstile response
    $data = [
        'secret' => $secretKey,
        'response' => $turnstileResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $context);
    
    if ($result === false) {
        echo "Error contacting Cloudflare Turnstile. Please try again.";
        exit;
    }

    // Check for valid JSON before decoding
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Invalid JSON response from Cloudflare Turnstile: " . json_last_error_msg();
        exit;
    }

    $verification = json_decode($result);
    
    // Check if Turnstile verification was successful
    if ($verification && $verification->success) {
        $mail = new PHPMailer(true);  // Create a new PHPMailer instance

        try {
            // SMTP configuration
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Host = 'smtp.gmail.com';                       // Specify SMTP server
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption
            $mail->Port = 587;                                    // TCP port for TLS
            
            $mail->Username = 'hafizwajahathussain46138@gmail.com'; // SMTP username
            $mail->Password = 'ialf fucj zvyp wxrd';               // SMTP password
            
            // Set sender information
            $mail->setFrom($email, $name);                        // From email and name
            $mail->addAddress('hafizwajahathussain46138@gmail.com', 'Dave'); // Add a recipient

            // Email content
            $mail->Subject = $subject;
            $mail->Body    = "Name: " . $name . "\nEmail: " . $email . "\nPhone: " . $phone . "\nSubject: " . $subject . "\n\nMessage: " . $message; 

            // Send email
            if ($mail->send()) {
                header("Location: index.html");
                exit;
            } else {
                $error = $mail->ErrorInfo;
                error_log("Email sending failed: " . $error); // Log the error for debugging
                echo "Email sending failed. Please check your SMTP settings and try again.";
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            error_log("Message could not be sent. Mailer Error: " . $error); // Log the error for debugging
            echo "Message could not be sent. Please check your SMTP settings and try again.";
        }
    } else {
        // Handle Turnstile validation failure or json_decode failure
        echo "CAPTCHA validation or data processing failed. Please try again.";
    }
} else {
    echo "Invalid request method.";
}
?>

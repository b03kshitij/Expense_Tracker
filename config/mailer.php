<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function sendOTP($email, $otp) {

    $mail = new PHPMailer(true);

    try {
        // 🔥 DEBUG ON
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';

        // SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kshitijbhosale76@gmail.com';
        $mail->Password = 'xqjeiaqzkyxoytlm'; // no spaces
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // XAMPP fix
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Email
        $mail->setFrom('kshitijbhosale76@gmail.com', 'Expense Tracker');
        $mail->addAddress($email);

        // HTML Mail
        $mail->isHTML(true);
        $mail->Subject = 'OTP Verification';
        //$mail->Body = "<h2>Your OTP is: $otp</h2>";


        
        // ✅ ONLY DESIGN IMPROVED HERE
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; background:#f4f6f8; padding:20px;">
            
            <div style="max-width:500px; margin:auto; background:#ffffff; padding:25px; border-radius:10px; text-align:center;">
                
                <h2 style="color:#2ecc71; margin-bottom:10px;">Expense Tracker</h2>
                
                <p style="font-size:16px; color:#333;">OTP Verification</p>

                <p style="color:#555;">Use the OTP below to complete your verification.</p>

                <div style="margin:20px 0;">
                    <span style="
                        display:inline-block;
                        font-size:28px;
                        letter-spacing:5px;
                        padding:12px 20px;
                        background:#f1f1f1;
                        border-radius:8px;
                        font-weight:bold;
                        color:#000;
                    ">
                        '.$otp.'
                    </span>
                </div>

                <p style="color:#777; font-size:14px;">
                    This OTP is valid for 5 minutes.<br>
                    Do not share it with anyone.
                </p>

                <hr style="margin:20px 0; border:none; border-top:1px solid #eee;">

                <p style="font-size:12px; color:#aaa;">
                    If you did not request this, you can ignore this email.
                </p>

            </div>
        </div>
        ';

        if (!$mail->send()) {
            echo "ERROR: " . $mail->ErrorInfo;
            exit;
        }

        echo "MAIL SENT"; // debug
        return true;

    } catch (Exception $e) {
        echo "EXCEPTION: " . $mail->ErrorInfo;
        exit;
    }
}
<div style="font-family: Poppins, Arial, sans-serif; color: #2D3436; line-height: 1.6;">
    <h2 style="color: #15803D;">DailyCart OTP Verification</h2>
    <p>Your OTP code is:</p>
    <p style="font-size: 28px; font-weight: 700; letter-spacing: 4px; color: #22C55E;">{{ $code }}</p>
    <p>This code is for {{ str_replace('_', ' ', $purpose) }} and will expire soon.</p>
    <p>If you did not request this code, please ignore this email.</p>
</div>

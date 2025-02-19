@if (session('message'))
    <h4>{{ session('message') }}</h4> <br><br>
@endif

<form action="{{ route('verify.email.submit') }}" method="POST">
    @csrf
    <label for="email">Email:</label>
    <input type="email" name="email" value="{{ $email }}" readonly required>

    <label for="verification_code">Verification Code:</label>
    <input type="text" name="verification_code" required>

    <div id="timer"></div>

    <button type="submit">Verify</button>
</form>

<!-- Resend Code button, initially hidden -->
<button id="resendCode" style="display: none;" onclick="resendCode()">Resend Code</button>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Get expiration time from Laravel in ISO format
    const expiresAt = new Date("{{ $expires_at }}").getTime();

    function startCountdown() {
        const timerElement = document.getElementById("timer");
        const resendButton = document.getElementById("resendCode");

        const interval = setInterval(function() {
            const now = new Date().getTime();
            const remainingTime = expiresAt - now;

            if (remainingTime <= 0) {
                clearInterval(interval);
                timerElement.innerHTML = "The code has expired.";
                resendButton.style.display = "inline"; // Show the resend button
            } else {
                // Calculate remaining minutes and seconds
                const minutes = Math.floor((remainingTime / 1000 / 60) % 60);
                const seconds = Math.floor((remainingTime / 1000) % 60);
                timerElement.innerHTML = `Expires in: ${minutes}m ${seconds}s`;
            }
        }, 1000);
    }

    console.log("Expires At:", new Date(expiresAt)); // Debugging: Check if expiration time is parsed correctly

    startCountdown();
});

function resendCode() {
    window.location.href = "{{ route('resend.verification', ['email' => $email]) }}";
}
</script>

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("passwordForm");
    const passwordInput = document.getElementById("password");
    const errorMessage = document.getElementById("error-message");
  
    form.addEventListener("submit", function (e) {
      const password = passwordInput.value;
  
      if (password.length < 8) {
        e.preventDefault();
        errorMessage.textContent = "Password must be at least 8 characters.";
      } else {
        errorMessage.textContent = "";
      }
    });
  });
 
  document.getElementById("passwordForm").addEventListener("submit", function (e) {
    const otp = document.getElementById("otp").value;
    if (otp.length !== 6) {
      alert("Kode OTP harus 6 digit!");
      e.preventDefault();
    }
  });
  
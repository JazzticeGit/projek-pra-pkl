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
  
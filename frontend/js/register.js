// Wait for DOM to be fully loaded
document.addEventListener("DOMContentLoaded", function () {
  document
    .getElementById("userRegisterForm")
    .addEventListener("submit", async function (e) {
      e.preventDefault();

      const name = document.getElementById("name").value;
      const email = document.getElementById("email").value;
      const password = document.getElementById("password").value;
      const confirmPassword = document.getElementById("confirmPassword").value;

      // Client-side validation
      if (password !== confirmPassword) {
        alert("Passwords do not match!");
        return;
      }

      if (password.length < 6) {
        alert("Password must be at least 6 characters long!");
        return;
      }

      const apiUrl =
        "http://localhost/library-management-system/backend/index.php?action=registerUser";

      console.log("Sending registration request to:", apiUrl);
      console.log("Data:", { name, email, password: "***" });

      try {
        const response = await fetch(apiUrl, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            name: name,
            email: email,
            password: password,
          }),
        });

        console.log("Response status:", response.status);

        const responseText = await response.text();
        console.log("Raw response:", responseText);

        let result;
        try {
          result = JSON.parse(responseText);
        } catch (parseError) {
          console.error("JSON Parse Error:", parseError);
          alert("Server returned invalid response. Check console for details.");
          return;
        }

        if (result.success) {
          alert("Registration successful! You can now login.");
          window.location.href = "login.html";
        } else {
          alert("Registration failed: " + result.message);
        }
      } catch (err) {
        console.error("Fetch Error:", err);
        alert("Error connecting to server: " + err.message);
      }
    });
}); // End of DOMContentLoaded

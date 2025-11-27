// admin-login.js - Admin Only Login

document
  .getElementById("adminLoginForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const email = document.querySelector("input[type=email]").value;
    const password = document.querySelector("input[type=password]").value;

    // Basic validation
    if (!email || !password) {
      alert("Please enter both email and password");
      return;
    }

    try {
      const response = await fetch(
        "http://localhost/php_project/backend/index.php?action=loginUser",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ email, password }),
        }
      );

      const result = await response.json();
      console.log("Admin Login Response:", result);

      if (!result.success) {
        alert(result.message || "Login failed");
        return;
      }

      // ✅ Safely access user data
      const userData = result.data;

      if (!userData) {
        alert("Invalid response from server");
        console.error("No data in response:", result);
        return;
      }

      // ✅ Check if role exists
      if (!userData.role) {
        alert("User role not set. Please contact administrator.");
        console.error("Missing role in user data:", userData);
        return;
      }

      // ✅ ADMIN CHECK - Only allow admin users
      if (userData.role !== "admin") {
        alert("Access Denied: This login is for administrators only.");
        console.log("User role:", userData.role);
        return;
      }

      // Store admin user data
      localStorage.setItem("userId", userData.id);
      localStorage.setItem("name", userData.name || "");
      localStorage.setItem("email", userData.email || "");
      localStorage.setItem("role", userData.role);
      localStorage.setItem("user", JSON.stringify(userData));

      console.log("Admin logged in successfully");

      // Redirect to admin dashboard
      window.location.href = "./index.html";

    } catch (error) {
      console.error("Admin login error:", error);
      alert("An error occurred during login. Please try again.");
    }
  });
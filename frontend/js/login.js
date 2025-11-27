// login.js

document
  .getElementById("adminLoginForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const email = document.querySelector("input[type=email]").value;
    const password = document.querySelector("input[type=password]").value;

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
      console.log(result);

      if (!result.success) {
        alert(result.message);
        return;
      }

      // ✅ FIXED: Use result.data instead of result.user
      const userData = result.data.data;

      // Store login user data individually for easy access
      localStorage.setItem("userId", userData.id);
      localStorage.setItem("name", userData.name);
      localStorage.setItem("email", userData.email);
      localStorage.setItem("role", userData.role);
      
      // Also store complete user object if needed
      localStorage.setItem("user", JSON.stringify(userData));

      // Check role and redirect
      if (userData.role === "admin") {
        window.location.href = "admin/index.html";
      } else {
        alert("You are not admin");
        // Optionally redirect user to user dashboard
        // window.location.href = "../user/dashboard.html";
      }
    } catch (error) {
      console.error("Login error:", error);
      alert("An error occurred during login. Please try again.");
    }
  });
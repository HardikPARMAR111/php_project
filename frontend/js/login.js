document
  .getElementById("userLoginForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    const apiUrl =
      "http://localhost/library-management-system/backend/index.php?action=loginUser";

    console.log("Sending login request to:", apiUrl);
    console.log("Data:", { email, password: "***" });

    try {
      const response = await fetch(apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
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
        alert("Login successful! Welcome " + result.user.name);

        // Store user data in localStorage
        localStorage.setItem("user", JSON.stringify(result.user));

        // Redirect based on role
        if (result.user.role === "admin") {
          window.location.href = "admin-dashboard.html";
        } else {
          window.location.href = "user-dashboard.html";
        }
      } else {
        alert("Login failed: " + result.message);
      }
    } catch (err) {
      console.error("Fetch Error:", err);
      alert("Error connecting to server: " + err.message);
    }
  });

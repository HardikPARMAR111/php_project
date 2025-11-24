document
  .getElementById("adminLoginForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const email = document.querySelector("input[type=email]").value;
    const password = document.querySelector("input[type=password]").value;

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

    // store login user
    localStorage.setItem("user", JSON.stringify(result.user));

    // check role
    if (result.user.role === "admin") {
      window.location.href = "./index.html";
    } else {
      alert("You are not admin");
    }
  });

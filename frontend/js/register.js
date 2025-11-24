const registerForm = document.getElementById("addUserForm");

registerForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const name = document.getElementById("name").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();

  const response = await fetch(
    "http://localhost/php_project/backend/index.php?action=registerUser",
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name, email, password }),
    }
  );

  const result = await response.json();
  console.log(result);
  alert(result.message);

  if (result.success) {
    window.location.href = "view-users.html"; // redirect
  }
});

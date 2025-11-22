document
  .getElementById("addBookForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const title = document.getElementById("title").value;
    const author = document.getElementById("author").value;
    const year = document.getElementById("year").value;

    try {
      const response = await fetch(
        "/library-management-system/backend/index.php?action=addBook",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            title,
            author,
            year,
          }),
        }
      );

      const result = await response.json();
      if (result.success) {
        alert("Book added successfully!");
        document.getElementById("addBookForm").reset();
      } else {
        alert("Failed: " + result.message);
      }
    } catch (err) {
      console.error(err);
      alert("Error connecting to server");
    }
  });

document
  .getElementById("addBookForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();

    const title = document.getElementById("title").value;
    const author = document.getElementById("author").value;
    const year = document.getElementById("year").value;

    // Update this URL to match your setup
    const apiUrl =
      "http://localhost/library-management-system/backend/index.php?action=addBook";

    console.log("Sending request to:", apiUrl);
    console.log("Data:", { title, author, year });

    try {
      const response = await fetch(apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          title: title,
          author: author,
          year: year,
        }),
      });

      console.log("Response status:", response.status);
      console.log("Response headers:", response.headers);

      // Get the raw response text first
      const responseText = await response.text();
      console.log("Raw response:", responseText);

      // Try to parse it as JSON
      let result;
      try {
        result = JSON.parse(responseText);
      } catch (parseError) {
        console.error("JSON Parse Error:", parseError);
        console.error("Response was:", responseText);
        alert("Server returned invalid JSON. Check console for details.");
        return;
      }

      if (result.success) {
        alert("Book added successfully!");
        document.getElementById("addBookForm").reset();
      } else {
        alert("Failed: " + result.message);
        console.error("Server error:", result);
      }
    } catch (err) {
      console.error("Fetch Error:", err);
      alert("Error connecting to server: " + err.message);
    }
  });

// Test function - call this from browser console
async function testAPI() {
  const apiUrl =
    "http://localhost/library-management-system/backend/index.php?action=getBooks";
  console.log("Testing:", apiUrl);

  try {
    const response = await fetch(apiUrl);
    const text = await response.text();
    console.log("Raw response:", text);

    try {
      const json = JSON.parse(text);
      console.log("Parsed JSON:", json);
    } catch (e) {
      console.error("Not valid JSON:", e);
    }
  } catch (err) {
    console.error("Error:", err);
  }
}

console.log("Script loaded. Run testAPI() in console to debug.");

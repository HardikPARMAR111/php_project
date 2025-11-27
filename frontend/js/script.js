console.log("Script loaded");

// =================== ADD BOOK HANDLER ===================
const addBookForm = document.getElementById("addBookForm");

if (addBookForm) {
  addBookForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const title = document.getElementById("title").value;
    const author = document.getElementById("author").value;
    const year = document.getElementById("year").value;

    const apiUrl =
      "http://localhost/php_project/backend/index.php?action=addBook";

    try {
      const response = await fetch(apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ title, author, year }),
      });

      const result = await response.json();
      console.log("Add book response:", result);

      if (result.success) {
        alert("Book added successfully!");
        addBookForm.reset();
      } else {
        alert("Failed: " + result.message);
      }
    } catch (err) {
      console.error("Error adding book:", err);
      alert("Server connection error");
    }
  });
}

async function fetchBooks() {
  try {
    const response = await fetch(
      "http://localhost/php_project/backend/index.php?action=getBooks"
    );
    const books = await response.json();

    console.log("Books data:", books);

    const tableBody = document.getElementById("booksTable");
    tableBody.innerHTML = "";

    books.data.forEach((book) => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${book.title}</td>
        <td>${book.author}</td>
        <td>${book.year}</td>
        <td>
          <button class="btn btn-warning btn-sm" onclick="editBook('${book.id}')">Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deleteBook('${book.id}')">Delete</button>
        </td>
      `;
      tableBody.appendChild(row);
    });
  } catch (err) {
    console.error("Error loading books:", err);
  }
}

async function deleteBook(id) {
  if (!confirm("Are you sure you want to delete this book?")) return;

  const url = `http://localhost/php_project/backend/index.php?action=deleteBook&id=${id}`;
  console.log("Deleting:", url);

  try {
    const response = await fetch(url);
    const text = await response.text();
    console.log("Delete response:", text);

    let result;
    try {
      result = JSON.parse(text);
    } catch (e) {
      console.error("Not valid JSON:", text);
      return;
    }

    if (result.success) {
      alert("Book deleted");
      fetchBooks();
    } else {
      alert("Failed: " + result.message);
    }
  } catch (err) {
    console.error(err);
  }
}

function editBook(id) {
  window.location.href = `editBook.html?id=${id}`;
}

window.onload = fetchBooks;

async function loadBook() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");
  
    if (!id) {
      alert("Invalid book ID");
      return;
    }
  
    const url = `http://localhost/library-management-system/backend/index.php?action=getBook&id=${id}`;
    console.log("Fetching book:", url);
  
    const response = await fetch(url);
    const text = await response.text();
    console.log("Raw response:", text);
  
    let result;
    try {
      result = JSON.parse(text);
    } catch (e) {
      console.error("JSON Parse error:", text);
      return;
    }
  
    if (!result.success) {
      alert(result.message);
      return;
    }
  
    const book = result.data; // access book
  
    document.getElementById("title").value = book.title;
    document.getElementById("author").value = book.author;
    document.getElementById("year").value = book.year;
  }
  
  loadBook();

  async function updateBook() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");
  
    if (!id) {
      alert("Invalid id");
      return;
    }
  
    const updatedBook = {
      id: id,
      title: document.getElementById("title").value,
      author: document.getElementById("author").value,
      year: document.getElementById("year").value,
    };
  
    const response = await fetch("http://localhost/library-management-system/backend/index.php?action=updateBook", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(updatedBook)
      });
  
    const result = await response.json();
    console.log(result);
  
    if (result.success) {
      alert("Book updated successfully");
    } else {
      alert(result.message);
    }
  }
  
  
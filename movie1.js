const toggleBtn = document.getElementById("themeToggle");

let mode = 0; // 0 = light, 1 = dark, 2 = cyberpunk

toggleBtn.addEventListener("click", () => {
    document.body.classList.remove("dark", "cyberpunk");

    if (mode === 0) {
        document.body.classList.add("dark");
        toggleBtn.textContent = "⚡ Cyberpunk";
        mode = 1;
    } 
    else if (mode === 1) {
        document.body.classList.add("cyberpunk");
        toggleBtn.textContent = "☀️ Light Mode";
        mode = 2;
    } 
    else {
        toggleBtn.textContent = "🌙 Dark Mode";
        mode = 0;
    }
});
function addToFavorites(movie) {
    let favorites = JSON.parse(localStorage.getItem("favorites")) || [];

    // prevent duplicate
    if (!favorites.some(fav => fav.imdbID === movie.imdbID)) {
        favorites.push(movie);
        localStorage.setItem("favorites", JSON.stringify(favorites));
        alert("Added to favorites ❤️");
        displayFavorites();
    } else {
        alert("Already in favorites!");
    }
}

function displayFavorites() {
    let favorites = JSON.parse(localStorage.getItem("favorites")) || [];
    let container = document.getElementById("favorites");
    container.innerHTML = "";

    favorites.forEach(movie => {
        container.innerHTML += `
            <div class="favorite-card">
                <img src="${movie.Poster}" width="100"><br>
                <strong>${movie.Title}</strong><br>
                (${movie.Year})
            </div>
        `;
    });
}

// Load favorites on page load
displayFavorites();

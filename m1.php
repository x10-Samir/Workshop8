<?php
include "cloud.php";

$movies = [];
$error = "";

if (isset($_GET['movie']) && !empty($_GET['movie'])) {

    $search = $conn->real_escape_string($_GET['movie']);
    $dbQuery = "
        SELECT * FROM movies
        WHERE title LIKE '%$search%'
        ORDER BY year DESC
    ";

    $dbResult = $conn->query($dbQuery);

    if ($dbResult && $dbResult->num_rows > 0) {

        while ($row = $dbResult->fetch_assoc()) {
            $movies[] = $row;
        }

    } else {
        $apiKey = "b29a0678";
        $url = "https://www.omdbapi.com/?s=" . urlencode($search) . "&apikey=$apiKey";
        $response = @file_get_contents($url);
        $data = json_decode($response, true);

        if ($data && $data["Response"] === "True") {

            foreach ($data["Search"] as $m) {

                $detailUrl = "https://www.omdbapi.com/?i={$m['imdbID']}&plot=short&apikey=$apiKey";
                $detail = json_decode(@file_get_contents($detailUrl), true);

                if (!$detail) continue;

                $title  = $conn->real_escape_string($detail["Title"]);
                $year   = $detail["Year"];
                $type   = $detail["Type"];
                $poster = $detail["Poster"];
                $plot   = $conn->real_escape_string($detail["Plot"]);
                $rating = $detail["imdbRating"] ?? "N/A";
                $genre  = $conn->real_escape_string($detail["Genre"] ?? "N/A");
                $actors = $conn->real_escape_string($detail["Actors"] ?? "N/A");

                
                $check = $conn->query("
                    SELECT id FROM movies
                    WHERE title='$title' AND year='$year'
                ");

                if ($check && $check->num_rows == 0) {
                    $conn->query("
                        INSERT INTO movies 
                        (title, year, type, poster, rating, genre, actors, plot)
                        VALUES 
                        ('$title', '$year', '$type', '$poster', '$rating', '$genre', '$actors', '$plot')
                    ");
                }

                
                $movies[] = [
                    "title" => $title,
                    "year" => $year,
                    "type" => $type,
                    "poster" => $poster,
                    "rating" => $rating,
                    "genre" => $genre,
                    "actors" => $actors,
                    "plot" => $plot
                ];
            }

        } else {
            $error = "Movie not found";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Movie Search App</title>
    <link rel="stylesheet" href="m1.css">
</head>
<body>

<button id="themeToggle" class="theme-toggle">🌙 Dark Mode</button>

<div class="container">
    <h1>🎬 Movie Search App</h1>

    <form method="get" class="search-box">
        <input type="text" name="movie" placeholder="Enter The Movie Name" required>
        <button type="submit">Search</button>
    </form>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <?php if (!empty($movies)): ?>
    <table class="movie-table">
        <thead>
            <tr>
                <th>Poster</th>
                <th>Title</th>
                <th>Year</th>
                <th>Rating</th>
                <th>Genre</th>
                <th>Actors</th>
                <th>Description</th>
                <th>❤️</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($movies as $movie): ?>
            <tr>
                <td>
                    <img src="<?= $movie['poster'] !== 'N/A' ? $movie['poster'] : 'https://via.placeholder.com/80x120' ?>" width="80">
                </td>
                <td><?= htmlspecialchars($movie['title']) ?></td>
                <td><?= $movie['year'] ?></td>
                <td><?= $movie['rating'] ?></td>
                <td><?= htmlspecialchars($movie['genre']) ?></td>
                <td><?= htmlspecialchars($movie['actors']) ?></td>
                <td class="plot"><?= htmlspecialchars($movie['plot']) ?></td>
                <td>
                    <button class="fav-btn"
                        onclick='addToFavorites(<?= json_encode($movie); ?>)'>
                        ❤️
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2 class="fav-title">⭐ Favorite Movies</h2>
    <div id="favorites" class="favorites-container"></div>

    <?php endif; ?>
</div>

<script>
function addToFavorites(movie) {
    let favorites = JSON.parse(localStorage.getItem("favorites")) || [];

    if (!favorites.some(f => f.title === movie.title && f.year === movie.year)) {
        favorites.push(movie);
        localStorage.setItem("favorites", JSON.stringify(favorites));
        alert("Added to favorites ❤️");
        displayFavorites();
    } else {
        alert("Already in favorites ⭐");
    }
}

function displayFavorites() {
    let favorites = JSON.parse(localStorage.getItem("favorites")) || [];
    let container = document.getElementById("favorites");
    if (!container) return;

    container.innerHTML = "";

    favorites.forEach(movie => {
        container.innerHTML += `
            <div class="favorite-card">
                <img src="${movie.poster}" width="80"><br>
                <strong>${movie.title}</strong><br>
                (${movie.year})
            </div>
        `;
    });
}

displayFavorites();
</script>

<script src="movie1.js"></script>
</body>
</html>

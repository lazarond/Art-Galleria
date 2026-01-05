<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Art Galleria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="images/logo.png" alt="Art Galleria Logo">
        </div>
        <nav>
            <ul>
                <li><a href="#hero-section">Main</a></li>
                <li><a href="#art-appreciation-section">Art Appreciation</a></li>
                <li><a href="#goal-section">Our Goal</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero-section">
        <div class="hero-text">
            <h1>ART<br>GALLERIA</h1>
        </div>
        <div class="hero-cta">
            <h2>Discover.<b>Bid.</b>Collect</h2>
            <button class="auction-btn" onclick="window.location.href='register.php';">Take part in the auction</button>
        </div>
    </section>  

    <section class="featured-art">
        <img src="images/landing_bg.jpg" alt="Featured Art">
    </section>

    <section class="art-appreciation-section" id="art-appreciation">
        <h3>Art Appreciation</h3>
        <div class="art-appreciation-cards">

            <!-- Art Card 1 -->
            <div class="art-appreciation-card">
                <img src="images/art1.jpg" alt="Art 1">
                <div class="art-appreciation-card-body">
                    <h4>The Beauty of Nature</h4>
                    <p>This art piece captures the serene and peaceful elements of nature through vibrant colors.</p>
                </div>
            </div>

            <!-- Art Card 2 -->
            <div class="art-appreciation-card">
                <img src="images/art2.jpg" alt="Art 2">
                <div class="art-appreciation-card-body">
                    <h4>Modern Abstract</h4>
                    <p>An abstract piece that explores the connection between modernity and creativity.</p>
                </div>
            </div>

            <!-- Art Card 3 -->
            <div class="art-appreciation-card">
                <img src="images/art3.jpg" alt="Art 3">
                <div class="art-appreciation-card-body">
                    <h4>Classical Portrait</h4>
                    <p>This classical portrait evokes a sense of timelessness and human emotion through careful brushwork.</p>
                </div>
            </div>

            <!-- Art Card 4 -->
            <div class="art-appreciation-card">
                <img src="images/art4.jpg" alt="Art 4">
                <div class="art-appreciation-card-body">
                    <h4>Colorful Expressionism</h4>
                    <p>A bold and vibrant expression of emotion through energetic brushstrokes and rich hues.</p>
                </div>
            </div>

            <!-- Art Card 5 -->
            <div class="art-appreciation-card">
                <img src="images/art5.jpg" alt="Art 5">
                <div class="art-appreciation-card-body">
                    <h4>Urban Landscape</h4>
                    <p>Capturing the raw beauty of city life, this art piece celebrates the chaos and harmony of urban environments.</p>
                </div>

            </div>
        </div>
    </section>

    <section class="goal-section">
        <h3>Our Goal</h3>
        <p>We aim to provide a platform where art lovers and collectors can engage in meaningful auctions, fostering an appreciation for creativity and artistry.</p>
    </section>

</body>
</html>
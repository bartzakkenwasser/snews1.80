<?php
ob_start();
// Zet foutrapportage VOLLEDIG aan
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Laad het sNews core-bestand veilig
require_once __DIR__ . '/snews.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <!-- Titel + meta tags veilig renderen -->
    <?php title(); ?>

    <meta name="robots" content="index,follow">
    <meta name="author" content="Solucija.com">

    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div id="wrapper">

    <header id="header">
        <div id="top">
            <ul id="topmenu">
                <?php pages(); ?>
            </ul>

            <div id="search">
                <?php searchform(); ?>
            </div>
        </div>

        <div id="logo">
            <h1><?= htmlspecialchars(s('website_title') ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars(s('website_description') ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </header>

    <div id="crumbs">
        <?php breadcrumbs(); ?>
    </div>

    <div id="content">
        <main id="main">
            <?php center(); ?>
        </main>

        <aside id="side">

            <div class="single">
                <h3>Categories</h3>
                <ul>
                    <?php categories(); ?>
                </ul>

                <h3>RSS Feeds</h3>
                <ul>
                    <?php rss_links(); ?>
                </ul>

                <?php extra(); ?>
            </div>

            <div class="single">
                <h3>New Posts</h3>
                <ul>
                    <?php menu_articles(0, 3, 1); ?>
                </ul>

                <h3>New Comments</h3>
                <ul>
                    <?php new_comments(5, 30); ?>
                </ul>

                <!-- Verwijderd: other_function() bestaat niet -->
            </div>

        </aside>
    </div>

    <footer id="footer">
        <p>
            This site is powered by
            <a href="http://snewscms.com/" target="_blank" rel="noopener">sNews</a>
            |
            <?php login_link(); ?>
        </p>
    </footer>

</div>
</body>
</html>

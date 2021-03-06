<html>
<head>
    <?php include __DIR__."/../components/head_content.php" ?>
    <script src="/assets/js/home.js"></script>
    <title>Mentor Match</title>
</head>
<body>
<?php include __DIR__."/../components/navbar.php" ?>
<div id="form" class="container mt-3 text center">
    <div class="row">
        <div class="col-6">
            <h4>My Projects</h4>
        </div>
        <div class="col-6 text-end">
            <a href="/create" class="btn btn-success">
                Create New
            </a>
        </div>
    </div>
</div>
<div id="project-container" class="container py-3">
    <!--projects inserted dynamically via JS-->
    <div id="loading-icon" class="row justify-content-center p-5 m-5">
        <div class="spinner-border text-secondary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
</body>
</html>
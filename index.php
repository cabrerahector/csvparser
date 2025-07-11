<?php
// Handle .csv uploads
require 'inc/class.csvuploader.php';
$csv_uploader = new CSVUploader();

// Load .csv file
require 'inc/class.csvloader.php';
require 'inc/class.csvparser.php';
$csv_loader = new CSVLoader();
$csv_parser = new CSVParser($csv_loader);

$data = $csv_parser->parse();

// Create TableBuilder instance
require 'inc/class.tablebuilder.php';
$table_builder = new TableBuilder($data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Parser</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <link href="assets/css/style.css" rel="stylesheet" />

    <?php $csv_uploader->maybe_replace_history_state(); ?>
</head>
<body>
    <main>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="csv_file">Select your .csv file:</label>
            <input type="file" id="csv_file" name="csv_file" accept=".csv" required />
            <button>Upload</button>
        </form>

        <?php echo $table_builder->build(); ?>
    </main>
</body>
</html>

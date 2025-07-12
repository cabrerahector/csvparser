<?php
/**
 * Handles CSV uploads
 *
 * @author Hector Cabrera <me@cabrerahector.com>
 */

class CSVUploader
{
    /**
     * Path to the uploads folder.
     */
    private $uploads_dir;

    /**
     * Captures error message, if any.
     *
     * @access private
     * @var    string
     */
    private $error;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->error = '';
        $this->uploads_dir = dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'uploads';

        if ( isset($_FILES['csv_file']['error']) ) {
            $this->handle_upload();
        }
    }

    /**
     * Handles .csv file upload.
     */
    private function handle_upload()
    {
        // Multiple file uploads? Bail.
        if ( is_array($_FILES['csv_file']['error']) ) {
            $this->error = 'invalid_upload';
            return;
        }

        // Something went wrong with the upload, bail.
        if ( $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK ) {
            $this->error = 'invalid_upload';
            return;
        }

        // File is too big, bail.
        if ( $_FILES['csv_file']['size'] > 10485760 ) { // 10485760 = 10 MB
            $this->error = 'upload_too_big';
            return;
        }

        // Check mime type.
        $allowed_mime_types = array('text/plain', 'text/csv', 'text/comma-separated-values');

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $upload_file_ext = finfo_file($finfo, $_FILES['csv_file']['tmp_name']);

        // This doesn't seem to be a valid .csv file, bail.
        if ( ! in_array($upload_file_ext, $allowed_mime_types) ) {
            $this->error = 'invalid_file';
            return;
        }

        // Upload file.
        $path = $this->uploads_dir . DIRECTORY_SEPARATOR . 'inventory.csv';

        // Create uploads folder if it doesn't exist.
        if ( ! file_exists($this->uploads_dir) ) {
            mkdir($this->uploads_dir, 0755, true);
        }

        if ( ! move_uploaded_file($_FILES['csv_file']['tmp_name'], $path) ) {
            $this->error = 'could_not_upload';
        } else {
            // Set file permissions
            chmod($path, 0644);
        }
    }

    /**
     * Prevents re-POST on page refresh.
     */
    public function maybe_replace_history_state()
    {
        if ( isset($_FILES['csv_file']['error']) ) {
            echo '<script>history.replaceState(null, document.title, location.href);</script>';
        }
    }

    /**
     * Returns error message, if any.
     *
     * @return string
     */
    public function has_error()
    {
        return $this->error;
    }
}

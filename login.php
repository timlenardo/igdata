<?php
    session_start();
    require __DIR__.'/vendor/autoload.php';

    $username = $_POST["username"];
    $password = $_POST["password"];

    $db_servername = "localhost";
    $db_username = "scraping";
    $db_password = "7DusDaCRVwztxjZFmecEPaQk";
    $db_tablename = "igdata";

    $conn = new mysqli($db_servername, $db_username, $db_password, $db_tablename);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // TODO use proxies!
    $debug = false;
    $truncatedDebug = true;
    \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
    $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

    try {
        $ig->login($username, $password);
        $maxId = null;
        do {
            $response = $ig->timeline->getSelfUserFeed($maxId);
            $maxId = $response->getNextMaxId();
            $posted_items = $response->getItems();

            foreach ($posted_items as $item) {
                $pk = $item->getPk();
                $time_epoch = $item->getTakenAt();
                $timestamp = date("Y-m-d H:i:s", $time_epoch);
                $num_likes = $item->getLikeCount();
                $user_id = $item->getUser()->getPk();
                $username= $item->getUser()->getUsername();

                $insert_query = "INSERT INTO posts (pk, num_likes, timestamp, username, user) VALUES ('".$pk."', '".$num_likes."', '".$timestamp."', '".$username."', '".$user_id."')";
                mysqli_query($conn, $insert_query);
            }
        } while ($maxId !== null);

        // $mediaMaxId = null;
        // do {
        //     $response = $ig->media->getLikedFeed($mediaMaxId);
        //     $mediaMaxId = $response->getNextMaxId();
        //     $liked_items = $response->getItems();
        //
        //     foreach ($liked_items as $item) {
        //         $pk = $item->getPk();
        //         $time_epoch = $item->getTakenAt();
        //         $timestamp = date("Y-m-d H:i:s", $time_epoch);
        //         $user_id = $item->getUser()->getPk();
        //         $username= $item->getUser()->getUsername();
        //
        //         $insert_query = "INSERT INTO likes (pk, user, username, timestamp) VALUES ('".$pk."', '".$user_id."', '".$username."', '".$timestamp."')";
        //         mysqli_query($conn, $insert_query);
        //     }
        //     sleep(5);
        // } while ($mediaMaxId !== null);

        $insert_query = "UPDATE accounts SET processing_status='succeeded' WHERE username='".$username."'";
        mysqli_query($conn, $insert_query);
        $_SESSION['logged_in'] = 1;
        $_SESSION['username'] = $username;

    } catch (\Exception $e) {
        $insert_query = "UPDATE accounts SET processing_status='failed' WHERE username='".$username."'";
        mysqli_query($conn, $insert_query);
        $_SESSION['logged_in'] = -1;
    }

    $conn->close();
    header("Location: index.php"); /* Redirect browser */
    exit();
?>

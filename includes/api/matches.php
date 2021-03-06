<?php
    include_once __DIR__.'/../utilities/Matcher.php';

    if (!isset($_GET['id'])) {
        http_response_code( "400");
        echo json_encode("Missing ID parameter.");
        die;
    }

    try {
        // GET
        if($_SERVER["REQUEST_METHOD"] === "GET") {
            $sql = "SELECT mp.match_id, mp.date_created, mp.confidence_rate, a.application_id AS 'mentee_application_id',
            a.first_name AS 'mentee_first_name', a.last_name AS 'mentee_last_name', a.email AS 'mentee_email',
            a2.application_id AS 'mentor_application_id', a2.first_name AS 'mentor_first_name', a2.last_name AS 'mentor_last_name', a2.email AS 'mentor_email'
            FROM match_pair mp
            JOIN application a ON mp.mentee_application_id = a.application_id
            JOIN application a2 ON mp.mentor_application_id = a2.application_id
            WHERE mp.match_form_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                ':id' => $_GET['id']
            ));
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // send json back
            echo json_encode($data);
            die;
        }

        // POST -- match all unmatched mentees
        // TODO: if application_id not set, matchs all unmatches. If application_id set, matches that specific mentee
        if($_SERVER["REQUEST_METHOD"] === "POST") {

            // make new matcher
            $matcher = new matcher($_GET['id'], $pdo);
            $matcher->match_open_mentees();

            // get matches, return them as JSON
            $sql = "SELECT mp.match_id, mp.date_created, mp.confidence_rate, a.application_id AS 'mentee_application_id',
            a.first_name AS 'mentee_first_name', a.last_name AS 'mentee_last_name', a.email AS 'mentee_email',
            a2.application_id AS 'mentor_application_id', a2.first_name AS 'mentor_first_name', a2.last_name AS 'mentor_last_name', a2.email AS 'mentor_email'
            FROM match_pair mp
            JOIN application a ON mp.mentee_application_id = a.application_id
            JOIN application a2 ON mp.mentor_application_id = a2.application_id
            WHERE mp.match_form_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                ':id' => $_GET['id']
            ));
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // send response
            echo json_encode($data);
            die;
        }

        // DELETE
        if($_SERVER["REQUEST_METHOD"] === "DELETE") {
            if(!isset($_GET['match_id'])) {
                throw new Exception("match_id parameter is mandatory", 400);
            } else {
                $sql = "DELETE FROM match_pair 
                    WHERE match_form_id = :match_form_id
                    AND match_id = :match_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(
                    ':match_form_id' => htmlentities($_GET['id']),
                    ':match_id' => htmlentities($_GET['match_id'])
                ));
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // send response
                http_response_code( "204");
                die;
            }
        }
    } catch (Exception $e) {
        //TODO figure out how to handle PDO exceptions...
        http_response_code($e->getCode() ? $e->getCode() : "500");
        echo json_encode($e->getMessage() ? $e->getMessage() : "Internal Server Error");
        die;
    }
?>
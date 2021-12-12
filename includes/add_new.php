<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once '../db/pdo.php';
        $response = Array();
        try {
            // server side validation
            foreach ($_POST as $key => $value) {
                if ($key === 'questions') {
                    foreach($_POST['questions'] as $question) {
                        if ($question['question'] === '') {
                            throw new Exception("Questions cannot be blank.", 400);
                        }
                        foreach($question['options'] as $option) {
                            if ($option === '') {
                                throw new Exception("Question options cannot be blank.", 400);
                            }
                        }
                    }
                } else if ($_POST[$key] === ''){
                    throw new Exception("$key cannot be blank.", 400);
                }
            }

            // add to DB
            $sql = "INSERT INTO match_form (title, mentor_desc, mentee_desc, mentor_app_open, mentee_app_open,
                    collect_first_name, collect_last_name, collect_email, collect_phone, collect_stud_id
                    ) VALUES (:title, :mentor_desc, :mentee_desc, :mentor_app_open, :mentee_app_open,
                      :collect_first_name, :collect_last_name, :collect_email, :collect_phone, :collect_stud_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                ':title' => htmlentities($_POST['title']),
                ':mentor_desc' => htmlentities($_POST['mentorDescription']),
                ':mentee_desc' => htmlentities($_POST['menteeDescription']),
                ':mentor_app_open' => $_POST['mentorApplicationStatus'] === 'true',
                ':mentee_app_open' => $_POST['menteeApplicationStatus'] === 'true',
                ':collect_first_name' => $_POST['collectFirstName'] === 'true',
                ':collect_last_name' => $_POST['collectLastName'] === 'true',
                ':collect_email' => $_POST['collectEmail'] === 'true',
                ':collect_phone' => $_POST['collectPhone'] === 'true',
                ':collect_stud_id' => $_POST['collectStudentID'] === 'true'
            ));
            $match_form_id = $pdo->lastInsertId();

            // loop through questions and options save to db
            foreach($_POST['questions'] as $question) {
                $sql = "INSERT INTO question (match_form_id, priority, question_text) VALUES ( :match_form_id, :priority, :question_text);";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(
                    ':match_form_id' => htmlentities($match_form_id),
                    ':priority' => htmlentities($question['priority']),
                    ':question_text' => htmlentities($question['question'])
                ));
                $question_id = $pdo->lastInsertId();

                foreach($question['options'] as $option) {
                    $sql = "INSERT INTO question_option (question_id, option_text) VALUES ( :question_id, :option_text);";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array(
                        ':question_id' => htmlentities($question_id),
                        ':option_text' => htmlentities($option)
                    ));
                }
            }

            http_response_code(200);
            $response['message'] = 'Data loaded to DB.';
        } catch (Exception $e) {
            http_response_code($e->getCode());
            $response['message'] = $e->getMessage();
        }

        // send response to browser
        echo json_encode($response);
        die;
    }
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script
            src="https://code.jquery.com/jquery-3.6.0.js"
            integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
            crossorigin="anonymous"></script>
    <script src="./assets/add_new.js"></script>
    <title>Mentor Match</title>
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">Mentor Match</span>
    </div>
</nav>
<div class="container mt-3">
    <form action="">
        <div class="mb-3">
            <h5>General</h5>
            <label for="title" class="form-label">Title</label>
            <input type="text" id="title" class="form-control" required>
            <div class="form-text">This name will be displayed publicly on forms. It is best to make it clean, clear, and descriptive. Example: "Bachelor Programs - 2021-2022"</div>
            <br>
            <label for="mentor-description" class="form-label">Mentor Description</label>
            <textarea class="form-control" id="mentor-description" cols="30" rows="3" required></textarea>
            <br>
            <label for="mentee-description" class="form-label">Mentee Description</label>
            <textarea class="form-control" id="mentee-description" cols="30" rows="3" required></textarea>
            <br>
            <label for="mentor-application-status" class="form-label">Mentor Application Status</label>
            <select class="form-select" id="mentor-application-status" aria-label="Mentor Application Status" disabled>
                <option value="open">Open</option>
                <option value="closed" selected>Closed</option>
            </select>
            <br>
            <label for="mentee-application-status" class="form-label">Mentee Application Status</label>
            <select class="form-select" id="mentee-application-status" aria-label="Mentee Application Status" disabled>
                <option value="open">Open</option>
                <option value="closed" selected>Closed</option>
            </select>
            <br>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="collect-first-name" checked disabled>
                <label class="form-check-label" for="collect-first-name">Ask for first name</label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="collect-last-name" checked disabled>
                <label class="form-check-label" for="collect-last-name">Ask for last name</label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="collect-email" checked disabled>
                <label class="form-check-label" for="collect-email">Ask for email</label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="collect-phone" checked>
                <label class="form-check-label" for="collect-phone">Ask for phone number</label>
                <div class="form-text">This name will be displayed publicly on forms. It is best to make it clean, clear, and descriptive. Example: "Bachelor Programs - 2021-2022"</div>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="collect-student-id" checked>
                <label class="form-check-label" for="collect-student-id">Ask for student ID</label>
            </div>
        </div>
        <br>
        <hr>
        <br>
        <div class="mb-3">
            <h5>Matching</h5>
            <p>Add new matching questions in the section below. For each question you will need to insert the question options (the choices presented to the applicants). Applicants will be matched based on these questions, with priority given to the questions listed first.</p>
            <div id="question-container">
                <div class="question card text-dark bg-light mb-3">
                    <div class="card-header">
                        <b>
                            Question
                        </b>
                        <button type="button" class="remove-question btn btn-sm btn-outline-danger float-end">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <input type="text" class="question-name form-control mb-3" required>
                        <div class="option-container">
                            <div class="option input-group input-group-sm mb-2">
                                <span class="input-group-text">Option</span>
                                <button class="delete-option-button btn btn-outline-danger" type="button">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <input type="text" class="option-name form-control" required>
                            </div>
                        </div>
                        <button class="add-option-button btn btn-sm btn-secondary">Add Option</button>
                    </div>
                </div>
            </div>
            <button id="add-question-button" class="btn btn-primary">
                Add question
            </button>
        </div>
        <button id="submit-button" class="btn btn-success btn-lg mb-3" type="submit">Submit</button>
    </form>
</div>
</body>
</html>

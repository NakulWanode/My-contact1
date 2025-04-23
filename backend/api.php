<?php
$servername = "localhost"; // Change if your MySQL server is different
$username = "root"; // Replace with your MySQL username
$password = "root"; // Replace with your MySQL password
$dbname = "contact_book"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $path = explode('/', $_SERVER['REQUEST_URI']);
        $id = isset($path[3]) && is_numeric($path[3]) ? intval($path[3]) : null; // Assuming /api/contacts/{id}
        if ($id) {
            $sql = "SELECT * FROM contacts WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                echo json_encode($result->fetch_assoc());
            } else {
                http_response_code(404);
                echo json_encode(array('error' => 'Contact not found'));
            }
            $stmt->close();
        } else {
            $sql = "SELECT * FROM contacts";
            $result = $conn->query($sql);
            $contacts = array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $contacts[] = $row;
                }
            }
            echo json_encode($contacts);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $conn->real_escape_string($data['name']);
        $phone = $conn->real_escape_string($data['phone_number']);
        $email = $conn->real_escape_string($data['email']);
        $address = $conn->real_escape_string($data['address']);

        $sql = "INSERT INTO contacts (name, phone_number, email, address) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $phone, $email, $address);
        if ($stmt->execute()) {
            echo json_encode(array('message' => 'Contact added successfully', 'id' => $conn->insert_id));
        } else {
            http_response_code(500);
            echo json_encode(array('error' => 'Error adding contact: ' . $stmt->error));
        }
        $stmt->close();
        break;

    case 'PUT':
        $path = explode('/', $_SERVER['REQUEST_URI']);
        $id = isset($path[3]) && is_numeric($path[3]) ? intval($path[3]) : null; // Assuming /api/contacts/{id}
        if ($id) {
            $data = json_decode(file_get_contents('php://input'), true);
            $name = $conn->real_escape_string($data['name']);
            $phone = $conn->real_escape_string($data['phone_number']);
            $email = $conn->real_escape_string($data['email']);
            $address = $conn->real_escape_string($data['address']);

            $sql = "UPDATE contacts SET name=?, phone_number=?, email=?, address=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $name, $phone, $email, $address, $id);
            if ($stmt->execute()) {
                echo json_encode(array('message' => 'Contact updated successfully'));
            } else {
                http_response_code(500);
                echo json_encode(array('error' => 'Error updating contact: ' . $stmt->error));
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(array('error' => 'Invalid contact ID for update'));
        }
        break;

    case 'DELETE':
        $path = explode('/', $_SERVER['REQUEST_URI']);
        $id = isset($path[3]) && is_numeric($path[3]) ? intval($path[3]) : null; // Assuming /api/contacts/{id}
        if ($id) {
            $sql = "DELETE FROM contacts WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(array('message' => 'Contact deleted successfully'));
            } else {
                http_response_code(500);
                echo json_encode(array('error' => 'Error deleting contact: ' . $stmt->error));
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(array('error' => 'Invalid contact ID'));
        }
        break;

    case 'OPTIONS':
        http_response_code(200);
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(array('error' => 'Method Not Allowed'));
        break;
}

$conn->close();
?>

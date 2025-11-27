<?php
require_once __DIR__ . "/../models/rental.php";
require_once __DIR__ . "/../models/book.php";
require_once __DIR__ . "/../models/user.php"; // if needed for validation
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;

class RentalController {

    private function setHeaders() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Content-Type: application/json");
    }

    private function allowOptions() {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $this->setHeaders();
            http_response_code(200);
            exit();
        }
    }

    public function __construct() {
        $this->allowOptions();
        $this->setHeaders();
    }

    // Rent a book
    public function rentBook() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $bookId = $data['book_id'] ?? null;
        $userId = $data['user_id'] ?? null;
        $rentDays = isset($data['days']) ? (int)$data['days'] : 30; // default 30 days

        if (!$bookId || !$userId) {
            echo json_encode(["success" => false, "message" => "book_id and user_id required"]);
            return;
        }

        try {
            $bookModel = new Book();
            $book = $bookModel->getBookById($bookId); // you already have this method in BookController; if not, implement in Book model: find by id

            // ensure book is available
            if (!$book || ($book['status'] ?? 'available') !== 'available') {
                echo json_encode(["success" => false, "message" => "Book is not available"]);
                return;
            }

            // create rental record
            $now = new UTCDateTime();
            $due = new UTCDateTime((time() + ($rentDays * 86400)) * 1000);

            $rentalModel = new Rental();
            $rentalData = [
                'book_id' => $bookId,
                'user_id' => $userId,
                'rented_at' => $now,
                'due_date' => $due,
                'returned_at' => null,
                'status' => 'rented'
            ];

            $res = $rentalModel->create($rentalData);

            // update book status and store current borrower (optional)
            $bookModel->updateStatus($bookId, 'rented', ['borrower_id' => $userId]);

            echo json_encode(["success" => true, "message" => "Book rented", "rental_id" => (string)$res->getInsertedId()]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }

    // Return a book
    public function returnBook() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $rentalId = $data['rental_id'] ?? null;
        $userId = $data['user_id'] ?? null;

        if (!$rentalId || !$userId) {
            echo json_encode(["success" => false, "message" => "rental_id and user_id required"]);
            return;
        }

        try {
            $rentalModel = new Rental();
            $rental = $rentalModel->findById($rentalId);

            if (!$rental) {
                echo json_encode(["success" => false, "message" => "Rental not found"]);
                return;
            }

            if ($rental['user_id'] !== $userId) {
                // optional: allow admin to return on behalf
                echo json_encode(["success" => false, "message" => "You are not the renter"]);
                return;
            }

            if (($rental['status'] ?? '') === 'returned') {
                echo json_encode(["success" => false, "message" => "Already returned"]);
                return;
            }

            // set returned_at and status
            $returnedAt = new UTCDateTime();

            $rentalModel->update($rentalId, [
                'returned_at' => $returnedAt,
                'status' => 'returned'
            ]);

            // update book status to available and remove borrower_id
            $bookModel = new Book();
            $bookModel->updateStatus($rental['book_id'], 'available', ['borrower_id' => null]);

            echo json_encode(["success" => true, "message" => "Book returned"]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }

    // Get all rentals (admin)
    public function getRentals() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        try {
            $rentalModel = new Rental();
            $rentals = $rentalModel->getAll();

            // format dates
            $out = array_map(function($r) {
                return [
                    'id' => (string)$r['_id'],
                    'book_id' => $r['book_id'],
                    'user_id' => $r['user_id'],
                    'rented_at' => isset($r['rented_at']) ? $r['rented_at']->toDateTime()->format(DATE_ATOM) : null,
                    'due_date' => isset($r['due_date']) ? $r['due_date']->toDateTime()->format(DATE_ATOM) : null,
                    'returned_at' => isset($r['returned_at']) ? $r['returned_at']->toDateTime()->format(DATE_ATOM) : null,
                    'status' => $r['status'] ?? null
                ];
            }, $rentals);

            echo json_encode(['success' => true, 'data' => $out]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }

    // Get rentals for a specific user
    public function getUserRentals() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(["success" => false, "message" => "Method not allowed"]);
            return;
        }

        $userId = $_GET['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(["success" => false, "message" => "user_id required"]);
            return;
        }

        try {
            $rentalModel = new Rental();
            $rentals = $rentalModel->findByUserId($userId);

            $out = array_map(function($r) {
                return [
                    'id' => (string)$r['_id'],
                    'book_id' => $r['book_id'],
                    'rented_at' => isset($r['rented_at']) ? $r['rented_at']->toDateTime()->format(DATE_ATOM) : null,
                    'due_date' => isset($r['due_date']) ? $r['due_date']->toDateTime()->format(DATE_ATOM) : null,
                    'returned_at' => isset($r['returned_at']) ? $r['returned_at']->toDateTime()->format(DATE_ATOM) : null,
                    'status' => $r['status'] ?? null
                ];
            }, $rentals);

            echo json_encode(['success' => true, 'data' => $out]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }
}

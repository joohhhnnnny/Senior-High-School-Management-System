<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up error logging
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xamppNew/htdocs/CST5-PROJECT/FINAL-ADMIN/trace_error.log');

require_once '../Portal-Main/conn.php';

class SQLTrace {
    private $conn;
    private static $queries = [];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function executeQuery($sql, $params = []) {
        self::$queries[] = [
            'sql' => $sql,
            'params' => $params
        ];

        try {
            // Prepare statement
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }

            // If we have parameters, bind them with correct types
            if (!empty($params)) {
                // Get the number of ? in the SQL
                $paramCount = substr_count($sql, '?');
                
                // Make sure we have the right number of parameters
                if (count($params) !== $paramCount) {
                    throw new Exception("Parameter count mismatch. Expected $paramCount, got " . count($params));
                }

                // Create the types string based on parameter values
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                }

                // Bind parameters with correct types
                if ($paramCount > 0) {
                    $bindParams = array_merge([$types], $params);
                    $stmt->bind_param(...$bindParams);
                }
            }

            // Execute and store result
            $success = $stmt->execute();
            $result = $stmt->get_result();
            $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : null;
            
            $stmt->close();

            return [
                'success' => $success,
                'data' => $data
            ];

        } catch (Exception $e) {
            error_log("Query execution error: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getQueries() {
        return self::$queries;
    }
}

try {
    $tracer = new SQLTrace($conn);
    $id = isset($_GET['id']) ? intval($_GET['id']) : 20;

    error_log("Starting trace for ID: " . $id);

    // Test each query with proper parameters
    $queries = [
        [
            "sql" => "SELECT * FROM studentpendingenroll WHERE id = ?",
            "params" => [$id]
        ],
        [
            "sql" => "SELECT * FROM student WHERE studentID = ?",
            "params" => [$id]
        ],
        [
            "sql" => "SELECT * FROM sections WHERE yearLevel = ? AND strand = ?",
            "params" => ['11', 'ICT'] // Example values
        ]
    ];

    $results = [];
    foreach ($queries as $query) {
        $results[] = $tracer->executeQuery($query['sql'], $query['params']);
    }

    echo json_encode([
        'success' => true,
        'queries' => SQLTrace::getQueries(),
        'results' => $results,
        'message' => 'Trace complete'
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Trace error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>

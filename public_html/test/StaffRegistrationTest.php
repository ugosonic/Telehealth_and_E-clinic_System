<?php

use PHPUnit\Framework\TestCase;

class StaffRegistrationTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        // Mock database connection
        $this->db = new mysqli('localhost', 'root', '', 'test_database');
        if ($this->db->connect_error) {
            die('Database connection failed: ' . $this->db->connect_error);
        }

        // Create a mock users table for testing
        $this->db->query("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(100),
                middle_name VARCHAR(100),
                surname VARCHAR(100),
                date_of_birth DATE,
                sex ENUM('Male', 'Female', 'Other'),
                username VARCHAR(100) UNIQUE,
                password VARCHAR(255),
                email VARCHAR(100) UNIQUE,
                phone_number VARCHAR(20),
                usergroup VARCHAR(50),
                profile_pic VARCHAR(255)
            )
        ");
    }

    protected function tearDown(): void
    {
        // Clean up the database
        $this->db->query("DROP TABLE IF EXISTS users");
        $this->db->close();
    }

    public function testInvalidEmailFormat()
    {
        $email = "invalid-email";
        $this->assertFalse(filter_var($email, FILTER_VALIDATE_EMAIL), "Invalid email format passed validation.");
    }

    public function testDuplicateUsername()
    {
        // Insert a test user
        $this->db->query("
            INSERT INTO users (username, email, password)
            VALUES ('testuser', 'test@example.com', 'password123')
        ");

        // Check for duplicate username
        $username = "testuser";
        $result = $this->db->query("SELECT * FROM users WHERE username = '$username'");
        $this->assertTrue($result->num_rows > 0, "Duplicate username check failed.");
    }

    public function testSuccessfulRegistration()
    {
        $hashedPassword = password_hash("password123", PASSWORD_DEFAULT);
        $query = "
            INSERT INTO users (first_name, surname, username, password, email, usergroup)
            VALUES ('John', 'Doe', 'johndoe', '$hashedPassword', 'johndoe@example.com', 'Doctor')
        ";

        $result = $this->db->query($query);
        $this->assertTrue($result, "Failed to register a new user.");

        // Verify the user was inserted
        $result = $this->db->query("SELECT * FROM users WHERE username = 'johndoe'");
        $this->assertTrue($result->num_rows === 1, "User was not successfully registered in the database.");
    }
}

?>

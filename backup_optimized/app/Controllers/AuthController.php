<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Helpers\Response;
use App\Helpers\Validator;
use App\Helpers\JWT;
use App\Core\Controller;

class AuthController extends Controller
{
    private \mysqli $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->connection();
    }

    public function register(): void
    {
        $data = $this->body();

        (new Validator())
            ->required($data, [
                'name',
                'email',
                'password',
                'password_confirmation'
            ])
            ->email($data, 'email')
            ->min($data, 'password', 8)
            ->confirmed($data, 'password')
            ->validate();

        $stmt = $this->db->prepare(
            "SELECT id FROM users WHERE email=? LIMIT 1"
        );

        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            Response::error("Email already exists.", 409);
        }

        $stmt->close();

        $password = password_hash(
            $data['password'],
            PASSWORD_BCRYPT
        );

        $stmt = $this->db->prepare(
            "INSERT INTO users(name,email,password_hash)
             VALUES(?,?,?)"
        );

        $stmt->bind_param(
            "sss",
            $data['name'],
            $data['email'],
            $password
        );

        $stmt->execute();

        $id = $stmt->insert_id;

        $stmt->close();

        $token = JWT::encode([
            'user_id' => $id,
            'email' => $data['email']
        ]);

        Response::created([
            'token' => $token,
            'user' => [
                'id' => $id,
                'name' => $data['name'],
                'email' => $data['email']
            ]
        ]);
    }

    public function login(): void
    {
        $data = $this->body();

        (new Validator())
            ->required($data, [
                'email',
                'password'
            ])
            ->email($data, 'email')
            ->validate();

        $stmt = $this->db->prepare(
            "SELECT
                id,
                name,
                email,
                password_hash,
                is_premium
             FROM users
             WHERE email=?
             LIMIT 1"
        );

        $stmt->bind_param("s", $data['email']);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            Response::unauthorized("Invalid credentials.");
        }

        $user = $result->fetch_assoc();

        if (
            !password_verify(
                $data['password'],
                $user['password_hash']
            )
        ) {
            Response::unauthorized("Invalid credentials.");
        }

        $this->db->query(
            "UPDATE users
             SET last_login=NOW()
             WHERE id={$user['id']}"
        );

        $token = JWT::encode([
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);

        $this->success([
            'token' => $token,
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'is_premium' => (bool)$user['is_premium']
            ]
        ]);
    }

    public function logout(): void
    {
        $this->success(
            null,
            "Logged out successfully."
        );
    }
}
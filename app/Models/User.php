<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class User extends Model
{
    public function create(array $data): int
    {
        $password = password_hash(
            $data['password'],
            PASSWORD_BCRYPT
        );

        $stmt = $this->db->prepare("
            INSERT INTO users
            (
                name,
                email,
                password_hash
            )
            VALUES
            (
                ?,
                ?,
                ?
            )
        ");

        $stmt->bind_param(
            "sss",
            $data['name'],
            $data['email'],
            $password
        );

        $stmt->execute();

        $id = $stmt->insert_id;

        $stmt->close();

        return $id;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE id=?
            LIMIT 1
        ");

        $stmt->bind_param("i", $id);

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE email=?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();

        return $user ?: null;
    }

    public function updateLastLogin(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET last_login = NOW()
            WHERE id=?
        ");

        $stmt->bind_param("i", $id);

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    public function updateProfile(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                name=?,
                avatar_url=?,
                country=?,
                birth_date=?,
                gender=?,
                bio=?
            WHERE id=?
        ");

        $stmt->bind_param(
            "ssssssi",
            $data['name'],
            $data['avatar_url'],
            $data['country'],
            $data['birth_date'],
            $data['gender'],
            $data['bio'],
            $id
        );

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET deleted_at = NOW()
            WHERE id=?
        ");

        $stmt->bind_param("i", $id);

        $status = $stmt->execute();

        $stmt->close();

        return $status;
    }

    public function exists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }
}
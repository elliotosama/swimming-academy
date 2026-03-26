<?php


// /app/modesl/UserModel.php


class UserModel {
	private PDO $db;

	public function __construct() {
		$this->db = get_db();
	}

	// lookups


	public function findAll(array $filters = []): array {
		// should return all users
	}

 	public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([strtolower(trim($email))]);
        return $stmt->fetch() ?: null;
    }

    public function findByRole(string $role): array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE role = ?');
        $stmt->execute([strtolower(trim($role))]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    // create

        public function create(array $data): int {
        $token = bin2hex(random_bytes(32));

        $stmt = $this->db->prepare(
            'INSERT INTO users
             (full_name, email, phone_number, password_hash, role, is_active, is_verified, verification_token, verification_expires)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))'
        );
        $stmt->execute([
            $data['full_name'],
            strtolower(trim($data['email'])),
            $data['phone_number'] ?? null,
            password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            $data['role'] ?? 'student',
            $data['is_active'] ?? 1,
            $data['is_verified'] ?? 0,
            $token,
        ]);

        return (int) $this->db->lastInsertId();
    }


    // update

    public function update(int $id, array $data): bool {
        $fields = [
            'full_name     = ?',
            'email         = ?',
            'phone_number  = ?',
            'role          = ?',
            'is_active     = ?',
        ];
        $params = [
            $data['full_name'],
            strtolower(trim($data['email'])),
            $data['phone_number'] ?? null,
            $data['role'],
            $data['is_active'],
        ];

        // Only update password if provided
        if (!empty($data['password'])) {
            $fields[] = 'password_hash = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $params[] = $id;

        $stmt = $this->db->prepare(
            'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?'
        );
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    // delete and deactivate

    public function deactivate(int $id): bool {
        $stmt = $this->db->prepare('UPDATE users SET is_active = 0 WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }


    // checks 

    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([strtolower(trim($email))]);
        return (bool) $stmt->fetch();
    }

    public function assignUser(int $branchId, int $userId): void {
        $db   = get_db();
        $stmt = $db->prepare("
            INSERT IGNORE INTO user_branch (branch_id, user_id)
            VALUES (:branch_id, :user_id)
        ");
        $stmt->execute([':branch_id' => $branchId, ':user_id' => $userId]);
    }

    public function isEmailTaken(string $email, ?int $excludeId = null): bool {
        if ($excludeId) {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
            $stmt->execute([strtolower(trim($email)), $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([strtolower(trim($email))]);
        }
        return (bool) $stmt->fetch();
    }


	// updates 


	public function updatePassword(int $id, string $newPassword): bool {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET password_hash = ? WHERE id = ?'
        );
        $stmt->execute([
            password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]),
            $id,
        ]);
        return $stmt->rowCount() > 0;
    }


    // misc

    public function updateLastLogin(int $id): void {
        $stmt = $this->db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function countByRole(): array {
        $stmt = $this->db->query(
            'SELECT role, COUNT(*) AS total FROM users GROUP BY role'
        );
        $rows   = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['role']] = (int) $row['total'];
        }
        return $result;
    }
}
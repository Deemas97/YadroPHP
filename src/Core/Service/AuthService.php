<?php
namespace Core\Service;

use Core\Service\SessionManager;
use Core\Service\AuthService\User;

final class AuthService implements CoreServiceInterface
{
    private string $userTable = '';
    private ?User  $user = null;
    private ?array $cachedUserData = null;

    public function __construct(
        private DBConnectionManager $dbManager,
        private SessionManager $sessionManager
    ) {}

    public function setUserTable(string $tableName): void
    {
        $this->userTable = $tableName;
    }

    public function register(string $email, string $password, array $additionalData = []): bool
    {
        if ($this->isEmailExists($email)) {
            return false;
        }

        $data = $this->prepareRegistrationData($email, $password, $additionalData);
        
        return $this->createUser($data);
    }

    public function login(string $email, string $password, bool $remember = false): bool
    {
        $userData = $this->getUserDataByEmail($email);
        
        if (!$userData || !$this->verifyUserPassword($userData, $password)) {
            return false;
        }

        $this->initUserSession($userData, $remember);

        $userId = $this->user->getId();
        $db = $this->dbManager->getConnection();

        $loginedAt = date('Y-m-d H:i:s');
        $query = "UPDATE $this->userTable 
                  SET logined_at = '$loginedAt'
                  WHERE id = $userId";
        $db->query($query);

        return true;
    }

    public function authenticateByRememberToken(string $rememberToken): bool
    {
        $db = $this->dbManager->getConnection();
        $safeRememberToken = $db->escape($rememberToken);

        $query = "SELECT u.id, u.role, u.status, u.email, CONCAT(u.f, ' ', u.i) AS name, u.avatar 
                  FROM {$this->userTable} AS u
                  JOIN {$this->userTable}_remember_tokens AS rt ON u.id = rt.user_id
                  WHERE rt.value = '$safeRememberToken' AND rt.expire_at > NOW()
                  LIMIT 1";

        $userData = $db->query($query);

        if (empty($userData)) {
            return false;
        }

        $this->initUserSession($userData[0], true);
        return true;
    }

    public function isAuthenticated(): bool
    {
        if ($this->user !== null) {
            return true;
        }

        $sessionData = $this->sessionManager->validateUserSession($this->userTable);
        if ($sessionData !== null) {
            $this->user = new User($sessionData);
            return true;
        }

        if (!empty($_COOKIE['REMEMBER-TOKEN'])) {
            return $this->authenticateByRememberToken($_COOKIE['REMEMBER-TOKEN']);
        }

        return false;
    }

    public function getUser(): ?User
    {
        if ($this->user === null && $this->isAuthenticated());
        return $this->user;
    }

    public function logout(): void
    {
        $this->sessionManager->endSession($this->userTable);
        $this->user = null;
        $this->cachedUserData = null;
    }

    public function changePassword(array $data): bool
    {
        if (!$this->validatePasswordChangeData($data)) {
            return false;
        }

        $userId = $this->user->getId();
        $currentPassword = $data['current_password'];
        $newPassword = $data['new_password'];

        error_log($currentPassword); // ERROR!
        error_log($newPassword);

        if (!$this->verifyCurrentPassword($userId, $currentPassword)) {
            return false;
        }

        return $this->updateUserPassword($userId, $newPassword);
    }

    private function isEmailExists(string $email): bool
    {
        $db = $this->dbManager->getConnection();
        $safeEmail = $db->escape($email);
        $sql = "SELECT id FROM {$this->userTable} WHERE email = '{$safeEmail}' LIMIT 1";
        $result = $db->query($sql);
        return !empty($result);
    }

    private function prepareRegistrationData(string $email, string $password, array $additionalData): array
    {
        $salt = bin2hex(random_bytes(16));
        $passwordHash = $passwordHash = password_hash($password . $salt, PASSWORD_DEFAULT);
        $dateTimeCurrent = date('Y-m-d H:i:s');

        $data = [
            'email' => $email,
            'password_hash' => $passwordHash,
            'salt' => $salt,
            'created_at' => $dateTimeCurrent,
            'updated_at' => $dateTimeCurrent
        ];

        foreach ($additionalData as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    private function createUser(array $data): bool
    {
        $db = $this->dbManager->getConnection();
        $escapedData = array_map([$db, 'escape'], $data);
        
        $columns = implode(', ', array_keys($escapedData));
        $values = "'" . implode("', '", array_values($escapedData)) . "'";
        $sql = "INSERT INTO {$this->userTable} ({$columns}) VALUES ({$values})";
        
        return $db->query($sql) !== false;
    }

    private function getUserDataByEmail(string $email): ?array
    {
        if ($this->cachedUserData !== null && $this->cachedUserData['email'] === $email) {
            return $this->cachedUserData;
        }

        $db = $this->dbManager->getConnection();
        $safeEmail = $db->escape($email);
        $query = "SELECT *, CONCAT(f, ' ', i) as name FROM {$this->userTable} WHERE email = '$safeEmail'";
        $result = $db->query($query);

        if (empty($result)) {
            return null;
        }

        $this->cachedUserData = $result[0];
        return $this->cachedUserData;
    }

    private function verifyUserPassword(array $userData, string $password): bool
    {
        return password_verify($password . $userData['salt'], $userData['password_hash']);
    }

    private function initUserSession(array $userData, bool $remember): void
    {
        $sessionUserData = [
            'id' => $userData['id'],
            'role' => $userData['role'],
            'status' => $userData['status'],
            'email' => $userData['email'],
            'name' => $userData['name'],
            'avatar' => $userData['avatar']
        ];

        $this->sessionManager->startUserSession($this->userTable, $sessionUserData, $remember);
        $this->user = new User($userData);
    }

    private function validatePasswordChangeData(array $data): bool
    {
        if (empty($data['current_password']) || 
            empty($data['new_password']) || 
            empty($data['confirm_password'])) {
            return false;
        }

        return $data['new_password'] === $data['confirm_password'] && 
               strlen($data['new_password']) >= 8;
    }

    private function verifyCurrentPassword(int $userId, string $currentPassword): bool
    {
        $db = $this->dbManager->getConnection();
        $sql = "SELECT password_hash, salt FROM {$this->userTable} WHERE id = {$userId} LIMIT 1";
        $result = $db->query($sql);

        if (empty($result)) {
            return false;
        }

        $user = $result[0];
        
        return password_verify($currentPassword . $user['salt'], $user['password_hash']);
    }

    private function updateUserPassword(int $userId, string $newPassword): bool
    {
        $db = $this->dbManager->getConnection();
        $sql = "SELECT salt FROM {$this->userTable} WHERE id = {$userId} LIMIT 1";
        $result = $db->query($sql);

        if (empty($result)) {
            return false;
        }

        $salt = $result[0]['salt'];
        $newPasswordHash = password_hash($newPassword . $salt, PASSWORD_DEFAULT);

        $updateSql = "UPDATE {$this->userTable} SET 
                     password_hash = '{$db->escape($newPasswordHash)}', 
                     updated_at = NOW() 
                     WHERE id = {$userId}";

        return $db->query($updateSql) !== false;
    }
}
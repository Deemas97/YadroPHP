<?php
namespace Infrastructure\DataBase;

interface DBConnectorInterface
{
    public function init(
        string $host,
        string $username,
        string $password,
        string $database,
        int $port = 3306,
        string $charset = 'utf8mb4'
    ): void;

    public function escape(string $value);
    public function query(string $query);

    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollback(): bool;

    public function getLastInsertId(): int;
    public function getLastError(): ?string;
}
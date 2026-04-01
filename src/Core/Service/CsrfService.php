<?php
namespace Core\Service;

// ADD CSRF-TOKEN REFRESHING FOR EACH API-REQUEST AT CLIENT SIDE
class CsrfService implements CoreServiceInterface
{
    public function __construct(
        private SessionManager $sessionManager
    )
    {}

    public function initCsrfSession(): void
    {
        $this->sessionManager->startCsrfSession($this->generateToken());
    }
    
    public function validateToken(array $headers, bool $ajaxOnly = false): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$ajaxOnly) {
            return true;
        }
        
        $token = $this->getTokenFromRequest($headers);
        
        return $this->sessionManager->validateCsrfToken($token);
    }
    
    private function getTokenFromRequest(array $headers): ?string
    {   
        $headerToken = ($headers['X-CSRF-TOKEN'] ?? 
                       $headers['X-XSRF-TOKEN'] ?? null);

        $postToken = ($_POST['csrf_token'] ?? null);

        $jsonToken = null;
        if ($headerToken === null && $postToken === null) {
            $input = file_get_contents('php://input');

            if (!empty($input)) {
                $data = json_decode($input, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $jsonToken = $data['csrf_token'] ?? null;
                }
            }
        }
        
        return ($headerToken ?? $postToken ?? $jsonToken);
    }
    
    public function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
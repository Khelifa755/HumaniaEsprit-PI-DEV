<?php

namespace App\Service\Utilisateur;

/**
 * MFA Service - TOTP (Time-based One-Time Password) implementation
 * Follows RFC 6238 standard for authenticator apps (Google Authenticator, Authy, etc.)
 */
class MfaService
{
    private const TIME_STEP     = 30;      // 30-second time window
    private const CODE_LENGTH   = 6;       // 6-digit codes
    private const ALGORITHM     = 'sha1';  // HMAC-SHA1
    private const SECRET_BYTES  = 32;      // 256-bit secret (base32 encoded = 52 chars)

    /**
     * Generate a random base32-encoded secret for MFA setup
     */
    public function generateSecret(): string
    {
        $randomBytes = random_bytes(self::SECRET_BYTES);
        return $this->base32Encode($randomBytes);
    }

    /**
     * Verify a user-provided 6-digit code against the secret
     * Allows 1 time window before/after current for clock skew tolerance
     */
    public function verifyCode(string $secret, string $code, int $tolerance = 1): bool
    {
        $code = trim((string) $code);
        
        // Ensure code is exactly 6 digits
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $timeCounter = (int) (time() / self::TIME_STEP);

        // Check current time window + tolerance windows
        for ($i = -$tolerance; $i <= $tolerance; $i++) {
            $expectedCode = $this->generateCode($secret, $timeCounter + $i);
            if ($code === $expectedCode) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate the otpauth:// URI for QR code generation
     * Format: otpauth://totp/[issuer:]email?secret=XXX&issuer=XXX&algorithm=SHA1&digits=6&period=30
     */
    public function getOtpAuthUri(string $secret, string $email, string $issuer = 'Humania'): string
    {
        $params = http_build_query([
            'secret'    => $secret,
            'issuer'    => $issuer,
            'algorithm' => strtoupper(self::ALGORITHM),
            'digits'    => self::CODE_LENGTH,
            'period'    => self::TIME_STEP,
        ]);

        return sprintf(
            'otpauth://totp/%s:%s?%s',
            rawurlencode($issuer),
            rawurlencode($email),
            $params
        );
    }

    /**
     * Generate a 6-digit TOTP code for a given time counter
     */
    private function generateCode(string $secret, int $timeCounter): string
    {
        $decodedSecret = $this->base32Decode($secret);
        
        // Pack time counter as big-endian 64-bit integer
        $timeBytes = pack('N2', 0, $timeCounter);
        
        // Generate HMAC-SHA1
        $hmac = hash_hmac(self::ALGORITHM, $timeBytes, $decodedSecret, true);
        
        // Dynamic truncation (RFC 4226 Section 5.4)
        $offset = ord($hmac[strlen($hmac) - 1]) & 0x0F;
        $truncated = (unpack('N', substr($hmac, $offset, 4))[1] & 0x7FFFFFFF);
        
        // Modulo to get 6-digit number
        $code = $truncated % (10 ** self::CODE_LENGTH);
        
        return str_pad((string) $code, self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Base32 encoding (RFC 4648)
     */
    private function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $encoded = '';
        $bits = '';

        for ($i = 0; $i < strlen($data); $i++) {
            $byte = ord($data[$i]);
            $bits .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
        }

        // Pad bits to multiple of 5
        $bits = str_pad($bits, ceil(strlen($bits) / 5) * 5, '0');

        for ($i = 0; $i < strlen($bits); $i += 5) {
            $index = bindec(substr($bits, $i, 5));
            $encoded .= $alphabet[$index];
        }

        return $encoded;
    }

    /**
     * Base32 decoding (RFC 4648)
     */
    private function base32Decode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        
        // Convert each character to 5-bit binary
        for ($i = 0; $i < strlen($data); $i++) {
            $index = strpos($alphabet, strtoupper($data[$i]));
            if ($index === false) {
                throw new \InvalidArgumentException("Invalid base32 character: {$data[$i]}");
            }
            $bits .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        // Convert 8-bit groups back to bytes
        $decoded = '';
        for ($i = 0; $i < strlen($bits); $i += 8) {
            $byte = substr($bits, $i, 8);
            if (strlen($byte) < 8) break; // Ignore padding
            $decoded .= chr(bindec($byte));
        }

        return $decoded;
    }
}

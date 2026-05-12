<?php

namespace App\Services;

use App\Models\Passkey;
use App\Models\User;
use Illuminate\Http\Request;
use InvalidArgumentException;

class WebauthnService
{
    public function publicKeyCreationOptions(User $user, Request $request): array
    {
        $challenge = $this->base64urlEncode(random_bytes(32));
        $request->session()->put('passkey.registration_challenge', $challenge);

        return [
            'challenge' => $challenge,
            'rp' => [
                'name' => config('app.name', 'Dragon\'s Hoard'),
                'id' => $request->getHost(),
            ],
            'user' => [
                'id' => $this->base64urlEncode(random_bytes(16)),
                'name' => $user->email,
                'displayName' => $user->name,
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],
            ],
            'authenticatorSelection' => [
                'residentKey' => 'required',
                'requireResidentKey' => true,
                'userVerification' => 'required',
            ],
            'excludeCredentials' => $user->passkeys()
                ->get(['credential_id'])
                ->map(fn (Passkey $passkey) => [
                    'type' => 'public-key',
                    'id' => $passkey->credential_id,
                ])
                ->values(),
            'timeout' => 60000,
            'attestation' => 'none',
        ];
    }

    public function publicKeyRequestOptions(Request $request): array
    {
        $challenge = $this->base64urlEncode(random_bytes(32));
        $request->session()->put('passkey.authentication_challenge', $challenge);

        return [
            'challenge' => $challenge,
            'rpId' => $request->getHost(),
            'allowCredentials' => [],
            'userVerification' => 'required',
            'timeout' => 60000,
        ];
    }

    /**
     * @param array<string, mixed> $credential
     * @return array{name: string, credential_id: string, public_key: string, sign_count: int}
     */
    public function verifyRegistration(User $user, array $credential, Request $request): array
    {
        $clientData = $this->jsonDecode($this->base64urlDecode($credential['response']['clientDataJSON'] ?? ''));
        $this->verifyClientData($clientData, 'webauthn.create', $request->session()->pull('passkey.registration_challenge'), $request);

        $attestation = $this->cborDecode($this->base64urlDecode($credential['response']['attestationObject'] ?? ''));
        $authData = $attestation['authData'] ?? null;

        if (! is_string($authData)) {
            throw new InvalidArgumentException('Missing authenticator data.');
        }

        $parsed = $this->parseAuthenticatorData($authData, true, $request);

        return [
            'name' => $request->input('name') ?: $this->defaultPasskeyName($request),
            'credential_id' => $this->base64urlEncode($parsed['credential_id']),
            'public_key' => $this->coseKeyToPem($parsed['credential_public_key']),
            'sign_count' => $parsed['sign_count'],
        ];
    }

    public function verifyAuthentication(Passkey $passkey, array $credential, Request $request): int
    {
        if (! hash_equals($passkey->credential_id, $credential['id'] ?? '')) {
            throw new InvalidArgumentException('Credential ID mismatch.');
        }

        $clientDataJson = $this->base64urlDecode($credential['response']['clientDataJSON'] ?? '');
        $clientData = $this->jsonDecode($clientDataJson);
        $this->verifyClientData($clientData, 'webauthn.get', $request->session()->pull('passkey.authentication_challenge'), $request);

        $authenticatorData = $this->base64urlDecode($credential['response']['authenticatorData'] ?? '');
        $parsed = $this->parseAuthenticatorData($authenticatorData, false, $request);
        $signature = $this->base64urlDecode($credential['response']['signature'] ?? '');
        $signedData = $authenticatorData.hash('sha256', $clientDataJson, true);

        if (openssl_verify($signedData, $signature, $passkey->public_key, OPENSSL_ALGO_SHA256) !== 1) {
            throw new InvalidArgumentException('Invalid passkey signature.');
        }

        if ($parsed['sign_count'] !== 0 && $parsed['sign_count'] <= $passkey->sign_count) {
            throw new InvalidArgumentException('Invalid passkey sign counter.');
        }

        return $parsed['sign_count'];
    }

    public function base64urlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    public function base64urlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4), true) ?: '';
    }

    /**
     * @return array<string, mixed>
     */
    private function parseAuthenticatorData(string $authData, bool $requiresAttestedCredentialData, Request $request): array
    {
        if (strlen($authData) < 37) {
            throw new InvalidArgumentException('Authenticator data is too short.');
        }

        $rpIdHash = substr($authData, 0, 32);

        if (! hash_equals(hash('sha256', $request->getHost(), true), $rpIdHash)) {
            throw new InvalidArgumentException('Passkey was created for a different site.');
        }

        $flags = ord($authData[32]);

        if (($flags & 0x01) !== 0x01 || ($flags & 0x04) !== 0x04) {
            throw new InvalidArgumentException('Passkey user presence and verification are required.');
        }

        $signCount = unpack('N', substr($authData, 33, 4))[1];
        $result = ['sign_count' => $signCount];

        if (! $requiresAttestedCredentialData) {
            return $result;
        }

        if (($flags & 0x40) !== 0x40 || strlen($authData) < 55) {
            throw new InvalidArgumentException('Missing attested credential data.');
        }

        $credentialIdLength = unpack('n', substr($authData, 53, 2))[1];
        $credentialId = substr($authData, 55, $credentialIdLength);
        $credentialPublicKey = substr($authData, 55 + $credentialIdLength);

        if ($credentialId === '' || $credentialPublicKey === '') {
            throw new InvalidArgumentException('Invalid passkey credential data.');
        }

        return $result + [
            'credential_id' => $credentialId,
            'credential_public_key' => $credentialPublicKey,
        ];
    }

    /**
     * @param array<string, mixed> $clientData
     */
    private function verifyClientData(array $clientData, string $type, ?string $challenge, Request $request): void
    {
        if (! $challenge || ($clientData['type'] ?? null) !== $type || ($clientData['challenge'] ?? null) !== $challenge) {
            throw new InvalidArgumentException('Invalid passkey challenge.');
        }

        $expectedOrigin = $request->getSchemeAndHttpHost();

        if (($clientData['origin'] ?? null) !== $expectedOrigin) {
            throw new InvalidArgumentException('Invalid passkey origin.');
        }
    }

    private function coseKeyToPem(string $coseKey): string
    {
        $decoded = $this->cborDecode($coseKey);

        if (($decoded[1] ?? null) !== 2 || ($decoded[3] ?? null) !== -7 || ($decoded[-1] ?? null) !== 1) {
            throw new InvalidArgumentException('Only ES256 passkeys are supported.');
        }

        $x = $decoded[-2] ?? null;
        $y = $decoded[-3] ?? null;

        if (! is_string($x) || ! is_string($y) || strlen($x) !== 32 || strlen($y) !== 32) {
            throw new InvalidArgumentException('Invalid ES256 public key.');
        }

        $spkiPrefix = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200');
        $der = $spkiPrefix."\x04".$x.$y;

        return "-----BEGIN PUBLIC KEY-----\n"
            .chunk_split(base64_encode($der), 64, "\n")
            ."-----END PUBLIC KEY-----\n";
    }

    /**
     * @return mixed
     */
    private function cborDecode(string $input): mixed
    {
        $offset = 0;

        return $this->readCbor($input, $offset);
    }

    /**
     * @return mixed
     */
    private function readCbor(string $input, int &$offset): mixed
    {
        if ($offset >= strlen($input)) {
            throw new InvalidArgumentException('Unexpected end of CBOR data.');
        }

        $initial = ord($input[$offset++]);
        $major = $initial >> 5;
        $additional = $initial & 0x1f;
        $length = $this->readCborLength($input, $offset, $additional);

        return match ($major) {
            0 => $length,
            1 => -1 - $length,
            2 => $this->readBytes($input, $offset, $length),
            3 => $this->readBytes($input, $offset, $length),
            4 => $this->readCborArray($input, $offset, $length),
            5 => $this->readCborMap($input, $offset, $length),
            7 => $this->readSimpleCborValue($length),
            default => throw new InvalidArgumentException('Unsupported CBOR type.'),
        };
    }

    private function readCborLength(string $input, int &$offset, int $additional): int
    {
        if ($additional < 24) {
            return $additional;
        }

        return match ($additional) {
            24 => ord($this->readBytes($input, $offset, 1)),
            25 => unpack('n', $this->readBytes($input, $offset, 2))[1],
            26 => unpack('N', $this->readBytes($input, $offset, 4))[1],
            default => throw new InvalidArgumentException('Unsupported CBOR length.'),
        };
    }

    private function readBytes(string $input, int &$offset, int $length): string
    {
        $bytes = substr($input, $offset, $length);

        if (strlen($bytes) !== $length) {
            throw new InvalidArgumentException('Unexpected end of CBOR data.');
        }

        $offset += $length;

        return $bytes;
    }

    private function readCborArray(string $input, int &$offset, int $length): array
    {
        $items = [];

        for ($index = 0; $index < $length; $index++) {
            $items[] = $this->readCbor($input, $offset);
        }

        return $items;
    }

    private function readCborMap(string $input, int &$offset, int $length): array
    {
        $items = [];

        for ($index = 0; $index < $length; $index++) {
            $key = $this->readCbor($input, $offset);
            $items[$key] = $this->readCbor($input, $offset);
        }

        return $items;
    }

    private function readSimpleCborValue(int $value): mixed
    {
        return match ($value) {
            20 => false,
            21 => true,
            22 => null,
            default => throw new InvalidArgumentException('Unsupported simple CBOR value.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonDecode(string $json): array
    {
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('Invalid JSON data.');
        }

        return $decoded;
    }

    private function defaultPasskeyName(Request $request): string
    {
        return 'Passkey '.$request->userAgent().' '.now()->format('Y-m-d H:i');
    }
}

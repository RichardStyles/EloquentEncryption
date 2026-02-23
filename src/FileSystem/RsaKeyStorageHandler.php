<?php

declare(strict_types=1);

namespace RichardStyles\EloquentEncryption\FileSystem;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\Contracts\RsaKeyHandler;
use RichardStyles\EloquentEncryption\Exceptions\RSAKeyFileMissing;

class RsaKeyStorageHandler implements RsaKeyHandler
{
    /**
     * Metadata file name for tracking key rotation history
     */
    protected string $metadata_file;

    /**
     * Storage path for the Public Key File
     *
     * @var string
     */
    protected $public_key_path;

    /**
     * Storage path for the Private Key File
     *
     * @var string
     */
    protected $private_key_path;

    /**
     * ApplicationKey constructor.
     */
    public function __construct()
    {
        $this->public_key_path =
            Config::get('eloquent_encryption.key.public', 'eloquent_encryption.pub');
        $this->private_key_path =
            Config::get('eloquent_encryption.key.private', 'eloquent_encryption');
        $this->metadata_file =
            Config::get('eloquent_encryption.key.metadata', '.eloquent_encryption_metadata.json');
    }

    /**
     * Have any RSA keys been generated
     *
     * @return bool
     */
    public function exists()
    {
        return $this->hasPrivateKey() && $this->hasPublicKey();
    }

    /**
     * A Private key file exists
     *
     * @return bool
     */
    public function hasPrivateKey()
    {
        return Storage::exists($this->private_key_path);
    }

    /**
     * A Public key file exists
     *
     * @return bool
     */
    public function hasPublicKey()
    {
        return Storage::exists($this->public_key_path);
    }

    /**
     * Save the generated RSA key to the storage location
     */
    public function saveKey($public, $private)
    {
        Storage::put($this->public_key_path, $public);
        Storage::put($this->private_key_path, $private);
    }

    /**
     * Get the contents of the public key file
     *
     * @return string
     *
     * @throws RSAKeyFileMissing
     */
    public function getPublicKey()
    {
        if (! $this->hasPublicKey()) {
            throw new RSAKeyFileMissing;
        }

        return Storage::get($this->public_key_path);
    }

    /**
     * Get the contents of the private key file
     *
     * @return string
     *
     * @throws RSAKeyFileMissing
     */
    public function getPrivateKey()
    {
        if (! $this->hasPrivateKey()) {
            throw new RSAKeyFileMissing;
        }

        return Storage::get($this->private_key_path);
    }

    /**
     * Get all previous public keys
     *
     * @deprecated Use getPreviousKeys() instead for structured key pairs
     */
    public function getPreviousPublicKeys(): array
    {
        $metadata = $this->getMetadata();
        $keys = [];

        foreach ($metadata['previous'] as $keyPair) {
            $path = $keyPair['public'];
            if (Storage::exists($path)) {
                $keys[] = Storage::get($path);
            }
        }

        return $keys;
    }

    /**
     * Get all previous private keys
     *
     * @deprecated Use getPreviousKeys() instead for structured key pairs
     */
    public function getPreviousPrivateKeys(): array
    {
        $metadata = $this->getMetadata();
        $keys = [];

        foreach ($metadata['previous'] as $keyPair) {
            $path = $keyPair['private'];
            if (Storage::exists($path)) {
                $keys[] = Storage::get($path);
            }
        }

        return $keys;
    }

    /**
     * Get all previous key pairs with rotation metadata
     *
     * Returns an array of key pairs, each containing:
     * - 'publickey': The public key content
     * - 'privatekey': The private key content
     * - 'rotated_at': ISO 8601 timestamp of when the key was rotated
     *
     * @return array<int, array{publickey: string, privatekey: string, rotated_at: string}>
     */
    public function getPreviousKeys(): array
    {
        $metadata = $this->getMetadata();
        $keyPairs = [];

        foreach ($metadata['previous'] as $keyPair) {
            $publicPath = $keyPair['public'];
            $privatePath = $keyPair['private'];

            // Only include if both keys exist
            if (Storage::exists($publicPath) && Storage::exists($privatePath)) {
                $keyPairs[] = [
                    'publickey' => Storage::get($publicPath),
                    'privatekey' => Storage::get($privatePath),
                    'rotated_at' => $keyPair['rotated_at'] ?? 'unknown',
                ];
            }
        }

        return $keyPairs;
    }

    /**
     * Rotate keys: move current to previous, save new as current
     */
    public function rotateKeys(string $newPublic, string $newPrivate): void
    {
        // Read current metadata or create new structure
        $metadata = $this->getMetadata();

        // Get current key pair contents if they exist
        if ($this->exists()) {
            $currentPublic = $this->getPublicKey();
            $currentPrivate = $this->getPrivateKey();

            // Generate new previous key filename (numbered: eloquent_encryption.1, etc.)
            $nextIndex = count($metadata['previous']) + 1;
            $previousPublicPath = $this->generatePreviousKeyName($this->public_key_path, $nextIndex);
            $previousPrivatePath = $this->generatePreviousKeyName($this->private_key_path, $nextIndex);

            // Save current keys as previous
            Storage::put($previousPublicPath, $currentPublic);
            Storage::put($previousPrivatePath, $currentPrivate);

            // Add to metadata
            $metadata['previous'][] = [
                'public' => $previousPublicPath,
                'private' => $previousPrivatePath,
                'rotated_at' => now()->toIso8601String(),
            ];

            // Check max_previous_keys limit
            $maxPreviousKeys = Config::get('eloquent_encryption.key.max_previous_keys', 5);
            while (count($metadata['previous']) > $maxPreviousKeys) {
                // Remove oldest entry
                $oldest = array_shift($metadata['previous']);

                // Delete physical key files
                if (Storage::exists($oldest['public'])) {
                    Storage::delete($oldest['public']);
                }
                if (Storage::exists($oldest['private'])) {
                    Storage::delete($oldest['private']);
                }
            }
        }

        // Update current key filenames in metadata
        $metadata['current'] = [
            'public' => $this->public_key_path,
            'private' => $this->private_key_path,
        ];

        // Save new keys as current
        Storage::put($this->public_key_path, $newPublic);
        Storage::put($this->private_key_path, $newPrivate);

        // Save metadata
        $this->saveMetadata($metadata);
    }

    /**
     * Get metadata from file or fall back to config
     */
    private function getMetadata(): array
    {
        $metadataPath = $this->metadata_file;

        if (Storage::exists($metadataPath)) {
            $content = Storage::get($metadataPath);
            $metadata = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($metadata)) {
                return $metadata;
            }
        }

        // Fall back to config or default structure
        $configPrevious = Config::get('eloquent_encryption.key.previous', []);

        return [
            'current' => [
                'public' => $this->public_key_path,
                'private' => $this->private_key_path,
            ],
            'previous' => $configPrevious,
        ];
    }

    /**
     * Save metadata to file
     */
    private function saveMetadata(array $metadata): void
    {
        $metadataPath = $this->metadata_file;
        $content = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        Storage::put($metadataPath, $content);
    }

    /**
     * Generate numbered key filename for previous keys
     */
    private function generatePreviousKeyName(string $originalPath, int $index): string
    {
        // Remove extension if present
        $pathInfo = pathinfo($originalPath);
        $dirname = $pathInfo['dirname'] !== '.' ? $pathInfo['dirname'].'/' : '';
        $filename = $pathInfo['filename'];
        $extension = isset($pathInfo['extension']) ? '.'.$pathInfo['extension'] : '';

        return $dirname.$filename.'.'.$index.$extension;
    }
}

<?php
/**
 * Lightweight per-IP rate limiter for single-instance deployments.
 *
 * Uses flock() so concurrent PHP workers share atomic counters. For multiple
 * application nodes/containers, replace the storage backend with Redis or a
 * shared database; the system temp directory is local to each host.
 */
class RateLimiter
{
    public static function allow(string $key, int $limit, int $windowSeconds): bool
    {
        $limit = max(1, $limit);
        $windowSeconds = max(1, $windowSeconds);
        $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'velox-rate-limit-' . substr(hash('sha256', dirname(__DIR__, 2)), 0, 16);

        if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
            error_log('[RATE_LIMIT] Unable to create rate-limit storage.');
            return false;
        }

        @chmod($directory, 0700);
        $filePath = $directory . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.json';
        $handle = @fopen($filePath, 'c+');

        if ($handle === false) {
            error_log('[RATE_LIMIT] Unable to open rate-limit storage.');
            return false;
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                error_log('[RATE_LIMIT] Unable to lock rate-limit storage.');
                return false;
            }

            $contents = stream_get_contents($handle);
            $state = is_string($contents) && $contents !== ''
                ? json_decode($contents, true)
                : null;

            $now = time();
            $startedAt = is_array($state) ? (int) ($state['started_at'] ?? 0) : 0;
            $hits = is_array($state) ? (int) ($state['hits'] ?? 0) : 0;

            if ($startedAt <= 0 || ($now - $startedAt) >= $windowSeconds || $now < $startedAt) {
                $startedAt = $now;
                $hits = 0;
            }

            if ($hits >= $limit) {
                return false;
            }

            $hits++;
            $nextState = json_encode(
                ['started_at' => $startedAt, 'hits' => $hits],
                JSON_THROW_ON_ERROR
            );

            rewind($handle);
            if (!ftruncate($handle, 0) || fwrite($handle, $nextState) === false) {
                error_log('[RATE_LIMIT] Unable to persist rate-limit state.');
                return false;
            }

            fflush($handle);
            return true;
        } catch (Throwable $e) {
            error_log('[RATE_LIMIT] ' . $e->getMessage());
            return false;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}

<?php

namespace Northlab\Parasut\Resources;

/**
 * Asenkron islerin (e-fatura/e-arsiv olusturma, PDF uretimi, stok
 * guncelleme gibi) durumunu takip etmek icin kullanilir.
 *
 * Ornek kullanim:
 *   $job = Parasut::trackableJobs()->find($jobId);
 *   $status = $job['data']['attributes']['status']; // 'pending' | 'succeeded' | 'failed'
 */
class TrackableJobResource extends BaseResource
{
    protected string $endpoint = 'trackable_jobs';

    protected ?string $jsonApiType = 'trackable_jobs';

    public function list(array $options = []): array
    {
        throw new \BadMethodCallException('trackable_jobs icin list uc noktasi yoktur, sadece find($id) kullanilabilir.');
    }

    public function create(array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('trackable_jobs dogrudan olusturulamaz; ilgili islemi tetikleyen uc nokta (orn. EArchiveResource::create) tarafindan otomatik uretilir.');
    }

    public function update(int|string $id, array $attributes = [], array $relationships = [], array $include = []): array
    {
        throw new \BadMethodCallException('trackable_jobs guncellenemez.');
    }

    public function delete(int|string $id): array
    {
        throw new \BadMethodCallException('trackable_jobs silinemez.');
    }

    /**
     * Is tamamlanana (succeeded/failed) kadar belirli araliklarla sorgular.
     * Sunucu tarafinda kullanildiginda dikkatli olun: bu metod bloklayicidir.
     *
     * @throws \RuntimeException  Zaman asimina ugrarsa
     */
    public function waitUntilFinished(int|string $jobId, int $timeoutSeconds = 30, int $intervalMilliseconds = 1000): array
    {
        $elapsed = 0;

        while ($elapsed < $timeoutSeconds * 1000) {
            $job = $this->find($jobId);
            $status = $job['data']['attributes']['status'] ?? null;

            if (in_array($status, ['succeeded', 'failed'], true)) {
                return $job;
            }

            usleep($intervalMilliseconds * 1000);
            $elapsed += $intervalMilliseconds;
        }

        throw new \RuntimeException("Trackable job #{$jobId} {$timeoutSeconds} saniye icinde tamamlanmadi.");
    }
}

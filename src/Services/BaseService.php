<?php

declare(strict_types=1);

namespace App\Services;

abstract class BaseService
{
    /**
     * @return int[]
     */
    protected function parseIdCsv(string $csv): array
    {
        $csv = trim($csv);
        if ($csv === '') {
            return [];
        }

        $parts = explode(',', $csv);
        $ids = [];
        foreach ($parts as $part) {
            $id = (int) trim($part);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    protected function toPublicUrl(string $path): string
    {
        $cleanPath = ltrim(trim($path), '/');
        if ($cleanPath === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $cleanPath) === 1) {
            return $cleanPath;
        }

        return '/seiryokukai_php/' . $cleanPath;
    }

    protected function normalizzaOrariSettimana(array $orari): array
    {
        $chiavi = [
            'lun_inizio', 'lun_fine',
            'mar_inizio', 'mar_fine',
            'mer_inizio', 'mer_fine',
            'gio_inizio', 'gio_fine',
            'ven_inizio', 'ven_fine',
            'sab_inizio', 'sab_fine',
            'dom_inizio', 'dom_fine',
        ];

        $out = [];

        foreach ($chiavi as $chiave) {
            $valore = trim((string) ($orari[$chiave] ?? ''));
            if ($valore === '') {
                $out[$chiave] = null;
                continue;
            }

            if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $valore)) {
                throw new \InvalidArgumentException('Formato orario non valido per ' . $chiave);
            }

            $out[$chiave] = $valore;
        }

        return $out;
    }
}

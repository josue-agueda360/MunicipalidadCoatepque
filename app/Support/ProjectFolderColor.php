<?php

namespace App\Support;

use App\Models\ProjectFolder;
use RuntimeException;

class ProjectFolderColor
{
    private const PALETTE = [
        '#e4a52c',
        '#2a9fd6',
        '#23b487',
        '#9467d8',
        '#df6a6a',
        '#f08c46',
        '#4a79d8',
        '#b85cc7',
        '#4aa65f',
        '#d5c044',
        '#2fb6b0',
        '#d9578d',
    ];

    public static function nextForWorkspace(): string
    {
        $usedColors = ProjectFolder::query()
            ->whereNotNull('color')
            ->pluck('color')
            ->mapWithKeys(fn (string $color): array => [
                mb_strtolower($color) => true,
            ]);

        for ($index = 0; $index < 4096; $index++) {
            $color = self::forIndex($index);

            if (! $usedColors->has(mb_strtolower($color))) {
                return $color;
            }
        }

        throw new RuntimeException('No fue posible asignar un color a la carpeta.');
    }

    public static function nextForUser(int $userId): string
    {
        return self::nextForWorkspace();
    }

    public static function forIndex(int $index): string
    {
        if (isset(self::PALETTE[$index])) {
            return self::PALETTE[$index];
        }

        $hue = fmod(35 + ($index * 137.508), 360);

        return self::hslToHex($hue, 0.68, 0.52);
    }

    private static function hslToHex(
        float $hue,
        float $saturation,
        float $lightness,
    ): string {
        $chroma = (1 - abs((2 * $lightness) - 1)) * $saturation;
        $segment = $hue / 60;
        $secondary = $chroma * (1 - abs(fmod($segment, 2) - 1));
        [$red, $green, $blue] = match (true) {
            $segment < 1 => [$chroma, $secondary, 0],
            $segment < 2 => [$secondary, $chroma, 0],
            $segment < 3 => [0, $chroma, $secondary],
            $segment < 4 => [0, $secondary, $chroma],
            $segment < 5 => [$secondary, 0, $chroma],
            default => [$chroma, 0, $secondary],
        };
        $offset = $lightness - ($chroma / 2);

        return sprintf(
            '#%02x%02x%02x',
            (int) round(($red + $offset) * 255),
            (int) round(($green + $offset) * 255),
            (int) round(($blue + $offset) * 255),
        );
    }
}

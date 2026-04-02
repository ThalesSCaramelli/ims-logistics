<?php

namespace App\Services;

use App\Models\JobContainer;
use App\Models\ClientPrice;

class PricingService
{
    /**
     * Calculate both client price and labor price for a container.
     * Returns ['client' => float, 'labor' => float, 'breakdown' => array]
     */
    public function calculateContainer(JobContainer $container, int $workerCount = 1): array
    {
        // If manual override is set, it replaces all automatic calculation
        if ($container->hasOverride()) {
            return [
                'client'    => (float) $container->override_client_amount,
                'labor'     => 0, // labor still calculated normally on override
                'overridden' => true,
            ];
        }

        $price = ClientPrice::forSiteAndProduct(
            $container->job->site_id,
            $container->product_id
        );

        if (!$price) {
            return ['client' => 0, 'labor' => 0, 'breakdown' => [], 'error' => 'No price configured'];
        }

        $clientTotal = (float) $price->client_base_price;
        $laborTotal  = (float) $price->labor_base_price;
        $breakdown   = [
            ['label' => 'Base price', 'client' => $clientTotal, 'labor' => $laborTotal]
        ];

        // Boxes surcharge (blocks above limit)
        if ($container->boxes_count && $price->product->has_boxes_skills) {
            $clientBoxes = $price->clientBoxesSurcharge($container->boxes_count);
            $laborBoxes  = $price->laborBoxesSurcharge($container->boxes_count);
            if ($clientBoxes > 0 || $laborBoxes > 0) {
                $clientTotal += $clientBoxes;
                $laborTotal  += $laborBoxes;
                $breakdown[] = ['label' => 'Boxes surcharge', 'client' => $clientBoxes, 'labor' => $laborBoxes];
            }
        }

        // Skills surcharge (blocks above limit)
        if ($container->skills_count && $price->product->has_boxes_skills) {
            $clientSkills = $price->clientSkillsSurcharge($container->skills_count);
            $laborSkills  = $price->laborSkillsSurcharge($container->skills_count);
            if ($clientSkills > 0 || $laborSkills > 0) {
                $clientTotal += $clientSkills;
                $laborTotal  += $laborSkills;
                $breakdown[] = ['label' => 'Skills surcharge', 'client' => $clientSkills, 'labor' => $laborSkills];
            }
        }

        // Required additionals (e.g. extra worker on FAK when workers > 4)
        foreach ($price->requiredExtras as $extra) {
            $applies = $this->evaluateCondition($extra->condition, ['worker_count' => $workerCount]);
            if ($applies) {
                $multiplier = $this->getMultiplier($extra, $workerCount);
                $clientExtra = (float) $extra->client_value * $multiplier;
                $laborExtra  = (float) $extra->labor_value * $multiplier;
                $clientTotal += $clientExtra;
                $laborTotal  += $laborExtra;
                $breakdown[] = ['label' => $extra->name . ' (required)', 'client' => $clientExtra, 'labor' => $laborExtra];
            }
        }

        return [
            'client'     => round($clientTotal, 2),
            'labor'      => round($laborTotal, 2),
            'breakdown'  => $breakdown,
            'overridden' => false,
        ];
    }

    /**
     * Calculate extra work impact on client and labor prices.
     */
    public function calculateExtraWork(float $hours, ClientPrice $price): array
    {
        return [
            'client' => round($hours * (float) $price->extra_work_client_rate, 2),
            'labor'  => round($hours * (float) $price->extra_work_labor_rate, 2),
        ];
    }

    /**
     * Calculate waiting time impact on client and labor prices.
     */
    public function calculateWaitingTime(float $hours, ClientPrice $price): array
    {
        return [
            'client' => round($hours * (float) $price->waiting_time_client_rate, 2),
            'labor'  => round($hours * (float) $price->waiting_time_labor_rate, 2),
        ];
    }

    // ── Private helpers ────────────────────────────────────────────────

    private function evaluateCondition(?string $condition, array $context): bool
    {
        if (!$condition) return true;
        // Simple condition parser: "workers > 4"
        if (preg_match('/worker_count\s*>\s*(\d+)/', $condition, $matches)) {
            return ($context['worker_count'] ?? 0) > (int) $matches[1];
        }
        return true;
    }

    private function getMultiplier(object $extra, int $workerCount): int
    {
        // "per_worker" extras above threshold (e.g. extra workers above 4)
        if ($extra->unit === 'per_worker' && $extra->condition) {
            if (preg_match('/worker_count\s*>\s*(\d+)/', $extra->condition, $matches)) {
                return max(0, $workerCount - (int) $matches[1]);
            }
        }
        return 1;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\BehavioralProfile;
use App\Services\Ml\SegmentClassifierService;
use Illuminate\Console\Command;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

/**
 * Trains the k-NN segment classifier on the real, human-labeled rows in
 * behavioral_profiles — the only real outcome-labeled dataset this platform
 * has for "which segment does this customer belong to". With only ~20
 * examples across 5 classes, this reports leave-one-out cross-validation
 * accuracy (train on all-but-one, predict the held-out one, repeat for
 * every row) rather than training accuracy, which would be meaninglessly
 * inflated at this sample size.
 */
class TrainSegmentClassifier extends Command
{
    protected $signature = 'ml:train-segment-classifier {--k=3 : Number of neighbors}';

    protected $description = 'Train the k-NN customer-segment classifier on labeled behavioral_profiles data';

    public function handle(): int
    {
        $profiles = BehavioralProfile::all();

        if ($profiles->count() < 10) {
            $this->error("Only {$profiles->count()} labeled profiles found — need a meaningful sample to train on.");
            return self::FAILURE;
        }

        $samples = $profiles->map(fn (BehavioralProfile $p) => [
            (float) $p->intent_score,
            (float) $p->engagement_score,
            (float) $p->buying_readiness,
            (float) $p->churn_score,
            (float) $p->loyalty_score,
            (float) $p->trust_score,
            (float) $p->frustration_score,
        ])->values()->all();

        $labels = $profiles->pluck('segment')->values()->all();

        $k = (int) $this->option('k');

        $this->info("Training data: {$profiles->count()} labeled examples, ".count(array_unique($labels)).' segments, k='.$k);

        // Leave-one-out cross-validation — the honest accuracy estimate at this sample size.
        $correct = 0;
        $n = count($samples);
        for ($i = 0; $i < $n; $i++) {
            $trainSamples = $samples;
            $trainLabels = $labels;
            unset($trainSamples[$i], $trainLabels[$i]);

            $foldModel = new KNearestNeighbors($k);
            $foldModel->train(new Labeled(array_values($trainSamples), array_values($trainLabels)));

            $prediction = $foldModel->predict(Unlabeled::build([$samples[$i]]))[0];
            if ($prediction === $labels[$i]) {
                $correct++;
            }
        }

        $accuracy = round($correct / $n * 100, 1);
        $this->info("Leave-one-out cross-validation accuracy: {$correct}/{$n} ({$accuracy}%)");

        // Final model trained on every labeled example, persisted for the app to use.
        $model = new PersistentModel(
            new KNearestNeighbors($k),
            new Filesystem(storage_path('app/' . SegmentClassifierService::MODEL_PATH))
        );
        $model->train(new Labeled($samples, $labels));
        $model->save();

        $this->info('Model saved to storage/app/' . SegmentClassifierService::MODEL_PATH);

        return self::SUCCESS;
    }
}

<?php

namespace App\Services\Ml;

use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

/**
 * Wraps the trained k-Nearest Neighbors segment classifier — predicts which
 * customer segment (champion/loyal/at_risk/dormant/new) a set of behavioral
 * scores belongs to. Trained on the 20 real, human-labeled rows in
 * behavioral_profiles (see App\Console\Commands\TrainSegmentClassifier),
 * the only real labeled outcome data this platform has for this task.
 *
 * Used to replace the hand-written if/else segment guess in the
 * business-helpers route — everything downstream (Sales/Marketing agent
 * "play" assignment, priority ranking, predefined-question answers) already
 * consumes whatever `seg` value it's given, so this is a drop-in swap of
 * the segment source, not a change to how segments are used.
 */
class SegmentClassifierService
{
    public const MODEL_PATH = 'ml/segment_classifier.rbx';

    /** Feature order the model was trained on — predictions must match this exactly. */
    public const FEATURE_ORDER = [
        'intent', 'engagement', 'buying_readiness', 'churn', 'loyalty', 'trust', 'frustration',
    ];

    private ?PersistentModel $model = null;

    public function isTrained(): bool
    {
        return file_exists(storage_path('app/' . self::MODEL_PATH));
    }

    /**
     * @param array<string,int|float> $scores keyed by App::FEATURE_ORDER names
     */
    public function predict(array $scores): string
    {
        $model = $this->loadModel();

        $sample = array_map(
            fn (string $key) => (float) ($scores[$key] ?? 0),
            self::FEATURE_ORDER
        );

        return $model->predict(Unlabeled::build([$sample]))[0];
    }

    private function loadModel(): PersistentModel
    {
        if ($this->model !== null) {
            return $this->model;
        }

        if (! $this->isTrained()) {
            throw new \RuntimeException(
                'Segment classifier has not been trained yet. Run: php artisan ml:train-segment-classifier'
            );
        }

        return $this->model = PersistentModel::load(
            new Filesystem(storage_path('app/' . self::MODEL_PATH))
        );
    }
}
